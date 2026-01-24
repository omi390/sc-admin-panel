<?php

namespace Modules\PaymentModule\Traits;

use Exception;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Modules\BusinessSettingsModule\Emails\PurchaseSubscriptionMail;
use Modules\BusinessSettingsModule\Emails\RenewSubscriptionMail;
use Modules\BusinessSettingsModule\Emails\ShiftSubscriptionMail;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ProviderManagement\Entities\SubscribedService;
use Modules\BusinessSettingsModule\Entities\
{
    SubscriptionPackage,
    SubscriptionPackageFeature,
    SubscriptionPackageLimit,
    PackageSubscriberLog,
    PackageSubscriber,
    PackageSubscriberFeature,
    PackageSubscriberLimit
};

trait SubscriptionTrait
{
    public static function handleFreeTrialPackageSubscription($id, $provider, $price, $name): bool
    {
        DB::beginTransaction();

        try {

            $providerUser       = Provider::where('id', $provider)->first();
            if (!$providerUser){
                DB::commit();
                return true;
            }

            $packageFeatures    = SubscriptionPackageFeature::where('subscription_package_id', $id)->get();
            $packageLimits      = SubscriptionPackageLimit::where('subscription_package_id', $id)->get();
            $freeTrial          = (int)((business_config('free_trial_period', 'subscription_Setting'))->live_values ?? 0);

            $startDate  = Carbon::now();
            $endDate    = Carbon::now()->addDays($freeTrial)->subDay();

            $packageSubscriberLog                           = new PackageSubscriberLog();
            $packageSubscriberLog->end_date                 = $endDate;
            $packageSubscriberLog->vat_amount               = 0;
            $packageSubscriberLog->payment_id               = null;
            $packageSubscriberLog->start_date               = $startDate;
            $packageSubscriberLog->provider_id              = $provider;
            $packageSubscriberLog->package_name             = $name;
            $packageSubscriberLog->package_price            = 0.00;
            $packageSubscriberLog->vat_percentage           = 0;
            $packageSubscriberLog->subscription_package_id  = $id;
            $packageSubscriberLog->save();

            $packageSubscriber                              = PackageSubscriber::where('provider_id', $provider)->first();
            if (!$packageSubscriber) {
                $packageSubscriber                          = new PackageSubscriber();
            }

            $packageSubscriber->vat_amount                  = 0;
            $packageSubscriber->provider_id                 = $provider;
            $packageSubscriber->package_name                = $name;
            $packageSubscriber->package_price               = 0.00;
            $packageSubscriber->trial_duration              = $freeTrial;
            $packageSubscriber->vat_percentage              = 0;
            $packageSubscriber->payment_method              = null;
            $packageSubscriber->package_end_date            = $endDate;
            $packageSubscriber->package_start_date          = $startDate;
            $packageSubscriber->subscription_package_id     = $id;
            $packageSubscriber->package_subscriber_log_id   = $packageSubscriberLog->id;

            $packageSubscriber->save();

            if ($packageFeatures) {
                foreach ($packageFeatures as $feature) {
                    $packageSubscriberFeature                               = new PackageSubscriberFeature();
                    $packageSubscriberFeature->provider_id                  = $provider;
                    $packageSubscriberFeature->package_subscriber_log_id    = $packageSubscriberLog->id;
                    $packageSubscriberFeature->feature                      = $feature->feature;
                    $packageSubscriberFeature->save();
                }
            }

            if ($packageLimits) {
                PackageSubscriberLimit::where('provider_id', $provider)->delete();
                foreach ($packageLimits as $limit) {
                    $packageSubscriberLimit                             = new PackageSubscriberLimit();
                    $packageSubscriberLimit->key                        = $limit->key;
                    $packageSubscriberLimit->is_limited                 = $limit->is_limited;
                    $packageSubscriberLimit->limit_count                = $limit->limit_count;
                    $packageSubscriberLimit->provider_id                = $provider;
                    $packageSubscriberLimit->subscription_package_id    = $id;
                    $packageSubscriberLimit->save();
                }
            }

            $subscribedServices = SubscribedService::where('provider_id', $provider)->get();

            foreach ($subscribedServices as $subscribedService) {
                $subscribedService->is_subscribed = 0;
                $subscribedService->save();
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            return false;
        } finally {
            if ($providerUser){
                $accessCheck = mobileAppCheck($providerUser->owner, 'mobile_app');

                if (!$accessCheck && $providerUser && $providerUser->owner && $providerUser->owner->tokens) {
                    $providerUser->owner->tokens->each(function ($token) {
                        $token->revoke();
                    });
                }
            }

            $emailServices =  business_config('email_config_status', 'email_config');
            $emailPermission = isNotificationActive($provider, 'subscription', 'email', 'provider');
            if (isset($emailServices) && $emailServices->live_values == 1 && $emailPermission) {
                try {
                    Mail::to($packageSubscriber?->provider?->owner?->email)->send(new PurchaseSubscriptionMail($packageSubscriber->provider));
                }catch (Exception $exception){

                }
            }
        }
    }

    public static function handlePurchasePackageSubscription($id, $provider, $request, $price, $name): bool
    {
        DB::beginTransaction();

        try {
            $duration           = 0;
            $calculationVat     = 0;

            $providerUser       = Provider::where('id', $provider)->first();
            if (!$providerUser){
                DB::commit();
                return true;
            }

            $getPackage         = SubscriptionPackage::find($id);
            $packageFeatures    = SubscriptionPackageFeature::where('subscription_package_id', $id)->get();
            $packageLimits      = SubscriptionPackageLimit::where('subscription_package_id', $id)->get();
            $vatPercentage      = (int)((business_config('subscription_vat', 'subscription_Setting'))->live_values ?? 0);

            if ($getPackage) {
                $duration       = $getPackage->duration ?? 0;
                $calculationVat = $getPackage->price * ($vatPercentage / 100);
            }

            $transactionId = purchaseSubscriptionTransaction(
                amount: $price,
                provider_id: $provider,
                vat: $calculationVat
            );

            $startDate  = Carbon::now();
            $endDate    = Carbon::now()->addDays($duration)->subDay();

            $packageSubscriberLog                           = new PackageSubscriberLog();
            $packageSubscriberLog->end_date                 = $endDate;
            $packageSubscriberLog->start_date               = $startDate;
            $packageSubscriberLog->vat_amount               = $calculationVat;
            $packageSubscriberLog->payment_id               = $request['payment_id'] ?? null;
            $packageSubscriberLog->provider_id              = $provider;
            $packageSubscriberLog->package_name             = $name;
            $packageSubscriberLog->package_price            = $price;
            $packageSubscriberLog->vat_percentage           = $vatPercentage;
            $packageSubscriberLog->subscription_package_id  = $id;
            $packageSubscriberLog->primary_transaction_id  = $transactionId;
            $packageSubscriberLog->save();

            $packageSubscriber                              = new PackageSubscriber();
            $packageSubscriber->subscription_package_id     = $id;
            $packageSubscriber->package_price               = $price;
            $packageSubscriber->package_name                = $name;
            $packageSubscriber->package_start_date          = $startDate;
            $packageSubscriber->package_end_date            = Carbon::parse($packageSubscriber->package_end_date)->addDays($duration)->subDay();
            $packageSubscriber->trial_duration              = 0;
            $packageSubscriber->provider_id                 = $provider;
            $packageSubscriber->vat_percentage              = $vatPercentage;
            $packageSubscriber->vat_amount                  = $calculationVat;
            $packageSubscriber->payment_method              = $request['payment_method'] ?? null;
            $packageSubscriber->payment_id                  = $request['payment_id'] ?? null;
            $packageSubscriber->package_subscriber_log_id   = $packageSubscriberLog->id;
            $packageSubscriber->save();

            if ($packageFeatures) {
                foreach ($packageFeatures as $feature) {
                    $packageSubscriberFeature                               = new PackageSubscriberFeature();
                    $packageSubscriberFeature->provider_id                  = $provider;
                    $packageSubscriberFeature->package_subscriber_log_id    = $packageSubscriberLog->id;
                    $packageSubscriberFeature->feature                      = $feature->feature;
                    $packageSubscriberFeature->save();
                }
            }

            if ($packageLimits) {
                PackageSubscriberLimit::where('provider_id', $provider)->delete();
                foreach ($packageLimits as $limit) {
                    $packageSubscriberLimit                             = new PackageSubscriberLimit();
                    $packageSubscriberLimit->provider_id                = $provider;
                    $packageSubscriberLimit->subscription_package_id    = $id;
                    $packageSubscriberLimit->key                        = $limit->key;
                    $packageSubscriberLimit->is_limited                 = $limit->is_limited;
                    $packageSubscriberLimit->limit_count                = $limit->limit_count;
                    $packageSubscriberLimit->save();
                }
            }

            $subscribedServices = SubscribedService::where('provider_id', $provider)->get();

            foreach ($subscribedServices as $subscribedService) {
                $subscribedService->is_subscribed = 0;
                $subscribedService->save();
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            return false;

        } finally {

            if ($providerUser){
                $accessCheck = mobileAppCheck($providerUser->owner, 'mobile_app');

                if (!$accessCheck && $providerUser && $providerUser->owner && $providerUser->owner->tokens) {
                    $providerUser->owner->tokens->each(function ($token) {
                        $token->revoke();
                    });
                }
            }

            $emailServices =  business_config('email_config_status', 'email_config');
            $emailPermission = isNotificationActive($provider, 'subscription', 'email', 'provider');
            if (isset($emailServices) && $emailServices->live_values == 1 && $emailPermission) {
                try {
                    Mail::to($packageSubscriber?->provider?->owner?->email)->send(new PurchaseSubscriptionMail($packageSubscriber->provider));
                }catch (Exception $exception){

                }
            }
        }
    }

    public static function handleRenewPackageSubscription($id, $provider, $request, $price, $name): bool
    {
        DB::beginTransaction();

        try {
            $duration           = 0;
            $calculationVat     = 0;

            $providerUser       = Provider::where('id', $provider)->first();
            if (!$providerUser){
                DB::commit();
                return true;
            }

            $getPackage         = SubscriptionPackage::find($id);
            $packageFeatures    = SubscriptionPackageFeature::where('subscription_package_id', $id)->get();
            $packageLimits      = SubscriptionPackageLimit::where('subscription_package_id', $id)->get();
            $vatPercentage      = (int)((business_config('subscription_vat', 'subscription_Setting'))->live_values ?? 0);

            if ($getPackage) {
                $duration       = $getPackage->duration ?? 0;
                $calculationVat = $getPackage->price * ($vatPercentage / 100);
            }

            $transactionId = renewSubscriptionTransaction(
                amount: $price,
                provider_id: $provider,
                vat: $calculationVat
            );

            $startDate  = Carbon::now()->startOfDay();
            $endDate    = Carbon::now()->addDays($duration)->subDay();

            $packageSubscriberLog                           = new PackageSubscriberLog();
            $packageSubscriberLog->end_date                 = $endDate;
            $packageSubscriberLog->start_date               = $startDate;
            $packageSubscriberLog->vat_amount               = $calculationVat;
            $packageSubscriberLog->payment_id               = $request['payment_id'];
            $packageSubscriberLog->provider_id              = $provider;
            $packageSubscriberLog->package_name             = $name;
            $packageSubscriberLog->package_price            = $price;
            $packageSubscriberLog->vat_percentage           = $vatPercentage;
            $packageSubscriberLog->subscription_package_id  = $id;
            $packageSubscriberLog->primary_transaction_id  = $transactionId;
            $packageSubscriberLog->save();

            $packageSubscriber                              = PackageSubscriber::where('subscription_package_id', $id)->where('provider_id', $provider)->first();
            $packageSubscriber->subscription_package_id     = $id;
            $packageSubscriber->package_price               = $price;
            $packageSubscriber->package_name                = $name;
            $packageSubscriber->package_start_date          = $startDate;
            $package_end_date = Carbon::parse($packageSubscriber->package_end_date); // Convert string to Carbon instance

            $packageSubscriber->package_end_date = (Carbon::now()->startOfDay()->equalTo($package_end_date->endOfDay()) || Carbon::now()->startOfDay()->lessThanOrEqualTo($package_end_date->endOfDay())) ? $package_end_date->addDays($duration) : $endDate;
            $packageSubscriber->trial_duration              = 0;
            $packageSubscriber->provider_id                 = $provider;
            $packageSubscriber->vat_percentage              = $vatPercentage;
            $packageSubscriber->vat_amount                  = $calculationVat;
            $packageSubscriber->payment_method              = $request['payment_method'];
            $packageSubscriber->package_subscriber_log_id   = $packageSubscriberLog->id;
            $packageSubscriber->is_canceled                 = 0;
            $packageSubscriber->payment_id                  = $request['payment_id'];
            $packageSubscriber->save();

            if ($packageFeatures) {
                foreach ($packageFeatures as $feature) {
                    $packageSubscriberFeature                               = new PackageSubscriberFeature();
                    $packageSubscriberFeature->provider_id                  = $provider;
                    $packageSubscriberFeature->package_subscriber_log_id    = $packageSubscriberLog->id;
                    $packageSubscriberFeature->feature                      = $feature->feature;
                    $packageSubscriberFeature->save();
                }
            }

            if ($packageLimits) {
                foreach ($packageLimits as $limit) {
                    if ($limit->key === 'booking') {
                        $existingLimit = PackageSubscriberLimit::where('provider_id', $provider)
                            ->where('subscription_package_id', $id)
                            ->where('key', $limit->key)
                            ->first();

                        if ($existingLimit) {
                            $existingLimit->limit_count += $limit->limit_count;
                            $existingLimit->save();
                        }
                    }
                }
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            return false;

        } finally {

            if ($providerUser){
                $accessCheck = mobileAppCheck($providerUser->owner, 'mobile_app');

                if (!$accessCheck && $providerUser && $providerUser->owner && $providerUser->owner->tokens) {
                    $providerUser->owner->tokens->each(function ($token) {
                        $token->revoke();
                    });
                }
            }

            $emailServices =  business_config('email_config_status', 'email_config');
            $emailPermission = isNotificationActive($provider, 'subscription', 'email', 'provider');

            if (isset($emailServices) && $emailServices->live_values == 1 && $emailPermission) {
                try {
                    Mail::to($packageSubscriber?->provider?->owner?->email)->send(new RenewSubscriptionMail($packageSubscriber->provider));
                }
                catch (Exception $exception){

                }
            }
        }
    }

    public static function handleShiftPackageSubscription($id, $provider, $request, $price, $name): bool
    {
        DB::beginTransaction();

        try {

            $totalDuration      = 0;
            $duration           = 0;
            $calculationVat     = 0;


            $providerUser       = Provider::where('id', $provider)->first();
            if (!$providerUser){
                DB::commit();
                return true;
            }

            $getPackage         = SubscriptionPackage::find($id);
            $packageFeatures    = SubscriptionPackageFeature::where('subscription_package_id', $id)->get();
            $packageLimits      = SubscriptionPackageLimit::where('subscription_package_id', $id)->get();
            $usedTime           = (int)((business_config('usage_time', 'subscription_Setting'))->live_values ?? 0);
            $vatPercentage      = (int)((business_config('subscription_vat', 'subscription_Setting'))->live_values ?? 0);

            $subscriberLog      = PackageSubscriber::where('provider_id', $provider)->with('logs','payment')->first();
            $packageStartDate   = Carbon::parse($subscriberLog->package_start_date)->subDay();
            $packageEndDate     = Carbon::parse($subscriberLog->package_end_date);
            $now                = Carbon::now();
            $roundedPercentageUsed = 0;

            if ($subscriberLog?->payment?->is_paid){
                if ($now->lessThanOrEqualTo($packageEndDate)) {
                    $totalDuration = $packageStartDate->diffInDays($packageEndDate);
                    $daysPassed = $packageStartDate->diffInDays($now);
                    $percentageUsed = ($daysPassed / $totalDuration) * 100;
                    $roundedPercentageUsed = ceil($percentageUsed);
                }
            }

            if ($getPackage) {
                $duration       = $getPackage->duration;
                $calculationVat = $getPackage->price * ($vatPercentage / 100);
            }

            $transactionId = shiftSubscriptionTransaction(
                amount: $price,
                provider_id: $provider,
                vat: $calculationVat
            );

            if ($now->lessThanOrEqualTo($packageEndDate) && $usedTime > $roundedPercentageUsed && $totalDuration != 0){
                shiftRefundSubscriptionTransaction(
                    provider_id: $provider
                );
            }

            $startDate  = Carbon::now();
            $endDate    = Carbon::now()->addDays($duration)->subDay();

            $packageSubscriberLog                               = new PackageSubscriberLog();
            $packageSubscriberLog->end_date                     = $endDate;
            $packageSubscriberLog->vat_amount                   = $calculationVat;
            $packageSubscriberLog->payment_id                   = $request['payment_id'] ?? null;
            $packageSubscriberLog->subscription_package_id      = $id;
            $packageSubscriberLog->package_price                = $price;
            $packageSubscriberLog->package_name                 = $name;
            $packageSubscriberLog->start_date                   = $startDate;
            $packageSubscriberLog->provider_id                  = $provider;
            $packageSubscriberLog->vat_percentage               = $vatPercentage;
            $packageSubscriberLog->primary_transaction_id       = $transactionId;
            $packageSubscriberLog->save();

            $packageSubscriber                                  = PackageSubscriber::where('provider_id', $provider)->first();
            $packageSubscriber->subscription_package_id         = $id;
            $packageSubscriber->package_price                   = $price;
            $packageSubscriber->package_name                    = $name;
            $packageSubscriber->package_start_date              = $startDate;
            $packageSubscriber->package_end_date                = $endDate;
            $packageSubscriber->trial_duration                  = 0;
            $packageSubscriber->provider_id                     = $provider;
            $packageSubscriber->vat_percentage                  = $vatPercentage;
            $packageSubscriber->vat_amount                      = $calculationVat;
            $packageSubscriber->payment_method                  = $request['payment_method']  ?? null;
            $packageSubscriber->package_subscriber_log_id       = $packageSubscriberLog->id;
            $packageSubscriber->is_canceled                     = 0;
            $packageSubscriber->payment_id                      = $request['payment_id']  ?? null;
            $packageSubscriber->save();

            if ($packageFeatures) {
                foreach ($packageFeatures as $feature) {
                    $packageSubscriberFeature                               = new PackageSubscriberFeature();
                    $packageSubscriberFeature->provider_id                  = $provider;
                    $packageSubscriberFeature->package_subscriber_log_id    = $packageSubscriberLog->id;
                    $packageSubscriberFeature->feature                      = $feature->feature;
                    $packageSubscriberFeature->save();
                }
            }

            if ($packageLimits) {
                PackageSubscriberLimit::where('provider_id', $provider)->delete();
                foreach ($packageLimits as $limit) {
                    $packageSubscriberLimit = new PackageSubscriberLimit();
                    $packageSubscriberLimit->provider_id = $provider;
                    $packageSubscriberLimit->subscription_package_id = $id;
                    $packageSubscriberLimit->key = $limit->key;
                    $packageSubscriberLimit->is_limited = $limit->is_limited;
                    $packageSubscriberLimit->limit_count = $limit->limit_count;
                    $packageSubscriberLimit->save();
                }
            }

            $subscribedServices = SubscribedService::where('provider_id', $provider)->get();

            foreach ($subscribedServices as $subscribedService) {
                $subscribedService->is_subscribed = 0;
                $subscribedService->save();
            }

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            return false;

        } finally {

            if ($providerUser){
                $accessCheck = mobileAppCheck($providerUser->owner, 'mobile_app');

                if (!$accessCheck && $providerUser && $providerUser->owner && $providerUser->owner->tokens) {
                    $providerUser->owner->tokens->each(function ($token) {
                        $token->revoke();
                    });
                }
            }

            $emailServices =  business_config('email_config_status', 'email_config');
            $emailPermission = isNotificationActive($provider, 'subscription', 'email', 'provider');

            if (isset($emailServices) && $emailServices->live_values == 1 && $emailPermission) {
                try {
                    Mail::to($packageSubscriber?->provider?->owner?->email)->send(new ShiftSubscriptionMail($packageSubscriber->provider));
                }catch (Exception $exception){

                }
            }
        }
    }

    public static function handlePurchaseSubscriptionFailed($id, $provider, $request, $price, $name): bool
    {
        DB::beginTransaction();
        $freeTrial = (int)((business_config('free_trial_period', 'subscription_Setting'))->live_values ?? 0);
        $freeTrialStatus = (int)((business_config('free_trial_period', 'subscription_Setting'))->is_active);

        try {
            $providerUser       = Provider::where('id', $provider)->first();
            if (!$providerUser){
                DB::commit();
                return true;
            }

            if ($freeTrial){
                $packageFeatures    = SubscriptionPackageFeature::where('subscription_package_id', $id)->get();
                $packageLimits      = SubscriptionPackageLimit::where('subscription_package_id', $id)->get();

                $startDate = Carbon::now();
                $endDate = Carbon::now()->addDays($freeTrial)->subDay();

                $packageSubscriberLog = new PackageSubscriberLog();
                $packageSubscriberLog->end_date = $endDate;
                $packageSubscriberLog->vat_amount = 0;
                $packageSubscriberLog->payment_id = null;
                $packageSubscriberLog->start_date = $startDate;
                $packageSubscriberLog->provider_id = $provider;
                $packageSubscriberLog->package_name = $name;
                $packageSubscriberLog->package_price = 0.00;
                $packageSubscriberLog->vat_percentage = 0;
                $packageSubscriberLog->subscription_package_id = $id;
                $packageSubscriberLog->save();

                $packageSubscriber = new PackageSubscriber();
                $packageSubscriber->vat_amount = 0;
                $packageSubscriber->provider_id = $provider;
                $packageSubscriber->package_name = $name;
                $packageSubscriber->package_price = 0.00;
                $packageSubscriber->trial_duration = $freeTrialStatus ? $freeTrial : 0;
                $packageSubscriber->vat_percentage = 0;
                $packageSubscriber->payment_method = null;
                $packageSubscriber->payment_id = $request['payment_id'];
                $packageSubscriber->package_end_date = $endDate;
                $packageSubscriber->package_start_date = $startDate;
                $packageSubscriber->subscription_package_id = $id;
                $packageSubscriber->package_subscriber_log_id = $packageSubscriberLog->id;
                $packageSubscriber->save();

                if ($packageFeatures) {
                    foreach ($packageFeatures as $feature) {
                        $packageSubscriberFeature = new PackageSubscriberFeature();
                        $packageSubscriberFeature->provider_id = $provider;
                        $packageSubscriberFeature->package_subscriber_log_id = $packageSubscriberLog->id;
                        $packageSubscriberFeature->feature = $feature->feature;
                        $packageSubscriberFeature->save();
                    }
                }

                if ($packageLimits) {
                    foreach ($packageLimits as $limit) {
                        $packageSubscriberLimit = new PackageSubscriberLimit();
                        $packageSubscriberLimit->key = $limit->key;
                        $packageSubscriberLimit->is_limited = $limit->is_limited;
                        $packageSubscriberLimit->limit_count = $limit->limit_count;
                        $packageSubscriberLimit->provider_id = $provider;
                        $packageSubscriberLimit->subscription_package_id = $id;
                        $packageSubscriberLimit->save();
                    }
                }
            }else{
                $packageSubscriber                              = new PackageSubscriber();
                $packageSubscriber->subscription_package_id     = $id;
                $packageSubscriber->package_price               = $price;
                $packageSubscriber->package_name                = $name;
                $packageSubscriber->package_start_date          = null;
                $packageSubscriber->package_end_date            = null;
                $packageSubscriber->trial_duration              = 0;
                $packageSubscriber->provider_id                 = $provider;
                $packageSubscriber->vat_percentage              = 0;
                $packageSubscriber->vat_amount                  = 0;
                $packageSubscriber->payment_method              = $request['payment_method'];
                $packageSubscriber->payment_id                  = $request['payment_id'];
                $packageSubscriber->package_subscriber_log_id   = null;
                $packageSubscriber->save();
            }

            $subscribedServices = SubscribedService::where('provider_id', $provider)->get();

            foreach ($subscribedServices as $subscribedService) {
                $subscribedService->is_subscribed = 0;
                $subscribedService->save();
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            return false;

        } finally {

            if ($providerUser){
                $accessCheck = mobileAppCheck($providerUser->owner, 'mobile_app');

                if (!$accessCheck && $providerUser && $providerUser->owner && $providerUser->owner->tokens) {
                    $providerUser->owner->tokens->each(function ($token) {
                        $token->revoke();
                    });
                }
            }
        }
    }
}
