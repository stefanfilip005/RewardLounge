<?php

use App\Http\Controllers\API\DemandtypesController;
use App\Http\Controllers\API\EmployeesController;
use App\Http\Controllers\API\InfoblattController;
use App\Http\Controllers\API\LoginController;
use App\Http\Controllers\API\LogController;
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


Route::middleware('auth:sanctum', 'log.pageview')->group(function () {
    Route::prefix('self')->group(function () {
        Route::get("user-profile", [EmployeesController::class, 'userProfile']);
        Route::get("ranking", [EmployeesController::class, 'selfRanking']);

        Route::get("shifts", [EmployeesController::class, 'selfShifts']);
        Route::get("latestShifts", [EmployeesController::class, 'latestShifts']);
        Route::get("futureShifts", [EmployeesController::class, 'futureShifts']);
    });
    Route::get("teamEmployees", [EmployeesController::class, 'teamEmployees']);
    Route::get('/infoblaetter/{year}', [InfoblattController::class, 'getInfoblaetter']);

    Route::get('/infoblaetter/{year}/{month}.pdf', [InfoblattController::class, 'getInfoblatt'])->where(['year' => '[0-9]{4}', 'month' => '[0-9]{2}']);// download pdf

});


/*
 * ----------------------------------------------------------------
 * Protected - allowed for moderators
 * ----------------------------------------------------------------
 */
Route::middleware('auth:sanctum', 'log.pageview', 'access:is.moderator')->group(function () {
    Route::get("shiftStatistics", [EmployeesController::class, 'shiftStatistics']);
    Route::apiResource("employees", EmployeesController::class)->only(['index','show']);
    Route::get("shifts", [EmployeesController::class, 'shifts']);
    Route::post('/infoblaetter/upload', [InfoblattController::class, 'upload']);
});


/*
 * ----------------------------------------------------------------
 * Protected - allowed for admins
 * ----------------------------------------------------------------
 */
Route::middleware('auth:sanctum', 'log.pageview', 'access:is.admin')->group(function () {
    Route::get('/login-logs', [LogController::class, 'loginLog']);
    Route::get('/access-logs', [LogController::class, 'accessLog']);

    Route::apiResource("demandtypes", DemandtypesController::class);
    Route::apiResource("rewards", RewardsController::class);
});


/*
 * ----------------------------------------------------------------
 * Protected - allowed for developers
 * ----------------------------------------------------------------
 */
Route::middleware('auth:sanctum', 'log.pageview', 'access:is.developer')->group(function () {

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
    EmployeesController::calculateRankings($year);
    $year = 2024;
    EmployeesController::calculateRankings($year);
});

/*
    Route::prefix('multiplications')->group(function () {
        Route::get('/', [MultiplicationController::class, 'index']);
        Route::get('/{id}', [MultiplicationController::class, 'show']);
        Route::post('/', [MultiplicationController::class, 'store']);
        Route::put('/{id}', [MultiplicationController::class, 'update']);
        Route::delete('/{id}', [MultiplicationController::class, 'destroy']);
    });
*/
