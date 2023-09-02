<?php

namespace App\Jobs;

use App\Models\Demandtype;
use App\Models\Employee;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use stdClass;

class ProcessPoints implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $employeeId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public Employee $employee,
    )
    {
        $this->employeeId = $employee->remoteId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $defaultRatingMap = array();
        $ratings = Demandtype::select('name','shiftType','pointsPerMinute','pointsPerShift')->get();
        foreach($ratings as $rating){
            $defaultRatingMap[$rating->name . '_X_' . $rating->shiftType] = ['pointsPerMinute' => $rating->pointsPerMinute, 'pointsPerShift' => $rating->pointsPerShift];
        }

        /*
        $ratings = array();
        $ratings['FBB'] = array();
        $rating = new stdClass();
        $rating->points = 0.15;
        $rating->start = Carbon::parse("2023-01-11 19:00:00");
        $rating->end = Carbon::parse("2023-01-11 23:59:00");
        $ratings['FBB'][] = $rating;
        */

        $points = 0;
        $shiftCounter = 0;
        $shiftUpserts = array();
        $shifts = Shift::where('employeeId',$this->employeeId)->get();
        foreach($shifts as $shift){
            $pointsForThisShift = 0;
            $shiftStart = Carbon::parse($shift->start);
            $shiftEnd = Carbon::parse($shift->end);
            $durationInMinutes = $shiftStart->diffInMinutes($shiftEnd);
            if(isset($ratings[$shift->demandType])){
                foreach($ratings[$shift->demandType] as $rating){
                    if($rating->end->greaterThan($shiftStart) && $rating->start->lessThan($shiftEnd)){
                        // There is an overlap with the rating
                        $tmpStart = $shiftStart->copy();
                        $tmpEnd = $shiftEnd->copy();
                        if($rating->start->greaterThan($shiftStart)){
                            $tmpStart = $rating->start->copy();
                        }
                        if($rating->end->lessThan($shiftEnd)){
                            $tmpEnd = $rating->end->copy();
                        }
                        $diffInMinutes = $tmpStart->diffInMinutes($tmpEnd);
                        $points += ($diffInMinutes * $rating->points);
                        $durationInMinutes -= $diffInMinutes;
                    }
                }
            }
            if(isset($defaultRatingMap[$shift->demandType . '_X_' . $shift->shiftType])){
                $pointsForThisShift = ($durationInMinutes * $defaultRatingMap[$shift->demandType . '_X_' . $shift->shiftType]['pointsPerMinute']) + $defaultRatingMap[$shift->demandType . '_X_' . $shift->shiftType]['pointsPerShift'];
                if($pointsForThisShift > 0){
                    $shiftCounter++;
                }
                $points += $pointsForThisShift;
            }
            $shiftUpserts[] = ['id' => $shift->id, 'employeeId' => $shift->employeeId, 'start' => $shift->start, 'end' => $shift->end, 'demandType' => $shift->demandType,'shiftType' => $shift->shiftType, 'location' => $shift->location, 'points' => $pointsForThisShift, 'lastPointCalculation' => Carbon::now()];
        }
        Employee::where('remoteId',$this->employeeId)->update(['points' => $points, 'shifts' => $shiftCounter, 'lastPointCalculation' => Carbon::now()]);
        Shift::upsert($shiftUpserts,['id'],['points', 'lastPointCalculation']);
    }
}
