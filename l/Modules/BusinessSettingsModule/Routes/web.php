<?php

use Illuminate\Support\Facades\Route;
use Modules\BusinessSettingsModule\Http\Controllers\Web\Admin\BusinessInformationController;
use Modules\BusinessSettingsModule\Http\Controllers\Web\Admin\LanguageController;
use Modules\BusinessSettingsModule\Http\Controllers\Web\Admin\LoginSetupController;
use Modules\BusinessSettingsModule\Http\Controllers\Web\Provider\BusinessInformationController as ProviderBusinessInformationController;
use Modules\BusinessSettingsModule\Http\Controllers\Web\Admin\ConfigurationController;

Route::group(['namespace' => 'Api\V1\Admin'], function () {
    Route::get('file-manager', 'FileManagerController@index');
});


Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Web\Admin', 'middleware' => ['admin']], function () {

    Route::get('lang/{locale}', 'LanguageController@lang')->name('lang');

    Route::group(['prefix' => 'business-settings', 'as' => 'business-settings.'], function () {
        Route::post('maintenance-mode-setup', [BusinessInformationController::class, 'maintenanceModeSetup'])->name('maintenance-mode-setup');
        Route::get('maintenance-mode-status-update', [BusinessInformationController::class, 'maintenanceModeStatusUpdate'])->name('maintenance-mode-status-update');
        Route::get('ajax-currency-change', [BusinessInformationController::class, 'ajaxCurrencyChange'])->name('ajax-currency-change');

        Route::get('get-business-information', 'BusinessInformationController@businessInformationGet')->name('get-business-information');
        Route::put('set-business-information', 'BusinessInformationController@businessInformationSet')->name('set-business-information');

        Route::put('set-bidding-system', 'BusinessInformationController@setBiddingSystem')->name('set-bidding-system');

        Route::put('update-action-status', 'BusinessInformationController@updateActionStatus')->name('update-action-status');
        Route::put('set-promotion-setup', 'BusinessInformationController@promotionSetupSet')->name('set-promotion-setup');
        Route::put('set-customer-setup', 'BusinessInformationController@customerSetup')->name('set-customer-setup');
        Route::put('set-provider-setup', 'BusinessInformationController@providerSetup')->name('set-provider-setup');
        Route::put('set-service-setup', 'BusinessInformationController@serviceSetup')->name('set-service-setup');
        Route::put('set-servicemen', 'BusinessInformationController@servicemen')->name('set-servicemen');

        Route::put('set-booking-setup', 'BusinessInformationController@bookingSetupSet')->name('set-booking-setup');

        Route::get('get-pages-setup', 'BusinessInformationController@pagesSetupGet')->name('get-pages-setup');
        Route::post('set-pages-setup', 'BusinessInformationController@pagesSetupSet')->name('set-pages-setup');

        //Gallery
        Route::get('get-gallery-setup/{storage_path?}', [BusinessInformationController::class, 'gallerySetupGet'])->name('get-gallery-setup');
        Route::post('/image-upload', [BusinessInformationController::class, 'galleryImageUpload'])->name('upload-gallery-image');
        Route::get('/image-download/{file_name}', [BusinessInformationController::class, 'galleryImageDownload'])->name('download-gallery-image');
        Route::delete('/delete/{file_path}', [BusinessInformationController::class, 'galleryImageRemove'])->name('remove-gallery-image');
        Route::get('download/public', [BusinessInformationController::class, 'downloadPublicDirectory'])->name('download.public');

        //database backup
        Route::get('get-database-backup', [BusinessInformationController::class, 'getDatabaseBackup'])->name('get-database-backup');
        Route::get('backup-database-backup', [BusinessInformationController::class, 'backupDatabase'])->name('backup-database-backup');
        Route::get('delete-database-backup/{file_name}', [BusinessInformationController::class, 'deleteDatabaseBackup'])->name('delete-database-backup');
        Route::get('restore-database-backup/{file_name}', [BusinessInformationController::class, 'restoreDatabaseBackup'])->name('restore-database-backup');
        Route::get('download-database-backup/{file_name}', [BusinessInformationController::class, 'download'])->name('download-database-backup');
        Route::post('database-backup/update-binary-path', [BusinessInformationController::class, 'updateBinaryPath'])->name('database-backup.update-binary-path');

        Route::get('get-landing-information', 'LandingPageController@getLandingInformation')->name('get-landing-information');
        Route::put('set-landing-information', 'LandingPageController@setLandingInformation')->name('set-landing-information');
        Route::put('set-landing-feature', 'LandingPageController@setLandingFeature')->name('set-landing-feature');
        Route::put('set-landing-speciality', 'LandingPageController@setLandingSpeciality')->name('set-landing-speciality');
        Route::put('set-landing-testimonial', 'LandingPageController@setLandingTestimonial')->name('set-landing-testimonial');
        Route::delete('delete-landing-information/{page}/{id}', 'LandingPageController@deleteLandingInformation')->name('delete-landing-information');
        Route::delete('delete-landing-feature/{id}', 'LandingPageController@deleteLandingFeature')->name('delete-landing-feature');
        Route::delete('delete-landing-speciality/{id}', 'LandingPageController@deleteLandingSpeciality')->name('delete-landing-speciality');
        Route::delete('delete-landing-testimonial/{id}', 'LandingPageController@deleteLandingTestimonial')->name('delete-landing-testimonial');

        //Cron job setting
        Route::group(['prefix' => 'cron-job', 'as' => 'cron-job.'], function () {
            Route::get('/', 'CronJobController@index')->name('list');
            Route::get('status/{id}', 'CronJobController@status')->name('status');
            Route::get('edit/{id}', 'CronJobController@edit')->name('edit');
            Route::post('edit/{id}', 'CronJobController@update');
        });

        //login setup & seo setting
        Route::group(['prefix' => 'seo-setting', 'as' => 'seo.'], function () {
            Route::get('/', 'SEOSettingController@index')->name('setting');
            Route::post('add-page', 'SEOSettingController@addPage')->name('add-page');
            Route::get('edit-page/{page_title}', 'SEOSettingController@editPage')->name('edit-page');
            Route::post('update-page/{name}', 'SEOSettingController@updatePage')->name('update-page');
            Route::post('error-log-link/{id}', 'SEOSettingController@redirectLink')->name('error-log-link');
            Route::delete('error-log-destroy/{id}', 'SEOSettingController@errorLogDestroy')->name('error-log-destroy');
            Route::post('error-log-bulk-destroy', 'SEOSettingController@bulkDelete')->name('error-log-bulk-destroy');

        });
        Route::get('notification-channel', 'BusinessInformationController@notificationChannel')->name('notification-channel');
        Route::post('update-notification-status', 'BusinessInformationController@updateStatus')->name('updateNotificationStatus');

        Route::get('login/setup', [LoginSetupController::class, 'loginSetup'])->name('login.setup');
        Route::post('login-setup-update', [LoginSetupController::class, 'loginSetupUpdate'])->name('login-setup-update');
        Route::get('check-active-sms-gateway', [LoginSetupController::class, 'checkActiveSMSGateway'])->name('check-active-sms-gateway');
        Route::get('check-active-social-media', [LoginSetupController::class, 'checkActiveSocialMedia'])->name('check-active-social-media');
        Route::post('set-otp-login-information', [LoginSetupController::class, 'otpLoginInformationSet'])->name('set-otp-login-information');

    });

    Route::group(['prefix' => 'language', 'as' => 'language.'], function () {
        Route::post('store', [LanguageController::class, 'store'])->name('store');
        Route::get('update-status', [LanguageController::class, 'updateStatus'])->name('update-status');
        Route::get('update-default-status', [LanguageController::class, 'updateDefaultStatus'])->name('update-default-status');
        Route::post('update', [LanguageController::class, 'update'])->name('update');
        Route::get('translate/{lang}', [LanguageController::class, 'translate'])->name('translate');
        Route::post('translate-submit/{lang}', [LanguageController::class, 'translateSubmit'])->name('translate-submit');
        Route::post('remove-key/{lang}', [LanguageController::class, 'translateKeyRemove'])->name('remove-key');
        Route::delete('delete/{lang}', [LanguageController::class, 'delete'])->name('delete');
        Route::any('auto-translate/{lang}', [LanguageController::class, 'autoTranslate'])->name('auto-translate');
        Route::any('auto-translate-all/{lang}', [LanguageController::class, 'autoTranslateAll'])->name('auto-translate-all');

    });

    Route::group(['prefix' => 'subscription', 'as' => 'subscription.'], function () {
        Route::group(['prefix' => 'package', 'as' => 'package.'], function () {
            Route::get('list', 'SubscriptionPackageController@index')->name('list');
            Route::get('create', 'SubscriptionPackageController@create')->name('create');
            Route::post('create', 'SubscriptionPackageController@store');
            Route::get('update/{id}', 'SubscriptionPackageController@edit')->name('edit');
            Route::post('update/{id}', 'SubscriptionPackageController@update');
            Route::get('details/{id}', 'SubscriptionPackageController@details')->name('details');
            Route::any('status-update/{id}', 'SubscriptionPackageController@statusUpdate')->name('status-update');
            Route::post('change-subscription', 'SubscriptionPackageController@changeSubscription')->name('change-subscription');
            Route::post('subscription-to-commission', 'SubscriptionPackageController@subscriptionToCommission')->name('subscription-to-commission');
            Route::post('commission-to-subscription', 'SubscriptionPackageController@commissionToSubscription')->name('commission-to-subscription');
            Route::any('download', 'SubscriptionPackageController@download')->name('download');
            Route::any('transactions', 'SubscriptionPackageController@transactions')->name('transactions');
            Route::get('transactions/download', 'SubscriptionPackageController@transactionsDownload')->name('transactions.download');
            Route::get('transactions/invoice/{id}', 'SubscriptionPackageController@invoice')->name('transactions.invoice');
            Route::get('invoice/{id}/{lang}', 'SubscriptionPackageController@subscriptionInvoice')->withoutMiddleware('admin');
        });
        Route::get('settings', 'SubscriptionSettingsController@settings')->name('settings');
        Route::post('settings', 'SubscriptionSettingsController@settingsStore');

        Route::group(['prefix' => 'subscriber', 'as' => 'subscriber.'], function () {
            Route::get('list', 'SubscriberController@index')->name('list');
            Route::get('details/{id}', 'SubscriberController@details')->name('details');
            Route::post('cancel', 'SubscriberController@cancel')->name('cancel');
            Route::any('download', 'SubscriberController@download')->name('download');
            Route::any('transactions', 'SubscriberController@transactions')->name('transactions');
            Route::get('transactions/download', 'SubscriberController@transactionsDownload')->name('transactions.download');
            Route::get('transactions/invoice/{id}', 'SubscriberController@invoice')->name('transactions.invoice');
        });
    });

    Route::group(['prefix' => 'configuration', 'as' => 'configuration.'], function () {
        Route::get('get-notification-setting', 'ConfigurationController@notificationSettingsGet')->name('get-notification-setting');
        Route::put('set-notification-setting', 'ConfigurationController@notificationSettingsSet')->name('set-notification-setting');
        Route::any('set-message-setting', 'ConfigurationController@messageSettingsSet')->name('set-message-setting');

        Route::get('get-email-config', 'ConfigurationController@emailConfigGet')->name('get-email-config');
        Route::put('set-email-config', 'ConfigurationController@emailConfigSet')->name('set-email-config');
        Route::get('test-send-email', 'ConfigurationController@sendMail')->name('send-mail');

        Route::get('get-third-party-config', 'ConfigurationController@thirdPartyConfigGet')->name('get-third-party-config');
        Route::put('set-third-party-config', 'ConfigurationController@thirdPartyConfigSet')->name('set-third-party-config');

        Route::get('get-app-settings', 'ConfigurationController@appSettingsConfigGet')->name('get-app-settings');
        Route::put('set-app-settings', 'ConfigurationController@appSettingsConfigSet')->name('set-app-settings');

        Route::get('language-setup', 'ConfigurationController@languageSetup')->name('language_setup');

        Route::post('social-login-config-set', [ConfigurationController::class, 'setSocialLoginConfig'])->name('social-login-config-set');
        Route::put('email-status-update', [ConfigurationController::class, 'emailStatusUpdate'])->name('email-status-update');

        Route::put('change-storage-connection-type', [ConfigurationController::class, 'changeStorageConnectionType'])->name('change-storage-connection-type');
        Route::put('update-storage-connection', [ConfigurationController::class, 'updateStorageConnectionSettings'])->name('update-storage-connection');
    });

    Route::group(['prefix' => 'customer', 'as' => 'customer.'], function () {
        Route::get('settings', [ConfigurationController::class, 'getCustomerSettings'])->name('settings');
        Route::put('settings', [ConfigurationController::class, 'setCustomerSettings']);
    });
});

