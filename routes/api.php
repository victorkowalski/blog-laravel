<?php

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
/*
| ==========================
|  Authenticate Routes
| ==========================
 */

Route::post('/logout', 'AuthController@logout')->name('logout');
Route::get('/verify/{token}', 'AuthController@verify')->name('verify');
Route::get('/verify-again', 'AuthController@verifyAgain')->name('verifyAgain');
Route::post('/verify-again', 'AuthController@resendVerification');

Route::prefix('v1')->namespace('Api\V1')->group(function () {
    Route::post('/register', 'AuthController@resgisterStore');
    Route::post('/login', 'AuthController@loginStore');
    
    Route::middleware(['jwt.auth'])->group(function () {
        Route::get('/index', 'HomeController@index');
    });
});
/*
Route::group(['middleware' => 'jwt.auth'], function () {
    Route::get('/index', 'HomeController@index');
});*/
