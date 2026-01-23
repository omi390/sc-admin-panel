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
use Modules\CustomerModule\Http\Controllers\Web\Admin\LoyaltyPointController;
use Modules\CustomerModule\Http\Controllers\Web\Admin\WalletController;
use Stevebauman\Location\Facades\Location;

Route::get('about-us', 'PagesController@aboutUs')->name('about-us');
Route::get('privacy-policy', 'PagesController@privacyPolicy')->name('privacy-policy');
Route::get('terms-and-conditions', 'PagesController@termsAndConditions')->name('terms-and-conditions');
Route::get('refund-policy', 'PagesController@refundPolicy')->name('refund-policy');
Route::get('return-policy', 'PagesController@returnPolicy')->name('return-policy');
Route::get('cancellation-policy', 'PagesController@cancellationPolicy')->name('cancellation-policy');


Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Web\Admin', 'middleware' => ['admin']], function () {
    Route::group(['prefix' => 'customer', 'as' => 'customer.'], function () {
        Route::any('list', 'CustomerController@index')->name('index');
        Route::any('create', 'CustomerController@create')->name('create');
        Route::post('store', 'CustomerController@store')->name('store');
        Route::any('detail/{id}', 'CustomerController@show')->name('detail');
        Route::get('edit/{id}', 'CustomerController@edit')->name('edit');
        Route::put('update/{id}', 'CustomerController@update')->name('update');
        Route::any('status-update/{id}', 'CustomerController@statusUpdate')->name('status-update');
        Route::delete('delete/{id}', 'CustomerController@destroy')->name('delete');
        Route::any('download', 'CustomerController@download')->name('download');

        Route::group(['prefix' => 'wallet', 'as' => 'wallet.'], function () {
            Route::get('add-fund', [WalletController::class, 'addFund'])->name('add-fund');
            Route::post('add-fund', [WalletController::class, 'storeFund']);
            Route::any('report', [WalletController::class, 'getFuncReport'])->name('report');
            Route::any('report/download', [WalletController::class, 'getFuncReportDownload'])->name('report.download');
        });

        Route::group(['prefix' => 'loyalty-point', 'as' => 'loyalty-point.'], function () {
            Route::any('report', [LoyaltyPointController::class, 'getLoyaltyPointReport'])->name('report');
            Route::any('report/download', [LoyaltyPointController::class, 'getLoyaltyPointReportDownload'])->name('report.download');
        });
    });
});
