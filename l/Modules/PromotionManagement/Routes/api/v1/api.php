<?php

use Illuminate\Support\Facades\Route;
use Modules\PromotionManagement\Http\Controllers\Api\V1\Customer\CouponController;

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Api\V1\Admin', 'middleware' => ['auth:api']], function () {
    Route::resource('discount', 'DiscountController', ['only' => ['index', 'store', 'edit', 'update']]);
    Route::group(['prefix' => 'discount', 'as' => 'discount.',], function () {
        Route::put('status/update', 'DiscountController@statusUpdate');
        Route::delete('delete', 'DiscountController@destroy');
    });

    Route::resource('coupon', 'CouponController', ['only' => ['index', 'store', 'edit', 'update']]);
    Route::group(['prefix' => 'coupon', 'as' => 'coupon.',], function () {
        Route::get('config', 'CouponController@config');
        Route::put('status/update', 'CouponController@statusUpdate');
        Route::delete('delete', 'CouponController@destroy');
    });

    Route::resource('campaign', 'CampaignController', ['only' => ['index', 'store', 'edit', 'update']]);
    Route::group(['prefix' => 'campaign', 'as' => 'campaign.',], function () {
        Route::put('status/update', 'CampaignController@statusUpdate');
        Route::delete('delete', 'CampaignController@destroy');
    });

    Route::resource('banner', 'BannerController', ['only' => ['index', 'store', 'edit', 'update']]);
    Route::group(['prefix' => 'banner', 'as' => 'banner.',], function () {
        Route::put('status/update', 'BannerController@statusUpdate');
        Route::delete('delete', 'BannerController@destroy');
    });

    Route::resource('push-notification', 'PushNotificationController', ['only' => ['index', 'store', 'edit', 'update']]);
    Route::group(['prefix' => 'push-notification', 'as' => 'push-notification.',], function () {
        Route::put('status/update', 'PushNotificationController@statusUpdate');
        Route::delete('delete', 'PushNotificationController@destroy');
    });
});

Route::group(['prefix' => 'customer', 'as' => 'customer.', 'namespace' => 'Api\V1\Customer'], function () {
    Route::group(['prefix' => 'banner', 'as' => 'banner.',], function () {
        Route::get('/', 'BannerController@index');
    });

    Route::group(['prefix' => 'notification', 'as' => 'notification.',], function () {
        Route::get('/', 'NotificationController@index');
    });

    Route::resource('coupon', 'CouponController', ['only' => ['index']]);
    Route::group(['prefix' => 'coupon', 'as' => 'coupon.'], function () {
        Route::get('remove', [CouponController::class, 'removeCoupon']);
        Route::post('apply', [CouponController::class, 'applyCoupon']);
        Route::get('applicable', [CouponController::class, 'applicable']);
    });

    Route::resource('campaign', 'CampaignController', ['only' => ['index']]);
    Route::group(['prefix' => 'campaign', 'as' => 'campaign.', 'middleware' => ['auth:api']], function () {
        Route::get('data/items', 'CampaignController@campaignItems')->withoutMiddleware('auth:api');
    });

    Route::group(['prefix' => 'advertisements', 'as' => 'advertisements.'], function () {
        Route::get('ads-list', 'AdvertisementsController@AdsList')->name('ads-list');
    });
});

Route::group(['prefix' => 'provider', 'as' => 'provider.', 'namespace' => 'Api\V1\Provider', 'middleware' => ['auth:api']], function () {
    Route::group(['prefix' => 'advertisements', 'as' => 'advertisements.'], function () {
        Route::get('ads-list', 'AdvertisementsController@AdsList')->name('ads-list');
        Route::post('ads-store', 'AdvertisementsController@AdsStore')->name('ads-store');
        Route::get('details/{id}', 'AdvertisementsController@details')->name('details');
        Route::get('edit/{id}', 'AdvertisementsController@edit')->name('edit');
        Route::put('update/{id}', 'AdvertisementsController@update')->name('update');
        Route::any('status-update/{id}/{type}', 'AdvertisementsController@statusUpdate')->name('status-update');
        Route::delete('delete/{id}', 'AdvertisementsController@destroy')->name('delete');
        Route::post('store-re-submit/{id}', 'AdvertisementsController@storeReSubmit')->name('store-re-submit');
    });
});
