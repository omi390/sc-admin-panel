<?php

use Illuminate\Support\Facades\Route;
use Modules\BookingModule\Http\Controllers\Web\Admin\BookingController;
use Modules\BookingModule\Http\Controllers\Web\Provider\BookingController as ProviderBookingController;

Route::get('invoice', function () {
    $booking = \Modules\BookingModule\Entities\Booking::first();
    return view('bookingmodule::mail-templates.booking-request-sent', compact('booking'));
});

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Web\Admin', 'middleware' => ['admin']], function () {

    Route::group(['prefix' => 'booking', 'as' => 'booking.'], function () {
        Route::any('list', [BookingController::class, 'index'])->name('list');
        Route::any('list/verification', [BookingController::class, 'bookingVerificationList'])->name('list.verification');
        Route::any('list/offline-payment', [BookingController::class, 'bookingOfflinePaymentList'])->name('offline.payment');
        Route::get('check', [BookingController::class, 'checkBooking'])->name('check');
        Route::get('details/{id}', [BookingController::class, 'details'])->name('details');
        Route::get('status-update/{id}', [BookingController::class, 'statusUpdate'])->name('status_update');
        Route::get('verification-status-update/{id}', [BookingController::class, 'verificationUpdate'])->name('verification_status_update');
        Route::post('verification-status/{id}', [BookingController::class, 'verificationStatus'])->name('verification-status');
        Route::get('payment-update/{id}', [BookingController::class, 'paymentUpdate'])->name('payment_update');
        Route::any('schedule-update/{id}', [BookingController::class, 'scheduleUpadte'])->name('schedule_update');
        Route::get('serviceman-update/{id}', [BookingController::class, 'servicemanUpdate'])->name('serviceman_update');
        Route::post('service-address-update/{id}', [BookingController::class, 'serviceAddressUpdate'])->name('service_address_update');
        Route::any('download', [BookingController::class, 'download'])->name('download');
        Route::any('invoice/{id}', [BookingController::class, 'invoice'])->name('invoice');
        Route::any('customer-invoice/{id}/{lang}', [BookingController::class, 'customerInvoice'])->withoutMiddleware('admin');
        Route::any('provider-invoice/{id}/{lang}', [BookingController::class, 'providerInvoice'])->withoutMiddleware('admin');
        Route::any('serviceman-invoice/{id}/{lang}', [BookingController::class, 'servicemanInvoice'])->withoutMiddleware('admin');

        Route::any('offline-payment/verify', [BookingController::class, 'verifyOfflinePayment'])->name('offline-payment.verify');

        Route::group(['prefix' => 'service', 'as' => 'service.'], function () {
            Route::put('update-booking-service', [BookingController::class, 'updateBookingService'])->name('update_booking_service');
            Route::get('ajax-get-service-info', [BookingController::class, 'ajaxGetServiceInfo'])->name('ajax-get-service-info');
            Route::get('ajax-get-variation', [BookingController::class, 'ajaxGetVariant'])->name('ajax-get-variant');
        });
    });
});

Route::group(['prefix' => 'provider', 'as' => 'provider.', 'namespace' => 'Web\Provider', 'middleware' => ['provider']], function () {

    Route::group(['prefix' => 'booking', 'as' => 'booking.'], function () {
        Route::any('list', [ProviderBookingController::class, 'index'])->name('list');
        Route::get('check', [ProviderBookingController::class, 'checkBooking'])->name('check');
        Route::get('details/{id}', [ProviderBookingController::class, 'details'])->name('details');
        Route::put('status-update/{id}', [ProviderBookingController::class, 'statusUpdate'])->name('status_update');
        Route::put('payment-update/{id}', [ProviderBookingController::class, 'paymentUpdate'])->name('payment_update');
        Route::any('schedule-update/{id}', [ProviderBookingController::class, 'scheduleUpdate'])->name('schedule_update');
        Route::put('serviceman-update/{id}', [ProviderBookingController::class, 'servicemanUpdate'])->name('serviceman_update');
        Route::put('service-address-update/{id}', [BookingController::class, 'serviceAddressUpdate'])->name('service_address_update');
        Route::any('download', [ProviderBookingController::class, 'download'])->name('download');
        Route::any('invoice/{id}', [ProviderBookingController::class, 'invoice'])->name('invoice');
        Route::post('evidence-photos-upload/{id}', [ProviderBookingController::class, 'evidencePhotosUpload'])->name('evidence_photos_upload');
        Route::get('otp/resend', [ProviderBookingController::class, 'resendOtp'])->name('otp.resend');

        Route::group(['prefix' => 'service', 'as' => 'service.'], function () {
            Route::put('update-booking-service', [ProviderBookingController::class, 'updateBookingService'])->name('update_booking_service');
            Route::get('ajax-get-service-info', [ProviderBookingController::class, 'ajaxGetServiceInfo'])->name('ajax-get-service-info');
            Route::get('ajax-get-variation', [ProviderBookingController::class, 'ajaxGetVariant'])->name('ajax-get-variant');
        });
    });
});
