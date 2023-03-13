<?php

use App\Jobs\ProcessPoints;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\VarDumper\VarDumper;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('sso/metadata.php',function(Request $request){
    var_dump($request);
});
Route::get('sso/acs.php',function(Request $request){
    var_dump($request);
});
Route::get('sso/sls.php',function(Request $request){
    var_dump($request);
});

