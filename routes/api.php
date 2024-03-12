<?php

use App\Http\Controllers\API\EmployeesController;
use App\Http\Controllers\API\InfoblattController;
use App\Http\Controllers\API\LogController;
use App\Http\Controllers\API\RewardsController;
use App\Http\Controllers\API\ShiftsController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\FAQController;
use App\Http\Controllers\API\GreetingController;
use App\Http\Controllers\API\OrderController;
use App\Jobs\ProcessPoints;
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

Route::get('login',function(Request $request){ return redirect('https://intern.rkhl.at/saml2/2209a842-3461-4241-968a-2d950ea35237/login'); })->name('login');;


Route::middleware('auth:sanctum', 'log.pageview')->group(function () {
    Route::prefix('self')->group(function () {
        Route::get("user-profile", [EmployeesController::class, 'userProfile']);
        Route::get("ranking", [EmployeesController::class, 'selfRanking']);

        
        Route::get('rewards', [RewardsController::class, 'index']);
        Route::get('rewards/{reward}', [RewardsController::class, 'show']);

        Route::get("shifts", [EmployeesController::class, 'selfShifts']);
        Route::get("latestShifts", [EmployeesController::class, 'latestShifts']);
        Route::get("futureShifts", [EmployeesController::class, 'futureShifts']);


        Route::prefix('cart')->group(function () {
            Route::get('/content', [CartController::class, 'getCartContents']);
            Route::get('/count', [CartController::class, 'getCartCount']);
            Route::post('/add-item', [CartController::class, 'addItem']);
            Route::patch('/item/{itemId}/quantity', [CartController::class, 'updateItemQuantity']);
            Route::patch('/item/{itemId}/note', [CartController::class, 'updateItemNote']);
            Route::delete('/item/{itemId}', [CartController::class, 'deleteItem']);
            Route::post('/checkout', [CartController::class, 'checkout']);
        });

        Route::prefix('order')->group(function () {
            Route::get('/list', [OrderController::class, 'getSelfOrders']);
        });


    });
    Route::get('greeting', [GreetingController::class, 'show']);
    Route::get('faqs', [FAQController::class, 'index']);
    //Route::get("teamEmployees", [EmployeesController::class, 'teamEmployees']);
    Route::get('/infoblaetter/{year}', [InfoblattController::class, 'getInfoblaetter']);

    Route::get('/infoblaetter/{year}/{month}.pdf', [InfoblattController::class, 'getInfoblatt'])->where(['year' => '[0-9]{4}', 'month' => '[0-9]{2}']);// download pdf
    Route::get("shiftStatistics", [EmployeesController::class, 'shiftStatistics']);

});


/*
 * ----------------------------------------------------------------
 * Protected - allowed for moderators
 * ----------------------------------------------------------------
 */
Route::middleware('auth:sanctum', 'log.pageview', 'access:is.moderator')->group(function () {
    Route::get('/employeesFromOrders', [OrderController::class, 'employeesFromOrders']);
    Route::apiResource("employees", EmployeesController::class)->only(['index','show']);
    Route::get("shifts", [EmployeesController::class, 'shifts']);
    Route::get('/orders', [OrderController::class, 'getOrders']);
    Route::patch('/order/{orderId}/note', [OrderController::class, 'updateOrderNote']);
    Route::post('/order/{id}/change-state', [OrderController::class, 'changeOrderState']);
});


/*
 * ----------------------------------------------------------------
 * Protected - allowed for admins
 * ----------------------------------------------------------------
 */
Route::middleware('auth:sanctum', 'log.pageview', 'access:is.admin')->group(function () {
    Route::get('/login-logs', [LogController::class, 'loginLog']);
    Route::get('/access-logs', [LogController::class, 'accessLog']);
    Route::post('/infoblaetter/upload', [InfoblattController::class, 'upload']);
    Route::post('/shifts/search', [ShiftsController::class, 'search']);
    Route::post('/shifts/update-points', [ShiftsController::class, 'updatePoints']);

    Route::patch('greeting', [GreetingController::class, 'update']);
    Route::post('faqs/{id?}', [FAQController::class, 'storeOrUpdate']);
    Route::delete('faqs/{id}', [FAQController::class, 'destroy']);

    Route::post('/employees/make-admin/{id}', [EmployeesController::class, 'makeAdmin']);
    Route::post('/employees/make-moderator/{id}', [EmployeesController::class, 'makeMod']);
    Route::post('/employees/{id}/remove-roles', [EmployeesController::class, 'removeAllRoles']);

    Route::post('rewards', [RewardsController::class, 'store']);
    Route::put('rewards/{reward}', [RewardsController::class, 'update']);
    Route::delete('rewards/{reward}', [RewardsController::class, 'destroy']);
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
