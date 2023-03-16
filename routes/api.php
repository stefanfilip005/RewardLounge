<?php

use App\Http\Controllers\API\DemandtypesController;
use App\Http\Controllers\API\EmployeesController;
use App\Http\Controllers\API\LoginController;
use App\Http\Controllers\API\RewardsController;
use App\Http\Controllers\API\ShiftsController;
use App\Jobs\ProcessPoints;
use App\Models\Demandtype;
use App\Models\Employee;
use App\Models\User;
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




Route::post('login',function(Request $request){
    $email = "stefan.filip.005@gmail.com";
    $user = User::where('email',$email)->first();
    if($user == null){
        $user = new User();
        $user->name = "Stefan Filip";
        $user->email = $email;
        $user->password = bcrypt("abcd");
        $user->save();
    }
    Auth::login($user);

    return response()->json([
        'status' => true,
        'message' => 'User Logged In Successfully',
        'token' => $user->createToken("API TOKEN")->plainTextToken
    ], 200);

})->name('login');;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



Route::get('/test', [LoginController::class, 'index']);

Route::apiResource("rewards", RewardsController::class);
Route::apiResource("demandtypes", DemandtypesController::class);
Route::apiResource("shifts", ShiftsController::class);




Route::apiResource("employees", EmployeesController::class)->only(['index','show']);


Route::prefix('self')->group(function () {
    Route::get("ranking", [EmployeesController::class, 'selfRanking']);
    Route::get("shifts", [EmployeesController::class, 'selfShifts']);
});


/*
Route::get('startPointsCalculation',function(Request $request){
    $employee = Employee::find(40);
    ProcessPoints::dispatchAfterResponse($employee);
});
*/


Route::get('startPointsCalculationForAllEmployees',function(Request $request){
    $employees = Employee::get();
    foreach($employees as $employee){
        ProcessPoints::dispatchAfterResponse($employee);
    }
});
