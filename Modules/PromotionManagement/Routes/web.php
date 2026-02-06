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

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Web\Admin', 'middleware' => ['admin']], function () {

    Route::group(['prefix' => 'discount', 'as' => 'discount.'], function () {
        Route::any('create', 'DiscountController@create')->name('create');
        Route::any('list', 'DiscountController@index')->name('list');
        Route::post('store', 'DiscountController@store')->name('store');
        Route::get('edit/{id}', 'DiscountController@edit')->name('edit');
        Route::put('update/{id}', 'DiscountController@update')->name('update');
        Route::any('status-update/{id}', 'DiscountController@statusUpdate')->name('status-update');
        Route::delete('delete/{id}', 'DiscountController@destroy')->name('delete');
        Route::any('download', 'DiscountController@download')->name('download');
    });

    Route::group(['prefix' => 'coupon', 'as' => 'coupon.'], function () {
        Route::any('create', 'CouponController@create')->name('create');
        Route::any('list', 'CouponController@index')->name('list');
        Route::post('store', 'CouponController@store')->name('store');
        Route::get('edit/{id}', 'CouponController@edit')->name('edit');
        Route::put('update/{id}', 'CouponController@update')->name('update');
        Route::any('status-update/{id}', 'CouponController@statusUpdate')->name('status-update');
        Route::delete('delete/{id}', 'CouponController@destroy')->name('delete');
        Route::any('download', 'CouponController@download')->name('download');
    });

    Route::group(['prefix' => 'campaign', 'as' => 'campaign.'], function () {
        Route::any('create', 'CampaignController@create')->name('create');
        Route::any('list', 'CampaignController@index')->name('list');
        Route::post('store', 'CampaignController@store')->name('store');
        Route::get('edit/{id}', 'CampaignController@edit')->name('edit');
        Route::put('update/{id}', 'CampaignController@update')->name('update');
        Route::any('status-update/{id}', 'CampaignController@statusUpdate')->name('status-update');
        Route::delete('delete/{id}', 'CampaignController@destroy')->name('delete');
        Route::any('download', 'CampaignController@download')->name('download');
    });

    Route::group(['prefix' => 'advertisements', 'as' => 'advertisements.'], function () {
        Route::get('ads-create', 'AdvertisementsController@AdsCreate')->name('ads-create');
        Route::get('ads-list', 'AdvertisementsController@AdsList')->name('ads-list');
        Route::post('ads-store', 'AdvertisementsController@AdsStore')->name('ads-store');
        Route::get('new-ads-request', 'AdvertisementsController@newAdsRequest')->name('new-ads-request');
        Route::get('details/{id}', 'AdvertisementsController@details')->name('details');
        Route::delete('delete/{id}', 'AdvertisementsController@destroy')->name('delete');
        Route::any('status-update/{id}/{type}', 'AdvertisementsController@statusUpdate')->name('status-update');
        Route::any('download', 'AdvertisementsController@download')->name('download');
        Route::get('payment-update/{id}', 'AdvertisementsController@paymentUpdate')->name('payment-update');
        Route::get('dates-update/{id}', 'AdvertisementsController@datesUpdate')->name('dates-update');
        Route::any('set-priority/{id}', 'AdvertisementsController@setPriority')->name('set-priority');
        Route::get('edit/{id}', 'AdvertisementsController@edit')->name('edit');
        Route::put('update/{id}', 'AdvertisementsController@update')->name('update');
        Route::get('re-submit/{id}', 'AdvertisementsController@reSubmit')->name('re-submit');
        Route::post('store-re-submit/{id}', 'AdvertisementsController@storeReSubmit')->name('store-re-submit');

    });

    Route::group(['prefix' => 'banner', 'as' => 'banner.'], function () {
        Route::any('create', 'BannerController@create')->name('create');
        Route::post('store', 'BannerController@store')->name('store');
        Route::get('edit/{id}', 'BannerController@edit')->name('edit');
        Route::put('update/{id}', 'BannerController@update')->name('update');
        Route::any('status-update/{id}', 'BannerController@statusUpdate')->name('status-update');
        Route::delete('delete/{id}', 'BannerController@destroy')->name('delete');
        Route::any('download', 'BannerController@download')->name('download');

    });

    Route::group(['prefix' => 'push-notification', 'as' => 'push-notification.'], function () {
        Route::any('create', 'PushNotificationController@create')->name('create');
        Route::post('store', 'PushNotificationController@store')->name('store');
        Route::get('edit/{id}', 'PushNotificationController@edit')->name('edit');
        Route::put('update/{id}', 'PushNotificationController@update')->name('update');
        Route::any('status-update/{id}', 'PushNotificationController@statusUpdate')->name('status-update');
        Route::delete('delete/{id}', 'PushNotificationController@destroy')->name('delete');
        Route::any('download', 'PushNotificationController@download')->name('download');
    });

});

Route::group(['prefix' => 'provider', 'as' => 'provider.', 'namespace' => 'Web\Provider', 'middleware' => ['provider']], function () {

    Route::group(['prefix' => 'advertisements', 'as' => 'advertisements.', 'middleware' => 'subscription:advertisement'], function () {
        Route::any('ads-create', 'AdvertisementsController@AdsCreate')->name('ads-create');
        Route::any('ads-list', 'AdvertisementsController@AdsList')->name('ads-list');
        Route::post('ads-store', 'AdvertisementsController@AdsStore')->name('ads-store');
        Route::any('download', 'AdvertisementsController@download')->name('download');
        Route::get('details/{id}', 'AdvertisementsController@details')->name('details');
        Route::get('edit/{id}', 'AdvertisementsController@edit')->name('edit');
        Route::put('update/{id}', 'AdvertisementsController@update')->name('update');
        Route::any('status-update/{id}/{type}', 'AdvertisementsController@statusUpdate')->name('status-update');
        Route::delete('delete/{id}', 'AdvertisementsController@destroy')->name('delete');
        Route::get('re-submit/{id}', 'AdvertisementsController@reSubmit')->name('re-submit');
        Route::post('store-re-submit/{id}', 'AdvertisementsController@storeReSubmit')->name('store-re-submit');
    });

});

