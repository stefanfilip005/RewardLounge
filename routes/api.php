<?php

use App\Http\Controllers\API\DemandtypesController;
use App\Http\Controllers\API\EmployeesController;
use App\Http\Controllers\API\LoginController;
use App\Http\Controllers\API\RewardsController;
use App\Http\Controllers\API\ShiftsController;
use App\Jobs\ProcessPoints;
use App\Models\Demandtype;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
});

Route::get('startPointsCalculationForAllEmployees',function(Request $request){
    $employees = Employee::get();
    foreach($employees as $employee){
        ProcessPoints::dispatchAfterResponse($employee);
    }
});
/*
Route::apiResource("rewards", RewardsController::class);
Route::apiResource("shifts", ShiftsController::class);
*/