<?php

use Illuminate\Support\Facades\Route;
use Modules\ProviderManagement\Http\Controllers\Api\V1\Customer\FavoriteProviderController;
use Modules\ProviderManagement\Http\Controllers\Api\V1\Customer\ProviderController;
use Modules\ProviderManagement\Http\Controllers\Api\V1\Provider\ConfigController as ProviderConfigController;
use Modules\ProviderManagement\Http\Controllers\Api\V1\Provider\Report\BookingReportController;
use Modules\ProviderManagement\Http\Controllers\Api\V1\Provider\Report\BusinessReportController;
use Modules\ProviderManagement\Http\Controllers\Api\V1\Provider\Report\TransactionReportController;
use Modules\ProviderManagement\Http\Controllers\Api\V1\Provider\TimeScheduleController;

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

Route::group(['prefix' => 'provider', 'as' => 'provider.', 'namespace' => 'Api\V1\Provider'], function () {
    Route::post('forgot-password', 'ProviderController@forgotPassword');
    Route::post('otp-verification', 'ProviderController@otpVerification');
    Route::put('reset-password', 'ProviderController@resetPassword');
    Route::post('change-language', 'ProviderController@changeLanguage');
});

Route::group(['prefix' => 'provider', 'as' => 'provider.', 'namespace' => 'Api\V1\Provider', 'middleware' => ['auth:api']], function () {

    Route::get('/', 'ProviderController@index');
    Route::get('dashboard', 'ProviderController@dashboard');
    Route::get('get-bank-details', 'ProviderController@getBankDetails');
    Route::put('update-bank-details', 'ProviderController@updateBankDetails');

    Route::get('config', [ProviderConfigController::class, 'config'])->withoutMiddleware('auth:api');
    Route::get('info', 'ProviderController@index');
    Route::get('adjust', 'ProviderController@adjust');
    Route::get('notifications', 'ProviderController@notifications');
    Route::put('update/fcm-token', 'ProviderController@updateFcmToken');
    Route::put('update/profile', 'ProviderController@updateProfile');
    Route::get('config/get-routes', [ProviderConfigController::class, 'getRoutes']);
    Route::delete('delete', 'ProviderController@deleteProvider');
    Route::get('transaction', 'ProviderController@transaction');

    Route::get('subscribed/sub-categories', 'ProviderController@subscribedSubCategories');


    Route::group(['prefix' => 'service', 'as' => 'service.',], function () {
        Route::post('update-subscription', 'ServiceController@updateSubscription');
    });

    Route::group(['prefix' => 'account', 'as' => 'account.',], function () {
        Route::get('overview', 'AccountController@overview');
        Route::get('account-edit', 'AccountController@accountEdit');
        Route::put('account-update', 'AccountController@accountUpdate');
        Route::get('commission-info', 'AccountController@commissionInfo');
    });

    Route::resource('withdraw', 'WithdrawController', ['only' => ['index', 'store']]);
    Route::get('review', 'ProviderController@review');

    Route::get('available-time-schedule', [TimeScheduleController::class, 'getAvailableTimeSchedule']);
    Route::put('available-time-schedule', [TimeScheduleController::class, 'setAvailableTimeSchedule']);

    //REPORT
    Route::group(['prefix' => 'report', 'namespace' => 'Report'], function () {
        //Transaction Report
        Route::post('transaction', [TransactionReportController::class, 'getTransactionReport']);
        Route::post('transaction/download', [TransactionReportController::class, 'downloadTransactionReport']);

        //Booking Report
        Route::post('booking', [BookingReportController::class, 'getBookingReport']);
        Route::post('booking/download', [BookingReportController::class, 'getBookingReportDownload']);

        //Business Report
        Route::group(['prefix' => 'business', 'as' => 'business.'], function () {
            Route::post('overview', [BusinessReportController::class, 'getBusinessOverviewReport']);
            Route::post('earning', [BusinessReportController::class, 'getBusinessEarningReport']);
            Route::post('expense', [BusinessReportController::class, 'getBusinessExpenseReport']);
        });
    });
});

Route::group(['prefix' => 'customer', 'as' => 'customer.', 'namespace' => 'Api\V1\Customer'], function () {
    Route::group(['prefix' => 'provider', 'as' => 'provider.'], function () {
        Route::post('list', [ProviderController::class, 'getProviderList']);
        Route::get('list-by-sub-category', [ProviderController::class, 'getProviderListBySubCategory']);
    });

    Route::group(['prefix' => 'favorite', 'as' => 'favorite.', 'middleware' => ['auth:api']], function () {
        Route::get('provider-list', [FavoriteProviderController::class, 'list']);
        Route::post('provider', [FavoriteProviderController::class, 'store']);
        Route::post('provider-destroy/{provider_id}', [FavoriteProviderController::class, 'destroy']);
    });

    Route::get('provider-details', [ProviderController::class, 'getProviderDetails']);

    Route::post('available-provider', [ProviderController::class, 'getAvailableProvider']);
    Route::post('available-service', [ProviderController::class, 'getAvailableService']);
    Route::post('rebooking-information', [ProviderController::class, 'rebookingInformation']);
});

//admin
Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Api\V1\Admin', 'middleware' => ['auth:api']], function () {
    Route::resource('provider', 'ProviderController', ['only' => ['index', 'store', 'edit', 'update']]);
    Route::group(['prefix' => 'provider', 'as' => 'provider.',], function () {
        Route::get('data/overview/{user_id}', 'ProviderController@overview');
        Route::put('settings/update/{provider_id}', 'ProviderController@settingsUpdate');

        Route::put('status/update', 'ProviderController@statusUpdate');
        Route::delete('delete', 'ProviderController@destroy');
        Route::delete('remove-image', 'ProviderController@removeImage');

        Route::get('data/reviews/{provider_id}', 'ProviderController@reviews');
        Route::get('data/requests', 'ProviderController@providerRequest');
        Route::get('data/requests/search', 'ProviderController@searchRequest');
        Route::get('data/serviceman/list/{provider_id}', 'ProviderController@servicemanList');

        Route::get('data/bookings/{provider_id}', 'ProviderController@bookings');
        Route::get('subscribed/sub-categories/{provider_id}', 'ProviderController@subscribedSubCategories');
        Route::put('update-subscription/sub-categories/{provider_id}', 'ProviderController@updateSubscription');
    });
});
