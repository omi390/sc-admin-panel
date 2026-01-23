<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'admin', 'as' => 'admin', 'namespace' => 'Api\V1'], function () {

    Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
        Route::post('login', 'LoginController@adminLogin')->name('login');
    });

});

Route::group(['prefix' => 'provider', 'as' => 'provider', 'namespace' => 'Api\V1'], function () {

    Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
        Route::post('registration', 'RegisterController@providerRegister')->name('registration');
        Route::post('login', 'LoginController@providerLogin')->name('login');
    });

});

Route::group(['prefix' => 'customer', 'as' => 'customer', 'namespace' => 'Api\V1'], function () {
    Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
        Route::post('registration', 'RegisterController@customerRegister')->name('registration');
        Route::post('login', 'LoginController@customerLogin')->name('login');
        Route::post('social-login', 'LoginController@customerSocialLogin')->name('social-login');
        Route::post('existing-account-check', 'LoginController@existingAccountCheck');
        Route::post('registration-with-social-media', 'LoginController@registrationWithSocialMedia');
        Route::post('logout', 'LoginController@customerLogOut')->middleware('auth:api');
    });
});

Route::group(['prefix' => 'serviceman', 'as' => 'serviceman', 'namespace' => 'Api\V1'], function () {
    Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
        Route::post('login', 'LoginController@servicemanLogin')->name('login');
    });
});


Route::group(['prefix' => 'user', 'as' => 'user.', 'middleware' => ['auth:api'], 'namespace' => 'Api\V1'], function () {
    Route::post('logout', 'LoginController@logout')->name('logout');
});

