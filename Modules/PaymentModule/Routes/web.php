<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Modules\Gateways\Http\Controllers\PaymobController;
use Modules\PaymentModule\Http\Controllers\PaytmController;
use Modules\Gateways\Http\Controllers\MercadoPagoController;
use Modules\PaymentModule\Http\Controllers\PaymentController;
use Modules\PaymentModule\Http\Controllers\PaystackController;
use Modules\PaymentModule\Http\Controllers\RazorPayController;
use Modules\PaymentModule\Http\Controllers\SenangPayController;
use Modules\PaymentModule\Http\Controllers\FlutterwaveV3Controller;
use Modules\PaymentModule\Http\Controllers\StripePaymentController;
use Modules\PaymentModule\Http\Controllers\SubscriptionPaymentController;
use Modules\PaymentModule\Http\Controllers\Web\Admin\BonusController;
use Modules\PaymentModule\Http\Controllers\SslCommerzPaymentController;
use Modules\PaymentModule\Http\Controllers\Web\Admin\PaymentConfigController;


/** Payment */

$isPublished = 0;
try {
    $fullData = include('Modules/Gateways/Addon/info.php');
    $isPublished = $fullData['is_published'] == 1 ? 1 : 0;
} catch (\Exception $exception) {}


Route::match(['get', 'post'],'payment', [PaymentController::class, 'index']);
Route::match(['get', 'post'],'payment/subscription', [SubscriptionPaymentController::class, 'index']);

if (!$isPublished) {
    Route::group(['prefix' => 'payment'], function () {
        Route::match(['get', 'post'],'/', [PaymentController::class, 'index']);

        //SSLCOMMERZ
        Route::group(['prefix' => 'sslcommerz', 'as' => 'sslcommerz.'], function () {
            Route::get('pay', [SslCommerzPaymentController::class, 'index'])->name('pay');
            Route::post('success', [SslCommerzPaymentController::class, 'success']);
            Route::post('failed', [SslCommerzPaymentController::class, 'failed']);
            Route::post('canceled', [SslCommerzPaymentController::class, 'canceled']);
        });

        //STRIPE
        Route::group(['prefix' => 'stripe', 'as' => 'stripe.'], function () {
            Route::get('pay', [StripePaymentController::class, 'index'])->name('pay');
            Route::get('token', [StripePaymentController::class, 'payment_process_3d'])->name('token');
            Route::get('success', [StripePaymentController::class, 'success'])->name('success');
        });

        //RAZOR-PAY
        Route::group(['prefix' => 'razor-pay', 'as' => 'razor-pay.'], function () {
            Route::get('pay', [RazorPayController::class, 'index']);
            Route::post('payment', [RazorPayController::class, 'payment'])->name('payment');
        });

        //SENANG-PAY
        Route::group(['prefix' => 'senang-pay', 'as' => 'senang-pay.'], function () {
            Route::get('pay', [SenangPayController::class, 'index']);
            Route::any('callback', [SenangPayController::class, 'return_senang_pay']);
        });

        //PAYTM
        Route::group(['prefix' => 'paytm', 'as' => 'paytm.'], function () {
            Route::get('pay', [PaytmController::class, 'payment']);
            Route::any('response', [PaytmController::class, 'response'])->name('response');
        });

        //FLUTTERWAVE
        Route::group(['prefix' => 'flutterwave-v3', 'as' => 'flutterwave-v3.'], function () {
            Route::get('pay', [FlutterwaveV3Controller::class, 'initialize'])->name('pay');
            Route::get('callback', [FlutterwaveV3Controller::class, 'callback'])->name('callback');
        });

        //PAYSTACK
        Route::group(['prefix' => 'paystack', 'as' => 'paystack.'], function () {
            Route::get('pay', [PaystackController::class, 'index'])->name('pay');
            Route::post('payment', [PaystackController::class, 'redirectToGateway'])->name('payment');
            Route::get('callback', [PaystackController::class, 'handleGatewayCallback'])->name('callback');
        });
    });
}

Route::get('payment-success', [PaymentController::class, 'success'])->name('payment-success');
Route::get('payment-fail', [PaymentController::class, 'fail'])->name('payment-fail');

/** Admin */
Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Web\Admin', 'middleware' => ['admin']], function () {
    Route::group(['prefix' => 'configuration', 'as' => 'configuration.'], function () {
        Route::put('payment-set', [PaymentConfigController::class, 'setPaymentConfig'])->name('payment-set');

        Route::group(['prefix' => 'offline-payment', 'as'=>'offline-payment.'], function () {
            Route::any('list', 'OfflinePaymentController@methodList')->name('list');
            Route::get('create', 'OfflinePaymentController@methodCreate')->name('create');
            Route::post('store', 'OfflinePaymentController@methodStore')->name('store');
            Route::get('edit/{id}', 'OfflinePaymentController@methodEdit')->name('edit');
            Route::put('update', 'OfflinePaymentController@methodUpdate')->name('update');
            Route::delete('delete/{id}', 'OfflinePaymentController@methodDestroy')->name('delete');
            Route::any('status-update/{id}', 'OfflinePaymentController@statusUpdate')->name('status-update');
        });
    });

    Route::group(['prefix' => 'bonus', 'as' => 'bonus.'], function () {
        Route::any('list', [BonusController::class, 'list'])->name('list');
        Route::get('create', [BonusController::class, 'create'])->name('create');
        Route::post('store', [BonusController::class, 'store'])->name('store');
        Route::get('edit/{id}', [BonusController::class, 'edit'])->name('edit');
        Route::put('update/{id}', [BonusController::class, 'update'])->name('update');
        Route::delete('delete/{id}', [BonusController::class, 'destroy'])->name('delete');
        Route::any('status-update/{id}', [BonusController::class, 'statusUpdate'])->name('status-update');
        Route::any('download', [BonusController::class, 'download'])->name('download');
    });
});