Route::group(['prefix' => 'provider', 'as' => 'provider.', 'namespace' => 'Web\Provider', 'middleware' => ['provider']], function () {
    Route::group(['prefix' => 'subscription-package', 'as' => 'subscription-package.'], function () {
        Route::get('details', 'SubscriptionPackageController@details')->name('details');
        Route::post('to-commission', 'SubscriptionPackageController@toCommission')->name('to.commission');
        Route::post('renew-payment', 'SubscriptionPackageController@renewPayment')->name('renew.payment');
        Route::post('renew-ajax', 'SubscriptionPackageController@ajaxRenewPackage')->name('renew.ajax');
        Route::post('shift-payment', 'SubscriptionPackageController@shiftPayment')->name('shift.payment');
        Route::post('shift-ajax', 'SubscriptionPackageController@ajaxShiftPackage')->name('shift.ajax');
        Route::post('purchase-payment', 'SubscriptionPackageController@purchasePayment')->name('purchase.payment');
        Route::post('purchase-ajax', 'SubscriptionPackageController@ajaxPurchasePackage')->name('purchase.ajax');
        Route::post('cancel', 'SubscriptionPackageController@cancel')->name('cancel');
        Route::get('transactions', 'SubscriptionPackageController@transactions')->name('transactions');
        Route::post('transactions', 'SubscriptionPackageController@transactions')->name('transactions');
        Route::any('download', 'SubscriptionPackageController@download')->name('download');
        Route::any('transactions', 'SubscriptionPackageController@transactions')->name('transactions');
        Route::get('transactions/download', 'SubscriptionPackageController@transactionsDownload')->name('transactions.download');
        Route::get('transactions/invoice/{id}', 'SubscriptionPackageController@invoice')->name('transactions.invoice');
    });
    Route::group(['prefix' => 'business-settings', 'as' => 'business-settings.'], function () {
        Route::get('get-business-information', [ProviderBusinessInformationController::class, 'businessInformationGet'])->name('get-business-information');
        Route::put('set-business-information', [ProviderBusinessInformationController::class, 'businessInformationSet'])->name('set-business-information');
        Route::put('availability-status', [ProviderBusinessInformationController::class, 'availabilityStatus'])->name('availability-status');
        Route::put('availability-schedule', [ProviderBusinessInformationController::class, 'availabilitySchedule'])->name('availability-schedule');
    });
    Route::group(['prefix' => 'configuration', 'as' => 'configuration.'], function () {
        Route::get('get-notification-setting', 'ConfigurationController@notificationSettingsGet')->name('get-notification-setting');
        Route::post('update-notification-status', 'ConfigurationController@updateStatus')->name('updateProviderNotification');
    });
});

Route::get('provider/public/subscription-package/transactions/download', 'Web\Provider\SubscriptionPackageController@transactionsDownload')->name('provider.public.subscription-package.transactions.download');
