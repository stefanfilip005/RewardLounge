<?php

namespace App\Jobs;

use App\Models\Demandtype;
use App\Models\Employee;
use App\Models\Multiplication;
use App\Models\Order;
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
        $ratings = Demandtype::select('name','shiftType','pointsPerMinute','pointsPerShift','useMultiplicator')->get();
        foreach($ratings as $rating){
            $defaultRatingMap[$rating->name . '_X_' . $rating->shiftType] = ['pointsPerMinute' => $rating->pointsPerMinute, 'pointsPerShift' => $rating->pointsPerShift, 'useMultiplicator' => $rating->useMultiplicator];
        }

        //ToDo : if we have multiple Multiplications with (from/to), then we can not take the first
        $defaultMultiplicationMap = array();
        $multiplications = Multiplication::get();
        foreach($multiplications as $multiplication){
            for ($hour = 0; $hour < 24; $hour++) {
                $hourKey = str_pad($hour, 2, '0', STR_PAD_LEFT);
                $columnName = 'hour_' . $hourKey;
                $defaultMultiplicationMap[$hourKey] = $multiplication->$columnName;
            }
        }

        $points = 0;
        $shiftCounter = 0;
        $shiftUpserts = array();
        // ToDo: we need to minimize the number of shifts sometime, cause it does not need to calculate everything everytime.
        // We only have to recalculate it, if something changes in the demandtypes or multiplications.
        $shifts = Shift::where('employeeId',$this->employeeId)->get();
        foreach($shifts as $shift){
            $pointsForThisShift = 0;
            $shiftStart = Carbon::parse($shift->start);
            $shiftEnd = Carbon::parse($shift->end);
            $durationInMinutes = $shiftStart->diffInMinutes($shiftEnd);
            if(isset($defaultRatingMap[$shift->demandType . '_X_' . $shift->shiftType])){
                $pointsPerShiftWithoutMultiplication = ($durationInMinutes * $defaultRatingMap[$shift->demandType . '_X_' . $shift->shiftType]['pointsPerMinute']) + $defaultRatingMap[$shift->demandType . '_X_' . $shift->shiftType]['pointsPerShift'];
                if($defaultRatingMap[$shift->demandType . '_X_' . $shift->shiftType]['useMultiplicator']){
                    $pointsForThisShift = $defaultMultiplicationMap[$shiftStart->format('H')] * $pointsPerShiftWithoutMultiplication;
                }else{
                    $pointsForThisShift = $pointsPerShiftWithoutMultiplication;
                }

                if($pointsForThisShift > 0){
                    $shiftCounter++;
                }
                if($shift->overwrittenPoints != null){
                    $points += $shift->overwrittenPoints;
                }else{
                    $points += $pointsForThisShift;
                }
            }
            $shiftUpserts[] = ['id' => $shift->id, 'employeeId' => $shift->employeeId, 'start' => $shift->start, 'end' => $shift->end, 'demandType' => $shift->demandType,'shiftType' => $shift->shiftType, 'location' => $shift->location, 'points' => $pointsForThisShift, 'lastPointCalculation' => Carbon::now()];
        }

        $orders = Order::where('remoteId', $this->employeeId)->where('state', '!=', 5)->get();
        foreach($orders as $order){
            $points = $points - $order->total_points;
        }

        Employee::where('remoteId',$this->employeeId)->update(['points' => $points, 'shifts' => $shiftCounter, 'lastPointCalculation' => Carbon::now()]);
        Shift::upsert($shiftUpserts,['id'],['points', 'lastPointCalculation']);
    }
}
