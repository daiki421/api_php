<?php

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::match(['get', 'post'], '/ver','API\VerController@index');
// Route::post('check','API\UserCheckController@check');
// Route::post('get','API\UserRankingController@get');
// Route::post('set','API\UserRankingController@set');
Route::post('ver','API\VerController@index');
Route::post('check','API\UserCheckController@check');
Route::post('get','API\UserRankingController@get');
Route::post('set','API\UserRankingController@set');