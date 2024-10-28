<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\Shift;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UpdateEmployeeShiftDate extends Command
{
    protected $signature = 'command:employees-update-shift-date {--full : Consider all shifts instead of the last week}';
    protected $description = 'Updates each employee\'s last shift date based on the most recent shift';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        DB::beginTransaction();
        try {
            $query = Shift::select('employeeId', DB::raw('MAX(start) as last_shift'))->groupBy('employeeId');
            if (!$this->option('full')) {
                $oneWeekAgo = Carbon::now()->subWeek()->startOfDay();
                $query->where('start', '>=', $oneWeekAgo);
            }
            $latestShifts = $query->get();
            foreach ($latestShifts as $shift) {
                Employee::where('remoteId', $shift->employeeId)->update(['last_shift_date' => $shift->last_shift]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        }

        //Set all next_shift_date to null
        Employee::whereNotNull('next_shift_date')->update(['next_shift_date' => null]);
        DB::beginTransaction();
        try {
            // Find and update next shift dates for employees with future shifts
            $today = Carbon::now()->startOfDay();
            $futureShifts = DB::table('futureShifts')
                ->where('Datum', '>=', $today)
                ->orderBy('Datum', 'asc')
                ->get();

            // Collect unique employee identifiers from future shifts
            $employeeUpdates = [];
            foreach ($futureShifts as $shift) {
                $remoteId = ltrim(preg_replace('/^\D+/', '', $shift->ObjektId), '0');
                // Only store the earliest date found for each remoteId
                if (!isset($employeeUpdates[$remoteId]) || new Carbon($shift->Datum) < new Carbon($employeeUpdates[$remoteId])) {
                    $employeeUpdates[$remoteId] = $shift->Datum;
                }
            }

            foreach (array_chunk($employeeUpdates, 200) as $chunk) {
                $updates = [];
                foreach ($chunk as $remoteId => $nextShiftDate) {
                    $updates[] = ['remoteId' => $remoteId, 'next_shift_date' => $nextShiftDate];
                }
                DB::statement("UPDATE employees SET next_shift_date = CASE remoteId " . 
                implode(' ', array_map(function($id, $date) { 
                    return "WHEN '$id' THEN '$date' "; 
                }, array_keys($employeeUpdates), $employeeUpdates)) . 
                "END WHERE remoteId IN (" . implode(',', array_map('intval', array_keys($employeeUpdates))) . ")");

            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        }



        DB::beginTransaction();
        try {
            // Set time boundaries
            $twoYearsAgo = Carbon::now()->subYears(2)->startOfDay();
            $twoMonthsAgo = Carbon::now()->subMonths(2)->startOfDay();

            // Fetch all shifts from the last 2 years
            $shifts = Shift::where('start', '>=', $twoYearsAgo)->get();

            // Initialize storage for shift type counts
            $employeeShiftTypes = [];
            $recentShifts = [];

            // Aggregate shift types by employee within 2 years and separately track recent shifts
            foreach ($shifts as $shift) {
                $employeeId = $shift->employeeId;
                $shiftType = $shift->shiftType;

                // Skip counting "(Kein)" type shifts
                if ($shiftType === '(Kein)') {
                    continue;
                }

                // Initialize arrays if not already set
                if (!isset($employeeShiftTypes[$employeeId])) {
                    $employeeShiftTypes[$employeeId] = ['recent' => [], 'historic' => []];
                }
                
                // Count shift types for the last 2 years
                if (!isset($employeeShiftTypes[$employeeId]['historic'][$shiftType])) {
                    $employeeShiftTypes[$employeeId]['historic'][$shiftType] = 0;
                }
                $employeeShiftTypes[$employeeId]['historic'][$shiftType]++;

                // Specifically count recent shift types
                if ($shift->start >= $twoMonthsAgo) {
                    if (!isset($employeeShiftTypes[$employeeId]['recent'][$shiftType])) {
                        $employeeShiftTypes[$employeeId]['recent'][$shiftType] = 0;
                    }
                    $employeeShiftTypes[$employeeId]['recent'][$shiftType]++;
                    $recentShifts[$employeeId] = 1; // Mark this employee as having recent shifts
                }
            }

            // Update each employee with the most used shift type based on the availability of recent shifts
            foreach ($employeeShiftTypes as $employeeId => $types) {
                $chosenType = 'recent';

                // If no recent shifts, fall back to historic data
                if (!isset($recentShifts[$employeeId])) {
                    $chosenType = 'historic';
                }

                // Determine the most used type from the chosen category, excluding "(Kein)"
                if (!empty($types[$chosenType])) {
                    $mostUsedType = array_keys($types[$chosenType], max($types[$chosenType]))[0];
                    Employee::where('remoteId', $employeeId)->update(['employeeType' => $mostUsedType]);
                }
            }

            DB::commit();
            $this->info('Successfully calculated and updated the most used shift types for all employees.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Failed to calculate and update due to: ' . $e->getMessage());
        }
    }
}