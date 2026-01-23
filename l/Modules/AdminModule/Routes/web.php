<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminModule\Http\Controllers\Web\Admin\Analytics\SearchController;
use Modules\AdminModule\Http\Controllers\Web\Admin\Report\BookingReportController;
use Modules\AdminModule\Http\Controllers\Web\Admin\Report\Business\EarningReportController;
use Modules\AdminModule\Http\Controllers\Web\Admin\Report\Business\ExpenseReportController;
use Modules\AdminModule\Http\Controllers\Web\Admin\Report\Business\OverviewReportController;
use Modules\AdminModule\Http\Controllers\Web\Admin\Report\ProviderReportController;
use Modules\AdminModule\Http\Controllers\Web\Admin\Report\TransactionReportController;

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


Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Web\Admin', 'middleware' => ['admin']], function () {
    Route::post('search-routing', 'AdminController@searchRouting')->name('search.routing');
    Route::get('dashboard', 'AdminController@dashboard')->name('dashboard');
    Route::get('update-dashboard-earning-graph', 'AdminController@updateDashboardEarningGraph')->name('update-dashboard-earning-graph');
    Route::get('profile-update', 'AdminController@profileInfo')->name('profile_update');
    Route::post('profile-update', 'AdminController@updateProfile');
    Route::get('get-updated-data', 'AdminController@getUpdatedData')->name('get_updated_data');
    Route::get('subscribeToTopic', 'AdminController@subscribeToTopic')->name('subscribeToTopic');

    Route::group(['prefix' => 'role', 'as' => 'role.'], function () {
        Route::any('list', 'RoleController@index')->name('index');
        Route::any('create', 'RoleController@create')->name('create');
        Route::post('store', 'RoleController@store')->name('store');
        Route::get('edit/{id}', 'RoleController@edit')->name('edit');
        Route::put('update/{id}', 'RoleController@update')->name('update');
        Route::any('status-update/{id}', 'RoleController@statusUpdate')->name('status-update');
        Route::delete('delete/{id}', 'RoleController@destroy')->name('delete');
        Route::any('download', 'RoleController@download')->name('download');
    });

    Route::group(['prefix' => 'employee', 'as' => 'employee.'], function () {
        Route::any('list', 'EmployeeController@index')->name('index');
        Route::any('create', 'EmployeeController@create')->name('create');
        Route::post('store', 'EmployeeController@store')->name('store');
        Route::get('edit/{id}', 'EmployeeController@edit')->name('edit');
        Route::get('set-permission/{id}', 'EmployeeController@setPermission')->name('set.permission');
        Route::put('update/{id}', 'EmployeeController@update')->name('update');
        Route::any('status-update/{id}', 'EmployeeController@statusUpdate')->name('status-update');
        Route::delete('delete/{id}', 'EmployeeController@destroy')->name('delete');
        Route::any('download', 'EmployeeController@download')->name('download');
        Route::get('ajax-role-access', 'EmployeeController@ajaxRoleAccess')->name('ajax.role.access');
        Route::get('ajax-employee-role-access', 'EmployeeController@ajaxEmployeeRoleAccess')->name('ajax.employee.role.access');
    });
});

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Web\Admin'], function () {

    Route::group(['prefix' => 'report', 'as' => 'report.', 'namespace' => 'Report'], function () {
        Route::any('transaction', [TransactionReportController::class, 'getTransactionReport'])->name('transaction');
        Route::any('transaction/download', [TransactionReportController::class, 'downloadTransactionReport'])->name('transaction.download');

        Route::any('booking', [BookingReportController::class, 'getBookingReport'])->name('booking');
        Route::any('booking/download', [BookingReportController::class, 'getBookingReportDownload'])->name('booking.download');

        Route::any('provider', [ProviderReportController::class, 'getProviderReport'])->name('provider');
        Route::any('provider/download', [ProviderReportController::class, 'getProviderReportDownload'])->name('provider.download');

        Route::group(['prefix' => 'business', 'as' => 'business.'], function () {
            Route::any('overview', [OverviewReportController::class, 'getBusinessOverviewReport'])->name('overview');
            Route::any('overview/download', [OverviewReportController::class, 'getBusinessOverviewReportDownload'])->name('overview.download');
            Route::any('earning', [EarningReportController::class, 'getBusinessEarningReport'])->name('earning');
            Route::any('subscription-earning', [EarningReportController::class, 'getBusinessSubscriptionEarningReport'])->name('subscription-earning');
            Route::any('commission-earning', [EarningReportController::class, 'getBusinessCommissionEarningReport'])->name('commission-earning');
            Route::any('earning/download', [EarningReportController::class, 'getBusinessEarningReportDownload'])->name('earning.download');
            Route::any('expense', [ExpenseReportController::class, 'getBusinessExpenseReport'])->name('expense');
            Route::any('expense/download', [ExpenseReportController::class, 'getBusinessExpenseReportDownload'])->name('expense.download');
            Route::any('subscription-earning/download', [EarningReportController::class, 'subEarningDownload'])->name('subscription.download');
            Route::any('commission-earning/download', [EarningReportController::class, 'comEarningDownload'])->name('commission.download');
        });
    });

    Route::group(['prefix' => 'analytics', 'as' => 'analytics.', 'namespace' => 'Analytics'], function () {

        Route::group(['prefix' => 'search', 'as' => 'search.'], function () {
            Route::any('keyword', [SearchController::class, 'getKeywordSearchAnalytics'])->name('keyword');
            Route::any('customer', [SearchController::class, 'getCustomerSearchAnalytics'])->name('customer');
        });
    });

});
