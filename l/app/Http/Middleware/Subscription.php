<?php

namespace App\Http\Middleware;

use Brian2694\Toastr\Facades\Toastr;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\BusinessSettingsModule\Entities\PackageSubscriber;
use Modules\BusinessSettingsModule\Entities\PackageSubscriberFeature;
use Modules\BusinessSettingsModule\Entities\SubscriptionPackage;
use Modules\ProviderManagement\Entities\Provider;
use Symfony\Component\HttpFoundation\Response;

class Subscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $module): Response
    {
        $user = Auth::user();
        if ($user) {
            $provider = Provider::where('user_id', $user->id)->first();
            if ($provider) {

                $providerId = $provider->id;
                $packageSubscriber = PackageSubscriber::where('provider_id', $providerId)->with('feature')->first();
                if ($packageSubscriber) {
                    $featureKeys = $packageSubscriber->feature->pluck('feature')->toArray();
                    if (in_array($module, $featureKeys) ) {
                        return $next($request);
                    } else {
                        Toastr::error(translate('your_package_does_not_include_this_section'));
                        return redirect()->route('provider.dashboard');
                    }
                }
            }
            return $next($request);
        }
        return $next($request);
    }
}
