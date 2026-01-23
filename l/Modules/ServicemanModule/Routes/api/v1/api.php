<?php

use Illuminate\Support\Facades\Route;
use Modules\ServicemanModule\Http\Controllers\Api\V1\Serviceman\ConfigController as ServicemanConfigController;

//provider routes
Route::group(['prefix' => 'provider', 'as' => 'provider', 'namespace' => 'Api\V1\Provider', 'middleware' => ['auth:api']], function () {

    //serviceman
    Route::resource('serviceman', 'ServicemanController', ['only' => ['index', 'store', 'edit', 'update', 'show']]);
    Route::group(['prefix' => 'serviceman', 'as' => 'serviceman.'], function () {
        Route::delete('delete', 'ServicemanController@destroy');
        Route::put('status/update', 'ServicemanController@changeActiveStatus');
    });

});

//customer section
Route::group(['prefix' => 'serviceman', 'as' => 'serviceman.', 'namespace' => 'Api\V1\Serviceman'], function () {

    Route::post('forgot-password', 'ServicemanController@forgotPassword');
    Route::post('otp-verification', 'ServicemanController@otpVerification');
    Route::put('reset-password', 'ServicemanController@resetPassword');

    Route::group(['middleware' => ['auth:api']], function () {
        Route::get('dashboard', 'ServicemanController@dashboard');

        Route::group(['prefix' => 'config'], function () {
            Route::get('/', 'ConfigController@configuration')->withoutMiddleware('auth:api');
            Route::get('get-zone-id', 'ConfigController@getZone');
            Route::get('place-api-autocomplete', 'ConfigController@placeApiAutocomplete');
            Route::get('distance-api', 'ConfigController@distanceApi');
            Route::get('place-api-details', 'ConfigController@placeApiDetails');
            Route::get('geocode-api', 'ConfigController@geocodeApi');
            Route::get('get-routes', [ServicemanConfigController::class, 'getRoutes']);
        });

        Route::get('info', 'ServicemanController@index');
        Route::put('update/profile', 'ServicemanController@updateProfile');
        Route::put('update/fcm-token', 'ServicemanController@updateFcmToken');
        Route::get('push-notifications', 'ServicemanController@pushNotifications');

        Route::group(['prefix' => 'profile', 'middleware' => ['auth:api']], function () {
            Route::put('info', 'ServicemanController@profileInfo');
            Route::put('change-password', 'ServicemanController@changePassword');
        });
    });

    Route::post('change-language', 'ServicemanController@changeLanguage');
});

