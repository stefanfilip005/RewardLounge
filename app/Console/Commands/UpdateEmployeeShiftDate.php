<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Services\EmployeeShiftStatus;
use App\Models\Shift;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UpdateEmployeeShiftDate extends Command
{
    private const TIMEZONE = 'Europe/Vienna';

    protected $signature = 'command:employees-update-shift-date {--full : Compatibility option; all shifts are always considered}';
    protected $description = 'Updates each employee\'s last shift date based on the most recent shift';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $now = Carbon::now(self::TIMEZONE);
        DB::beginTransaction();
        try {
            $statuses = app(EmployeeShiftStatus::class)->forEmployees(Employee::pluck('remoteId')->all(), $now);
            foreach ($statuses as $employeeId => $status) {
                Employee::where('remoteId', $employeeId)->update([
                    'last_shift_date' => $status['last_shift_date'],
                    'next_shift_date' => $status['next_shift_date'],
                ]);
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            $this->error('Failed to update employee shift dates. Previous dates were preserved.');
            return 1;
        }



        DB::beginTransaction();
        try {
            // Set time boundaries
            $twoYearsAgo = $now->copy()->subYears(2)->startOfDay();
            $twoMonthsAgo = $now->copy()->subMonths(2)->startOfDay();

            // Fetch all shifts from the last 2 years
            $shifts = Shift::where('start', '>=', $twoYearsAgo)
                ->where('start', '<=', $now->format('Y-m-d H:i:s'))->get();

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
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            $this->error('Failed to calculate and update due to: ' . $e->getMessage());
            return 1;
        }
        return 0;
    }
}
