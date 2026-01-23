<?php

use Illuminate\Support\Facades\Route;
use Modules\ProviderManagement\Http\Controllers\Web\Provider\LanguageController;
use Modules\ProviderManagement\Http\Controllers\Web\Provider\Report\Business\OverviewReportController;
use Modules\ProviderManagement\Http\Controllers\Web\Provider\Report\BookingReportController;
use Modules\ProviderManagement\Http\Controllers\Web\Provider\Report\Business\EarningReportController;
use Modules\ProviderManagement\Http\Controllers\Web\Provider\Report\Business\ExpenseReportController;
use Modules\ProviderManagement\Http\Controllers\Web\Provider\Report\TransactionReportController;

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
    Route::group(['prefix' => 'provider', 'as' => 'provider.'], function () {
        Route::any('list', 'ProviderController@index')->name('list');
        Route::any('status-update/{id}', 'ProviderController@statusUpdate')->name('status_update');
        Route::any('service-availability/{id}', 'ProviderController@serviceAvailability')->name('service_availability');
        Route::any('suspend-update/{id}', 'ProviderController@suspendUpdate')->name('suspend_update');
        Route::post('commission-update/{id}', 'ProviderController@commissionUpdate')->name('commission_update');

        Route::get('available-provider', 'ProviderController@availableProviderList')->name('available-provider-list');
        Route::get('provider-info', 'ProviderController@providerInfo')->name('provider-info');
        Route::put('reassign-provider/{id}', 'ProviderController@reassignProvider')->name('reaasign-provider');

        Route::get('create', 'ProviderController@create')->name('create');
        Route::post('store', 'ProviderController@store')->name('store');
        Route::get('edit/{id}', 'ProviderController@edit')->name('edit');
        Route::put('update/{id}', 'ProviderController@update')->name('update');
        Route::delete('delete/{id}', 'ProviderController@destroy')->name('delete');
        Route::any('details/{id}', 'ProviderController@details')->name('details');
        Route::any('download', 'ProviderController@download')->name('download');
        Route::any('reviews/download', 'ProviderController@reviewsDownload')->name('reviews.download');
        Route::get('get-provider-info/{provider_id}', 'ProviderController@getProviderInfo')->name('get-provider-info')->withoutMiddleware('admin');

        Route::group(['prefix' => 'subscription-package', 'as' => 'subscription-package.'], function () {
            Route::get('details', 'SubscriptionController@details')->name('details');
            Route::post('to-commission', 'SubscriptionController@toCommission')->name('to.commission');
            Route::post('renew-payment', 'SubscriptionController@renewPayment')->name('renew.payment');
            Route::post('renew-ajax', 'SubscriptionController@ajaxRenewPackage')->name('renew.ajax');
            Route::post('shift-payment', 'SubscriptionController@shiftPayment')->name('shift.payment');
            Route::post('shift-ajax', 'SubscriptionController@ajaxShiftPackage')->name('shift.ajax');
            Route::post('purchase-payment', 'SubscriptionController@purchasePayment')->name('purchase.payment');
            Route::post('purchase-ajax', 'SubscriptionController@ajaxPurchasePackage')->name('purchase.ajax');
            Route::post('cancel', 'SubscriptionController@cancel')->name('cancel');
        });

        Route::group(['prefix' => 'account', 'as' => 'account.'], function () {
            Route::post('update/{id}', 'ProviderController@updateAccountInfo')->name('update');
            Route::get('delete/{id}', 'ProviderController@deleteAccountInfo')->name('delete');
        });

        Route::group(['prefix' => 'sub-category', 'as' => 'sub_category.'], function () {
            Route::get('update-subscription/{id}', 'ProviderController@updateSubscription')->name('update_subscription');
        });

        Route::any('onboarding-request', 'ProviderController@onboardingRequest')->name('onboarding_request');
        Route::get('onboarding-details/{id}', 'ProviderController@onboardingDetails')->name('onboarding_details');
        Route::get('update-approval/{id}/{status}', 'ProviderController@updateApproval')->name('update-approval');

        Route::group(['prefix' => 'collect-cash', 'as' => 'collect_cash.'], function () {
            Route::get('/{id}', 'CollectCashController@index')->name('list');
            Route::post('/', 'CollectCashController@collectCash')->name('store');
        });
    });
});


