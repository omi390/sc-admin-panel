<?php

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

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'admin', 'as'=>'admin.', 'namespace' => 'Web\Admin','middleware'=>['admin']], function () {
    Route::group(['prefix'=>'configuration', 'as'=>'configuration.'],function (){
        Route::get('sms-get', 'SMSConfigController@smsConfigGet')->name('sms-get');
        Route::put('sms-set', 'SMSConfigController@smsConfigSet')->name('sms-set');
    });
});
