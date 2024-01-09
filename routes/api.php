<?php

use App\Http\Controllers\API\DemandtypesController;
use App\Http\Controllers\API\EmployeesController;
use App\Http\Controllers\API\LoginController;
use App\Http\Controllers\API\MultiplicationController;
use App\Http\Controllers\API\RewardsController;
use App\Http\Controllers\API\ShiftsController;
use App\Jobs\ProcessPoints;
use App\Models\Demandtype;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


use App\Models\Ranking;
use App\Models\Shift;
use Carbon\Carbon;
use App\Models\RankingDistribution;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('login',function(Request $request){ return redirect('https://intern.rkhl.at/saml2/2209a842-3461-4241-968a-2d950ea35237/login'); })->name('login');;

Route::middleware('auth:sanctum')->prefix('self')->group(function () {
    Route::get("user-profile", [EmployeesController::class, 'userProfile']);
    Route::get("ranking", [EmployeesController::class, 'selfRanking']);
    Route::get("shifts", [EmployeesController::class, 'selfShifts']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource("demandtypes", DemandtypesController::class); // ToDo - allow the show route for everyone, restrict the save routes for admins
    Route::apiResource("employees", EmployeesController::class)->only(['index','show']); // ToDo - restrict only for admins
    Route::get("teamEmployees", [EmployeesController::class, 'teamEmployees']);
    Route::get("rankingDistribution", [EmployeesController::class, 'rankingDistribution']);


    Route::prefix('multiplications')->group(function () {
        Route::get('/', [MultiplicationController::class, 'index']);
        Route::get('/{id}', [MultiplicationController::class, 'show']);
        Route::post('/', [MultiplicationController::class, 'store']);
        Route::put('/{id}', [MultiplicationController::class, 'update']);
        Route::delete('/{id}', [MultiplicationController::class, 'destroy']);
    });

});

Route::get('startPointsCalculationForAllEmployees',function(Request $request){
    $employees = Employee::get();
    foreach($employees as $employee){
        ProcessPoints::dispatchAfterResponse($employee);
    }
    echo "Punkte werden im Hintergrund berechnet, das kann 1-2 Minuten dauern";
});
Route::get('startRankingCalculation',function(Request $request){
    $year = 2023;
    $yearStart = Carbon::create(2023,1,1,0,0,0,"Europe/Vienna");
    $yearEnd = Carbon::create($year+1,1,1,0,0,0,"Europe/Vienna");
    $employees = array();
    $shifts = Shift::where('start','>=',$yearStart)->where('start','<=',$yearEnd)->get();
    foreach($shifts as $shift){
        if(!isset($employees[$shift->employeeId])){
            $employees[$shift->employeeId] = [
                'remoteId' => $shift->employeeId,
                'place' => 0,
                'pointsForNext' => 0,
                'points' => 0,
                'year' => $year
            ];
        }
        $employees[$shift->employeeId]['points'] += $shift->points;
    }
    foreach($employees as $key => $employee){
        $employees[$key]['points'] = floor($employee['points']);
        if($employee['points'] == 0){
            unset($employees[$key]);
        }
    }

    usort($employees, function($a, $b){ return $b['points'] - $a['points']; });

    $previousHighscore = PHP_INT_MAX;
    $place = 1;
    $platzierungCounter = 1;

    if(count($employees) > 2){
        for( $i = 0 ; $i < count($employees) ; $i++ ){
            if($i == 0){
            }else if($i >= count($employees)-1){
                if($employees[$i]['points'] == $employees[$i-1]['points']){
                    $employees[$i]['pointsForNext'] = 1;
                }else{
                    $employees[$i]['pointsForNext'] = $employees[$i-1]['points'] - $employees[$i]['points'];
                }
            }else{
                if($employees[$i]['points'] == $employees[$i-1]['points'] || $employees[$i]['points'] == $employees[$i+1]['points']){
                    $employees[$i]['pointsForNext'] = 1;
                }else{
                    $employees[$i]['pointsForNext'] = $employees[$i-1]['points'] - $employees[$i]['points'];
                }
            }

            $employees[$i]['place'] = $place;
            if($previousHighscore != $employees[$i]['points']){
                $place = $platzierungCounter;
                $employees[$i]['place'] = $place;
                $previousHighscore = $employees[$i]['points'];
            }
            $platzierungCounter++;
        }
        Ranking::where('year',$year)->delete();
        Ranking::upsert($employees,['year','remoteId'],['place','points','pointsForNext']);
    }

    
    $year = 2023;
    $yearStart = Carbon::create(2023,1,1,0,0,0,"Europe/Vienna");
    $yearEnd = Carbon::create($year+1,1,1,0,0,0,"Europe/Vienna");

    $rankings = array();
    $shifts = Shift::where('start','>=',$yearStart)->where('start','<=',$yearEnd)->get();
    foreach($shifts as $shift){
        if(!isset($rankings[$shift->employeeId])){
            $rankings[$shift->employeeId] = 0;
        }
        $rankings[$shift->employeeId] += $shift->points;
    }
    foreach($rankings as $key => $employee){
        $rankings[$key] = floor($employee);
        if($employee == 0){
            unset($rankings[$key]);
        }
    }

    usort($rankings, function($a, $b){ return $a - $b; });


    $firstPos = floor(count($rankings)/4);
    $secondPos = floor(count($rankings)/4*2);
    $thirdPos = floor(count($rankings)/4*3);

    $limits = array();
    $limits[] = floor($rankings[$firstPos] * 0.33);
    $limits[] = floor($rankings[$firstPos] * 0.66);
    $limits[] = floor($rankings[$firstPos]);

    $limits[] = floor($rankings[$firstPos] + ($rankings[$secondPos] - $rankings[$firstPos]) * 0.33);
    $limits[] = floor($rankings[$firstPos] + ($rankings[$secondPos] - $rankings[$firstPos]) * 0.66);
    $limits[] = floor($rankings[$secondPos]);

    $limits[] = floor($rankings[$secondPos] + ($rankings[$thirdPos] - $rankings[$secondPos]) * 0.33);
    $limits[] = floor($rankings[$secondPos] + ($rankings[$thirdPos] - $rankings[$secondPos]) * 0.66);
    $limits[] = floor($rankings[$thirdPos]);

    $limits[] = floor($rankings[$thirdPos] + ($rankings[count($rankings)-1] - $rankings[$thirdPos]) * 0.33);
    $limits[] = floor($rankings[$thirdPos] + ($rankings[count($rankings)-1] - $rankings[$thirdPos]) * 0.33);

    $limits = array_unique($limits);

    $distincts = array();
    $distincts[] = [
        'year' => $year,
        'limit' => 1,
        'count' => 0
    ];
    foreach($limits as $limit){
        $distincts[] = [
            'year' => $year,
            'limit' => $limit,
            'count' => 0
        ];
    }


    foreach($rankings as $ranking){
        for($i = 0; $i < count($distincts); $i++){
            if($i == count($distincts)-1){
                if($ranking >= $distincts[$i]['limit']){
                    $distincts[$i]['count']++;
                }
            }else{
                if($ranking >= $distincts[$i]['limit'] && $ranking < $distincts[$i+1]['limit']){
                    $distincts[$i]['count']++;
                }
            }
        }
    }

    RankingDistribution::where('year','=',$year)->delete();
    RankingDistribution::insert($distincts);

    echo "Ranking wurde berechnet";

});
/*
Route::apiResource("rewards", RewardsController::class);
Route::apiResource("shifts", ShiftsController::class);
*/