Route::group(['prefix' => 'provider', 'as' => 'provider.', 'namespace' => 'Web\Provider', 'middleware' => ['provider']], function () {
    Route::post('search-routing', 'ProviderController@searchRouting')->name('search.routing');
    Route::get('lang/{locale}', [LanguageController::class, 'lang'])->name('lang');
    Route::get('get-updated-data', 'ProviderController@getUpdatedData')->name('get_updated_data');
    Route::get('dashboard', 'ProviderController@dashboard')->name('dashboard');
    Route::post('/set-modal-closed', 'ProviderController@setModalClosed')->name('set.modal.closed');
    Route::get('update-dashboard-earning-graph', 'ProviderController@updateDashboardEarningGraph')->name('update-dashboard-earning-graph');
    Route::post('subscribeToTopic', 'ProviderController@subscribeToTopic')->name('subscribeToTopic');

    Route::get('bank-info', 'ProviderController@bankInfo')->name('bank_info');
    Route::put('update-bank-info', 'ProviderController@updateBankInfo')->name('update_bank_info');

    Route::any('account-info', 'ProviderController@accountInfo')->name('account_info');
    Route::any('adjust', 'ProviderController@adjust')->name('adjust');
    Route::any('reviews/download', 'ProviderController@reviewsDownload')->name('reviews.download');

    //profile
    Route::get('profile-update', 'ProviderController@profileInfo')->name('profile_update');
    Route::post('profile-update', 'ProviderController@updateProfile');

    Route::delete('delete', 'ProviderController@deleteProvider')->name('delete_account');

    Route::group(['prefix' => 'chat', 'as' => 'chat.'], function () {
        Route::get('conversation', 'ProviderController@conversation')->name('conversation');
    });

    Route::group(['prefix' => 'sub-category', 'as' => 'sub_category.'], function () {
        Route::get('subscribed', 'ProviderController@subscribedSubCategories')->name('subscribed');
        Route::get('status-update/{id}', 'ProviderController@statusUpdate')->name('status-update');
        Route::get('available/services', 'ProviderController@availableServices')->name('available-services');
        Route::get('download', 'ProviderController@download')->name('download');
    });

    Route::group(['prefix' => 'withdraw', 'as' => 'withdraw.'], function () {
        Route::any('/', 'WithdrawController@index')->name('list');
        Route::post('/store', 'WithdrawController@withdraw')->name('store');
        Route::any('download', 'WithdrawController@download')->name('download');
    });

    Route::group(['prefix' => 'report', 'as' => 'report.', 'namespace' => 'Report', 'middleware' => 'subscription:reports_&_analytics'], function () {
        //Transaction Report
        Route::any('transaction', [TransactionReportController::class, 'getTransactionReport'])->name('transaction');
        Route::any('transaction/download', [TransactionReportController::class, 'downloadTransactionReport'])->name('transaction.download');

        //Booking Report
        Route::any('booking', [BookingReportController::class, 'getBookingReport'])->name('booking');
        Route::any('booking/download', [BookingReportController::class, 'getBookingReportDownload'])->name('booking.download');

        //Business Report
        Route::group(['prefix' => 'business', 'as' => 'business.'], function () {
            Route::any('overview', [OverviewReportController::class, 'getBusinessOverviewReport'])->name('overview');
            Route::any('overview/download', [OverviewReportController::class, 'getBusinessOverviewReportDownload'])->name('overview.download');
            Route::any('earning', [EarningReportController::class, 'getBusinessEarningReport'])->name('earning');
            Route::any('earning/download', [EarningReportController::class, 'getBusinessEarningReportDownload'])->name('earning.download');
            Route::any('expense', [ExpenseReportController::class, 'getBusinessExpenseReport'])->name('expense');
            Route::any('expense/download', [ExpenseReportController::class, 'getBusinessExpenseReportDownload'])->name('expense.download');
        });
    });
});
