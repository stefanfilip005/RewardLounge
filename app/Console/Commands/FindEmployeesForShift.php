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

class FindEmployeesForShift extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:findEmployeesForShift';

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
            'RTW' => ['SR1', 'FRC', 'FRB'],
            'KTW' => ['FKB', 'SK1', 'FKB-B', 'FKC'],
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


        $shiftsByDate = [];

        Shift::where('location', 38)->where('shiftType', 'EA-RKT')->orderBy('start', 'asc')->chunk(5000, function ($shifts) use (&$shiftsByDate,$skipTypes) {
            foreach ($shifts as $shift) {
                if(in_array($shift->demandType, $skipTypes)) continue;
                $date = Carbon::parse($shift->start)->format('Y-m-d');
                if (!isset($shiftsByDate[$date])) {
                    $shiftsByDate[$date] = [];
                }
                $shiftsByDate[$date][] = $shift->toArray();
            }
        });


        $employeeOverlaps = [];
        $employeeOverlapsCount = [];
        $demandTypeCount = [];
        foreach ($shiftsByDate as $date => $shifts) {
            foreach ($shifts as $index => $shift) {
                for ($j = $index  + 1; $j < count($shifts); $j++) {
                    $comparisonShift = $shifts[$j];

                    // Convert strings to Carbon instances
                    $shiftStart = Carbon::parse($shift['start']);
                    $shiftEnd = Carbon::parse($shift['end']);
                    $comparisonStart = Carbon::parse($comparisonShift['start']);
                    $comparisonEnd = Carbon::parse($comparisonShift['end']);

                    // Calculate the overlap
                    $startOverlap = $shiftStart->max($comparisonStart);
                    $endOverlap = $shiftEnd->min($comparisonEnd);

                    if ($startOverlap < $endOverlap) {  // There is some overlap
                        // Calculate total duration and overlap duration in seconds
                        $totalDuration = $shiftEnd->diffInSeconds($shiftStart);
                        $overlapDuration = $endOverlap->diffInSeconds($startOverlap);

                        // Calculate the overlap percentage
                        $overlapPercent = ($overlapDuration / $totalDuration);

                        // Initialize matrix slots if not already set
                        if (!isset($employeeOverlaps[$shift['employeeId']][$comparisonShift['employeeId']])) {
                            $employeeOverlaps[$shift['employeeId']][$comparisonShift['employeeId']] = 0;
                            $employeeOverlapsCount[$shift['employeeId']][$comparisonShift['employeeId']] = 0;
                        }
                        if (!isset($employeeOverlaps[$comparisonShift['employeeId']][$shift['employeeId']])) {
                            $employeeOverlaps[$comparisonShift['employeeId']][$shift['employeeId']] = 0;
                            $employeeOverlapsCount[$comparisonShift['employeeId']][$shift['employeeId']] = 0;
                        }

                        if ($overlapPercent == 1.0) {
                            if ($this->demandTypeMatches($shift['demandType'], $comparisonShift['demandType'], $demandTypeMapping)) {
                                if (!isset($demandTypeCount[$shift['employeeId']][$comparisonShift['employeeId']])) {
                                    $demandTypeCount[$shift['employeeId']][$comparisonShift['employeeId']] = 0;
                                }
                                if (!isset($demandTypeCount[$comparisonShift['employeeId']][$shift['employeeId']])) {
                                    $demandTypeCount[$comparisonShift['employeeId']][$shift['employeeId']] = 0;
                                }
                                $demandTypeCount[$shift['employeeId']][$comparisonShift['employeeId']]++;
                                $demandTypeCount[$comparisonShift['employeeId']][$shift['employeeId']]++;
                            }
                        }


                        // Add the overlap percentage to both employees' matrix slots
                        $employeeOverlaps[$shift['employeeId']][$comparisonShift['employeeId']] += $overlapPercent;
                        $employeeOverlaps[$comparisonShift['employeeId']][$shift['employeeId']] += $overlapPercent;
                        $employeeOverlapsCount[$shift['employeeId']][$comparisonShift['employeeId']]++;
                        $employeeOverlapsCount[$comparisonShift['employeeId']][$shift['employeeId']]++;
                    }
                }
            }
        }


        
        // After processing all shifts, print the array
        /*
        echo '<pre>' . print_r($shiftsByDate, true) . '</pre>';
        echo '<pre>' . print_r($demandTypeCount, true) . '</pre>';
        echo '<pre>' . print_r($employeeOverlaps, true) . '</pre>';
        echo '<pre>' . print_r($employeeOverlapsCount, true) . '</pre>';
        echo "\n";
        */

        // print_r($employeeMap);


        for ($day = 0; $day <= 7; $day++) {
            $currentDate = Carbon::today()->addDays($day);
            $dateFormatted = $currentDate->format('Y-m-d');
            if (!$currentDate->isWeekend()) continue;
            $weekdayIndex = $currentDate->dayOfWeek;
            echo "Weekday index: $weekdayIndex for $dateFormatted\n";
        
            // Fetch the FutureShifts for the current day in the loop
            $dailyShifts = FutureShift::where('AbteilungId', 38)
                ->where('IstOptional', 0)
                ->where('ISTForderer', 0)
                ->whereNull('ObjektId')
                ->where('Beginn', 'LIKE', "$dateFormatted%") // Adjust if your start column name is different
                ->where('KlassId', 'NOT LIKE', 'KFZ%')
                ->where('KlassId', 'NOT LIKE', 'ANA')
                ->get();
        
            foreach ($dailyShifts as $shift) {
                $shiftOverlaps = [];
                $shiftVaterMatches = [];
        
                // Fetch all shifts for comparison
                $allShifts = FutureShift::where('AbteilungId', 38)
                    ->where('Beginn', 'LIKE', "$dateFormatted%")
                    ->get();
        
                foreach ($allShifts as $comparisonShift) {
                    if ($shift->id == $comparisonShift->id) continue; // Skip comparing the shift with itself
        
                    $start1 = new Carbon($shift->Beginn);
                    $end1 = new Carbon($shift->Ende);
                    $start2 = new Carbon($comparisonShift->Beginn);
                    $end2 = new Carbon($comparisonShift->Ende);
        
                    // Check for overlap
                    if ($start1 < $end2 && $start2 < $end1) {
                        // Process ObjektId for overlaps
                        $normalizedObjektId = ltrim(preg_replace('/^[a-zA-Z]+/', '', $comparisonShift->ObjektId), '0');
                        if(strlen($normalizedObjektId) > 0) {
                            $shiftOverlaps[] = $normalizedObjektId;
                        }
                    }
        
                    // Check for matching VaterId
                    if ($shift->VaterId == $comparisonShift->VaterId && $shift->VaterId != null) {
                        $normalizedObjektId = ltrim(preg_replace('/^[a-zA-Z]+/', '', $comparisonShift->ObjektId), '0');
                        if(strlen($normalizedObjektId) > 0) {
                            $shiftVaterMatches[] = $normalizedObjektId;
                        }
                    }
                }
        
                //echo "Shift ID: {$shift->id} Overlaps: \n";
                //print_r($shiftOverlaps);
                //echo "Shift ID: {$shift->id} Vater Matches: \n";
                //print_r($shiftVaterMatches);
                //echo "\n";

                $employeeScores = [];
                $employeeScoresDirect = [];
                foreach($shiftVaterMatches as $overlap) {
                    if(array_key_exists($overlap, $demandTypeCount)) {
                        foreach($demandTypeCount[$overlap] as $employeeId => $score) {
                            if(!array_key_exists($employeeId, $employeeScoresDirect)) {
                                $employeeScoresDirect[$employeeId] = 0;
                            }
                            $employeeScoresDirect[$employeeId] += $score;
                        }
                    }
                    if(array_key_exists($overlap, $employeeOverlaps)) {
                        foreach($employeeOverlaps[$overlap] as $employeeId => $score) {
                            if(!array_key_exists($employeeId, $employeeScores)) {
                                $employeeScores[$employeeId] = 0;
                            }
                            $employeeScores[$employeeId] += $score;
                        }
                    }
                }
                arsort($employeeScoresDirect);
                foreach ($employeeScoresDirect as $employeeId => $score) {
                    //echo "Employee ObjektId: $employeeId - Score: $score\n";
                }
                //echo "\n";
                //echo "\n";
            }
        }



        /*
        for ($day = 0; $day <= 7; $day++) {
            $currentDate = Carbon::today()->addDays($day);
            $dateFormatted = $currentDate->format('Y-m-d');
            if (!$currentDate->isWeekend()) continue;
            // Calculate the weekday index (Monday = 0, ..., Sunday = 6)
            $weekdayIndex = $currentDate->dayOfWeek;
            //if($weekdayIndex != 5 && $weekdayIndex != 6) continue;
            echo "Weekday index: $weekdayIndex\n";
        
            // Fetch the FutureShifts for the current day in the loop
            $dailyShifts = FutureShift::where('AbteilungId', 38)
                ->where('IstOptional', 0)
                ->where('ISTForderer', 0)
                ->whereNull('ObjektId')
                ->where('Beginn', 'LIKE', "$dateFormatted%") // Adjust if your start column name is different
                ->where('KlassId', 'NOT LIKE', 'KFZ%')
                ->get();
        
            foreach ($dailyShifts as $shift) {
                $mainGroup = $reverseDemandTypeMapping[$shift->KlassId] ?? '';
                echo "Main group: $mainGroup\n";
                $normFieldMainGroup = $mainGroup . '_norm'; 


                $startTime = Carbon::parse($shift->start, 'Europe/Vienna');
                $hour = $startTime->hour;
                $timeGroup = '';
                if ($hour >= 4 && $hour < 12) {
                    $timeGroup = 'VM';
                } elseif ($hour >= 12 && $hour < 20) {
                    $timeGroup = 'NM';
                } else {
                    $timeGroup = 'NIGHT';
                }
                $normFieldTimeGroup = $timeGroup . '_norm';

                
                $normFieldWeekdayGroup = "weekday_$startTime->dayOfWeek" . '_norm';


                $sortedMainGroupEmployeeIds = collect($employeeMap)->filter(function($groupData) use ($normFieldMainGroup) {
                    return isset($groupData[$normFieldMainGroup]) && $groupData[$normFieldMainGroup] > 0;
                })->sortByDesc($normFieldMainGroup)->keys()->all();
                //print_r($sortedMainGroupEmployeeIds);

                $sortedTimeGroupEmployeeIds = collect($employeeMap)->filter(function($groupData) use ($normFieldTimeGroup) {
                    return isset($groupData[$normFieldTimeGroup]) && $groupData[$normFieldTimeGroup] > 0;
                })->sortByDesc($normFieldTimeGroup)->keys()->all();
                //print_r($sortedTimeGroupEmployeeIds);

                $sortedWeekdayGroupEmployeeIds = collect($employeeMap)->filter(function($groupData) use ($normFieldWeekdayGroup) {
                    return isset($groupData[$normFieldWeekdayGroup]) && $groupData[$normFieldWeekdayGroup] > 0;
                })->sortByDesc($normFieldWeekdayGroup)->keys()->all();
                //print_r($sortedWeekdayGroupEmployeeIds);
                

                $allEmployeeIds = collect($sortedMainGroupEmployeeIds)
                    ->merge($sortedTimeGroupEmployeeIds)
                    ->merge($sortedWeekdayGroupEmployeeIds)
                    ->unique()
                    ->flip() // Flip to make values (initially keys) into keys
                    ->map(function () { return 0; }) // Initialize scores to 0
                    ->all();


                $weights = [
                    'MainGroup' => 3,
                    'TimeGroup' => 2,
                    'WeekdayGroup' => 1,
                ];


                $maxPossibleScores = [];
                foreach ($weights as $groupType => $weight) {
                    $sortedListName = "sorted{$groupType}EmployeeIds";
                    $sortedList = $$sortedListName; // Dynamically access the list variable
                    $lastRank = count($sortedList) + 1;
                    $maxPossibleScores[$groupType] = $lastRank * $weight; // Maximum score for this group
                }
                $totalMaxScore = array_sum($maxPossibleScores);

                foreach ($weights as $groupType => $weight) {
                    $sortedListName = "sorted{$groupType}EmployeeIds";
                    $sortedList = $$sortedListName;

                    $lastRank = count($sortedList) + 1;

                    // Assign scores based on position in the list, adjusted by weight
                    foreach ($sortedList as $rank => $employeeId) {
                        $allEmployeeIds[$employeeId] += (($rank + 1) * $weight);
                    }

                    // Adjust scores for employees not in the current list
                    foreach ($allEmployeeIds as $employeeId => $score) {
                        if (!in_array($employeeId, $sortedList)) {
                            $allEmployeeIds[$employeeId] += ($lastRank * $weight);
                        }
                    }
                }
                foreach ($allEmployeeIds as $employeeId => $score) {
                    $allEmployeeIds[$employeeId] = $score / $totalMaxScore;
                }
                asort($allEmployeeIds);
                $finalSortedEmployeeIds = array_keys($allEmployeeIds);
                print_r($finalSortedEmployeeIds);


                $calculations = array();
                $calculations['mainGroup'] = $sortedMainGroupEmployeeIds;
                $calculations['timeGroup'] = $sortedTimeGroupEmployeeIds;
                $calculations['weekdayGroup'] = $sortedWeekdayGroupEmployeeIds;
                $calculations['final'] = $finalSortedEmployeeIds;
                $shift->calculations = json_encode($calculations);
                $shift->save();


                

            }
            echo "\n";
            echo "************************************\n";
        }*/
    }

    function demandTypeMatches($demandType1, $demandType2, $mapping) {
        foreach ($mapping as $category => $types) {
            if (in_array($demandType1, $types) && in_array($demandType2, $types)) {
                return true;
            }
        }
        return false;
    }
}
