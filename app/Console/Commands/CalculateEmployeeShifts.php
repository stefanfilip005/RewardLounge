<?php

namespace App\Console\Commands;

use App\Mail\OrderPlacedForCustomer;
use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Models\FutureShift;
use App\Models\Order;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class CalculateEmployeeShifts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:calculateEmployeeShifts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'aaaaaaa';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $demandTypeMapping = [
            'NEF' => ['FNB_NEF'],
            'RTW' => ['SR2','SR1', 'FRC', 'FRB'],
            'KTW' => ['FKB', 'SK1','SK2', 'FKB-B', 'FKC'],
            'BKTW' => ['FBB'],
            'DF' => ['DF1'],
        ];

        $skipTypes = ['ANA','BEL','DF1', 'DF2','FAB','FAC','FBB_AMB','FKB_AMB','FRB_AMB','MA','SK1_AMB','SK2_AMB','SR2_AMB'];

        $reverseDemandTypeMapping = [];
        foreach ($demandTypeMapping as $mainGroup => $types) {
            foreach ($types as $type) {
                $reverseDemandTypeMapping[$type] = $mainGroup;
            }
        }

        $demandTypeGroups = ['NEF', 'RTW', 'KTW', 'BKTW'];
        $timeGroups = ['VM', 'NM', 'NIGHT'];
        $weekdays = [0, 1, 2, 3, 4, 5, 6]; // 0 = Monday, 6 = Sunday

        $locations = [38,39];

        //truncate emloyee shifts
        EmployeeShift::truncate();
        foreach($locations as $location){
            $employeeMap = [];

            $shifts = Shift::where('location', $location)->where('shiftType', 'EA-RKT')->get();
            foreach ($shifts as $shift) {
                if(in_array($shift->demandType,$skipTypes)) continue;

                $employeeId = $shift->employeeId;
                // Initialize the employee's group data if not already set
                if (!isset($employeeMap[$employeeId])) {
                    foreach (array_merge($timeGroups, $demandTypeGroups) as $group) {
                        $employeeMap[$employeeId][$group] = 0;
                    }
                    foreach ($weekdays as $day) {
                        $employeeMap[$employeeId]["weekday_$day"] = 0;
                    }
                }
                
                $employeeShift = EmployeeShift::firstOrNew([
                    'employee_id' => $employeeId,
                    'location' => $location,
                ]);

                $start = Carbon::parse($shift->start, 'Europe/Vienna');
                $weekdayIndex = "weekday_".$start->dayOfWeek;
                $employeeShift->$weekdayIndex++;
                $employeeMap[$employeeId]["weekday_".$start->dayOfWeek]++;


                $group = ($start->hour >= 5 && $start->hour < 13) ? 'VM' : (($start->hour >= 13 && $start->hour < 21) ? 'NM' : 'NIGHT');
                $employeeMap[$employeeId][$group]++;
                $employeeShift->$group++;
    
                if (isset($reverseDemandTypeMapping[$shift->demandType])) {
                    $demandGroup = $reverseDemandTypeMapping[$shift->demandType];
                    $employeeMap[$employeeId][$demandGroup]++;
                    $employeeShift->$demandGroup++;
                }
                $employeeShift->save();
            }

            // Normalize the groups
            foreach ($employeeMap as $employeeId => &$groups) {
                $employeeShift = EmployeeShift::where('employee_id', $employeeId)->where('location', $location)->first();
                $totalTimeShifts = array_sum(array_intersect_key($groups, array_flip($timeGroups)));
                foreach ($timeGroups as $group) {
                    $normalizedKey = $group . '_norm';
                    $groups[$normalizedKey] = $totalTimeShifts > 0 ? $groups[$group] / $totalTimeShifts : 0;
                    $employeeShift->$normalizedKey = $totalTimeShifts > 0 ? $groups[$group] / $totalTimeShifts : 0;
                }
                $totalMainShifts = array_sum(array_intersect_key($groups, array_flip($demandTypeGroups)));
                foreach ($demandTypeGroups as $group) {
                    $normalizedKey = $group . '_norm';
                    $groups[$normalizedKey] = $totalMainShifts > 0 ? $groups[$group] / $totalMainShifts : 0;
                    $employeeShift->$normalizedKey = $totalMainShifts > 0 ? $groups[$group] / $totalMainShifts : 0;
                }
                $totalShiftsWeekdayGroups = array_sum(array_intersect_key($groups, array_flip(array_map(function($day) { return "weekday_$day"; }, $weekdays))));
                foreach ($weekdays as $day) {
                    $normalizedKey = "weekday_$day" . '_norm';
                    $groups[$normalizedKey] = $totalShiftsWeekdayGroups > 0 ? $groups["weekday_$day"] / $totalShiftsWeekdayGroups : 0;
                    $employeeShift->$normalizedKey = $totalShiftsWeekdayGroups > 0 ? $groups["weekday_$day"] / $totalShiftsWeekdayGroups : 0;
                }
                $employeeShift->save();
            }
        }
    }
}
