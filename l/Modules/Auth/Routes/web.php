<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\Web\PasswordResetController;
use Modules\Auth\Http\Controllers\Web\VerificationController;

Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {
    Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
        Route::get('login', 'LoginController@loginForm')->name('login');
        Route::post('login', 'LoginController@adminLogin');
        Route::get('logout', 'LoginController@logout')->name('logout');
    });
});

Route::group(['prefix' => 'provider', 'as' => 'provider.', 'namespace' => null], function () {
    Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
        Route::get('sign-up', 'RegisterController@providerSelfRegisterForm')->name('sign-up');
        Route::post('sign-up', 'RegisterController@providerSelfRegister')->name('sign-up-submit');

        Route::get('login', 'LoginController@providerLoginForm')->name('login');
        Route::post('login', 'LoginController@providerLogin');
        Route::get('logout', 'LoginController@logout')->name('logout');

        Route::group(['prefix' => 'reset-password', 'as' => 'reset-password.'], function () {
            Route::get('/', [PasswordResetController::class, 'index'])->name('index');

            Route::post('send-otp', [PasswordResetController::class, 'sendOtp'])->name('send-otp');
            Route::post('verify-otp', [PasswordResetController::class, 'verifyOtp'])->name('verify-otp');
            Route::post('change-password', [PasswordResetController::class, 'changePassword'])->name('change-password');
        });

        Route::group(['prefix' => 'verification', 'as' => 'verification.'], function () {
            Route::get('/', [VerificationController::class, 'index'])->name('index');

            Route::post('send-otp', [VerificationController::class, 'sendOtp'])->name('send-otp');
            Route::post('verify-otp', [VerificationController::class, 'verifyOtp'])->name('verify-otp');
        });
    });

});

Route::group(['prefix' => 'customer', 'as' => 'customer', 'namespace' => 'Api\V1'], function () {
    Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
        Route::post('registration', 'RegisterController@customerRegister')->name('registration');
        Route::post('login', 'LoginController@customerLogin')->name('login');
        Route::post('social-login', 'LoginController@socialCustomerLogin')->name('social-login');
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
