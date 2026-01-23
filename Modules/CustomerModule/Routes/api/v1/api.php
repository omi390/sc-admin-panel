<?php

use Illuminate\Support\Facades\Route;
use Modules\CustomerModule\Http\Controllers\Api\V1\Customer\AddressController;
use Modules\CustomerModule\Http\Controllers\Api\V1\Customer\CustomerController;

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

Route::group(['prefix' => 'admin', 'as'=>'admin.', 'namespace' => 'Api\V1\Admin','middleware'=>['auth:api']], function () {
    Route::resource('customer', 'CustomerController', ['only' => ['index', 'store', 'edit', 'update']]);
    Route::group(['prefix' => 'customer', 'as' => 'customer.',], function () {
        Route::put('status/update', 'CustomerController@statusUpdate');
        Route::delete('delete', 'CustomerController@destroy');

        Route::group(['prefix' => 'data', 'as' => 'data.',], function () {
            Route::get('overview/{id}', 'CustomerController@overview');
            Route::get('bookings/{id}', 'CustomerController@bookings');
            Route::get('reviews/{id}', 'CustomerController@reviews');

            Route::post('store-address', 'CustomerController@storeAddress');
            Route::get('edit-address/{id}', 'CustomerController@editAddress');
            Route::put('update-address/{id}', 'CustomerController@updateAddress');
            Route::delete('delete/{id}', 'CustomerController@destroyAddress');
        });

    });
});

Route::group(['prefix' => 'customer', 'as' => 'customer.', 'namespace' => 'Api\V1\Customer'], function () {

    Route::group(['prefix' => 'config'], function () {
        Route::get('/', 'ConfigController@configuration');
        Route::get('pages', 'ConfigController@pages');
        Route::get('get-zone-id', 'ConfigController@getZone');
        Route::get('place-api-autocomplete', 'ConfigController@placeApiAutocomplete');
        Route::get('distance-api', 'ConfigController@distanceApi');
        Route::get('place-api-details', 'ConfigController@placeApiDetails');
        Route::get('geocode-api', 'ConfigController@geocodeApi');
    });

    Route::resource('address', 'AddressController', ['only' => ['index', 'store', 'edit', 'update', 'destroy']])->withoutMiddleware(['api:auth']);
    Route::group(['middleware' => ['auth:api']], function () {
        Route::get('info', [CustomerController::class, 'index']);
        Route::put('update/profile',[CustomerController::class, 'updateProfile']);
        Route::put('update/fcm-token',[CustomerController::class, 'updateFcmToken']);
        Route::delete('remove-account', [CustomerController::class, 'removeAccount']);

        Route::post('loyalty-point/wallet-transfer', [CustomerController::class, 'transferLoyaltyPointToWallet']);
        Route::get('wallet-transaction', [CustomerController::class, 'walletTransaction']);
        Route::get('loyalty-point-transaction', [CustomerController::class, 'loyaltyPointTransaction']);
    });

    Route::post('change-language', [CustomerController::class, 'changeLanguage']);
    Route::post('error-link', [CustomerController::class, 'errorLink']);
});

