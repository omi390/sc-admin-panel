<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Modules\BusinessSettingsModule\Entities\PackageSubscriber;
use Modules\BusinessSettingsModule\Entities\SubscriptionPackage;
use Modules\ProviderManagement\Entities\Provider;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionModalMiddleware
{

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('provider/*')) {
            $user = Auth::user();
            if ($user) {
                $provider = Provider::where('user_id', $user->id)->first();
                if ($provider) {
                    $providerId = $provider->id;
                    $commissionStatus = (int)((business_config('provider_commision', 'provider_config'))?->live_values);
                    $subscriptionStatus = (int)((business_config('provider_subscription', 'provider_config'))?->live_values);
                    $commission = $provider->commission_status == 1 ? $provider->commission_percentage : business_config('default_commission', 'business_information')->live_values;

                    $subscriptionPackages = SubscriptionPackage::with('subscriptionPackageFeature', 'subscriptionPackageLimit')
                        ->OfStatus(1)->get();

                    $formattedPackages = $subscriptionPackages->map(function ($subscriptionPackage) {
                        return formatSubscriptionPackage($subscriptionPackage, PACKAGE_FEATURES);
                    });

                    $packageSubscriber = PackageSubscriber::where('provider_id', $providerId)->first();
                    $trialDuration = optional($packageSubscriber)->trial_duration;
                    $canceled = optional($packageSubscriber)->is_canceled;
                    $endDate = optional($packageSubscriber)->package_end_date;
                    $packageEndDate = $endDate ? Carbon::parse($endDate)->endOfDay() : null;
                    $currentDate = Carbon::now()->startOfDay();
                    $sameDate = $currentDate->equalTo($packageEndDate) || $currentDate->lessThanOrEqualTo($packageEndDate);
                    $isPackageEnded = $packageEndDate ? $currentDate->diffInDays($packageEndDate, false) : null;
                    $deadlineWarning = (int)((business_config('deadline_warning', 'subscription_Setting'))->live_values ?? 0);
                    $deadlineWarningMessage = ((business_config('deadline_warning_message', 'subscription_Setting'))->live_values ?? '');
                    $digitalPayment = (int)((business_config('digital_payment', 'service_setup'))->live_values ?? null);
                    $modalClosed = session('modalClosed', false);

                    View::share(compact(
                        'provider', 'providerId', 'commission', 'formattedPackages',
                        'trialDuration', 'canceled', 'packageEndDate', 'isPackageEnded', 'modalClosed', 'endDate',
                        'packageSubscriber', 'subscriptionPackages', 'deadlineWarning', 'deadlineWarningMessage',
                        'commissionStatus', 'subscriptionStatus', 'digitalPayment', 'sameDate'
                    ));

                    if ($packageSubscriber && $packageSubscriber->trial_duration == 0 && !$packageSubscriber?->payment?->is_paid){
                        if (!$request->is('provider/dashboard', 'provider/booking*', 'provider/subscription-package*','payment/*', 'provider/auth/logout', 'provider/profile-update*')) {
                            session()->flash('paySubscriptionModal', true);
                            return redirect()->route('provider.subscription-package.details')->with('warning', 'Please pay to continue.');
                        }
                    }

                    if ($isPackageEnded < 0 && !$request->is(
                            'provider/dashboard', 'provider/auth/logout', 'provider/service/available', 'provider/sub-category/subscribed', 'provider/profile-update*',
                            'provider/account-info*', 'provider/withdraw*', 'provider/business-settings*', 'provider/*bank-info*',
                            'provider/booking*', 'provider/serviceman*' , 'provider/subscription-package*','payment/subscription*', 'payment/*')) {
                        session()->flash('showSubscriptionModal', true);
                        return redirect()->route('provider.dashboard')->with('warning', 'Your subscription has ended. Please renew to continue.');
                    }
                }
            }
        }

        return $next($request);
    }
}
