<?php

use App\Http\Controllers\API\DemandtypesController;
use App\Http\Controllers\API\LoginController;
use App\Http\Controllers\API\RewardsController;
use App\Jobs\ProcessPoints;
use App\Models\Demandtype;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::apiResource("rewards", RewardsController::class);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/test', [LoginController::class, 'index']);


Route::apiResource("demandtypes", DemandtypesController::class);


Route::get('/demandtypesInUse', [DemandtypesController::class, 'demandtypesInUse']);



Route::get('startPointsCalculation',function(Request $request){
    $employee = Employee::find(40);
    ProcessPoints::dispatchAfterResponse($employee);
});


Route::get('startPointsCalculationForAllEmployees',function(Request $request){
    $employees = Employee::get();
    foreach($employees as $employee){
        ProcessPoints::dispatchAfterResponse($employee);
    }
});
