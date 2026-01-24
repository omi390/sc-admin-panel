<?php

namespace Modules\BusinessSettingsModule\Http\Controllers\Api\V1\Provider;

use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use JetBrains\PhpStorm\NoReturn;
use Modules\BusinessSettingsModule\Entities\PackageSubscriber;
use Modules\BusinessSettingsModule\Entities\SubscriptionPackage;
use Modules\BusinessSettingsModule\Entities\SubscriptionPackageFeature;
use Modules\BusinessSettingsModule\Entities\SubscriptionPackageLimit;
use Modules\ProviderManagement\Entities\Provider;
use Modules\TransactionModule\Entities\Transaction;

class SubscriptionPackageController extends Controller
{
    private SubscriptionPackage $subscriptionPackage;
    private PackageSubscriber $packageSubscriber;
    private  Provider $provider;
    private  Transaction $transactions;

    public function __construct(SubscriptionPackage $subscriptionPackage, PackageSubscriber $packageSubscriber, Provider $provider, Transaction $transactions)
    {
        $this->subscriptionPackage = $subscriptionPackage;
        $this->packageSubscriber = $packageSubscriber;
        $this->provider = $provider;
        $this->transactions = $transactions;
    }

    /**
     * Display a listing of the resource.
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $subscriptionPackages = $this->subscriptionPackage->OfStatus(1)->with('subscriptionPackageFeature', 'subscriptionPackageLimit')->get();
        $formattedPackages = $subscriptionPackages->map(function($subscriptionPackage) {
            return subscriptionFeatureList($subscriptionPackage, PACKAGE_FEATURES);
        });

        return response()->json(response_formatter(DEFAULT_200, $formattedPackages), 200);
    }

    /**
     * Show the form for creating a new resource.
     * @return JsonResponse
     */
    public function subscriber()
    {
        $providerId = $this->provider->where('user_id', auth()->user()->id)->value('id');
        $packageSubscriber = $this->packageSubscriber->where('provider_id', $providerId)
            ->with('feature', 'limits', 'package')
            ->first();

        if ($packageSubscriber) {
            $formattedPackage = packageSubscriber($packageSubscriber, PACKAGE_FEATURES);
            return response()->json(response_formatter(DEFAULT_200, $formattedPackage), 200);
        }

        return response()->json(response_formatter(DEFAULT_200), 200);

    }

    /**
     * Show the form for creating a new resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function renew(Request $request): JsonResponse
    {
        $userId = auth('api')->user()->id;
        $providerId = $this->provider::where('user_id', $userId)->value('id');

        $package = $this->subscriptionPackage->where('id',$request->package_id)->ofStatus(1)->first();
        if (!$package){
            return response()->json(response_formatter(DEFAULT_400), 400);
        }

        $id                 = $package->id;
        $price              = $package->price;
        $name               = $package->name;
        $vatPercentage      = (int)((business_config('subscription_vat', 'subscription_Setting'))->live_values ?? 0);
        $vatAmount          = $package->price * ($vatPercentage / 100);
        $vatWithPrice       = $price + $vatAmount;

        $subscriber = $this->packageSubscriber->where('subscription_package_id', $id)->where('provider_id', $providerId)->first();
        if ($subscriber) {
            $paymentUrl = url('payment/subscription') . '?' .
                'provider_id=' . $providerId . '&' .
                'access_token=' . base64_encode($userId) . '&' .
                'amount=' . $vatWithPrice . '&' .
                'name=' . $name . '&' .
                'package_status=' . 'subscription_renew' . '&' .
                http_build_query($request->all());

            return response()->json(response_formatter(DEFAULT_200, $paymentUrl), 200);
        }

        return response()->json(response_formatter(DEFAULT_400), 400);
    }

    /**
     * Show the form for creating a new resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function shift(Request $request): JsonResponse
    {
        $userId = auth('api')->user()->id;
        $providerId = $this->provider::where('user_id', $userId)->value('id');

        $package = $this->subscriptionPackage->where('id',$request->package_id)->ofStatus(1)->first();
        if (!$package){
            return response()->json(response_formatter(DEFAULT_400), 400);
        }

        $id                 = $package->id;
        $price              = $package->price;
        $name               = $package->name;
        $vatPercentage      = (int)((business_config('subscription_vat', 'subscription_Setting'))->live_values ?? 0);
        $vatAmount          = $package->price * ($vatPercentage / 100);
        $vatWithPrice       = $price + $vatAmount;

        $paymentUrl = url('payment/subscription') . '?' .
            'provider_id=' . $providerId . '&' .
            'access_token=' . base64_encode($userId) . '&' .
            'amount=' . $vatWithPrice . '&' .
            'name=' . $name . '&' .
            'package_status=' . 'subscription_shift' . '&' .
            http_build_query($request->all());

        return response()->json(response_formatter(DEFAULT_200, $paymentUrl), 200);
    }

    /**
     * Show the form for creating a new resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function purchase(Request $request): JsonResponse
    {
        $userId = auth('api')->user()->id;
        $providerId = $this->provider::where('user_id', $userId)->value('id');

        $package = $this->subscriptionPackage->where('id',$request->package_id)->ofStatus(1)->first();
        if (!$package){
            return response()->json(response_formatter(DEFAULT_400), 400);
        }

        $price              = $package->price;
        $name               = $package->name;
        $vatPercentage      = (int)((business_config('subscription_vat', 'subscription_Setting'))->live_values ?? 0);
        $vatAmount          = $package->price * ($vatPercentage / 100);
        $vatWithPrice       = $price + $vatAmount;

        $paymentUrl = url('payment/subscription') . '?' .
            'provider_id=' . $providerId . '&' .
            'access_token=' . base64_encode($userId) . '&' .
            'amount=' . $vatWithPrice . '&' .
            'name=' . $name . '&' .
            'package_status=' . 'business_plan_change' . '&' .
            http_build_query($request->all());

        return response()->json(response_formatter(DEFAULT_200, $paymentUrl), 200);
    }

    /**
     * Show the form for creating a new resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function commission(Request $request): JsonResponse
    {
        $roundedPercentageUsed = 0;
        $totalDuration = 0;
        $userId = auth('api')->user()->id;
        $providerId = $this->provider::where('user_id', $userId)->value('id');

        $subscriber = $this->packageSubscriber->where('provider_id',$providerId)->with('logs')->first();
        $usedTime   = (int)((business_config('usage_time', 'subscription_Setting'))?->live_values ?? 0);

        if (!$subscriber){
            return response()->json(response_formatter(ALREADY_COMMISSION_BASE), 400);
        }

        $packageStartDate = Carbon::parse($subscriber->package_start_date);
        $packageEndDate = Carbon::parse($subscriber->package_end_date);
        $now = Carbon::now();

        if ($subscriber?->payment?->is_paid) {
            $totalDuration = $packageStartDate->diffInDays($packageEndDate);
            $daysPassed = $packageStartDate->diffInDays($now);
            $percentageUsed = ($daysPassed / $totalDuration) * 100;
            $roundedPercentageUsed = ceil($percentageUsed);
        }

        if ($usedTime > $roundedPercentageUsed && $totalDuration != 0){
            shiftRefundSubscriptionTransaction(
                provider_id: $providerId
            );
        }
        $subscriber->delete();

        return response()->json(response_formatter(SHIFT_SUBSCRIPTION_PACKAGE), 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function cancel(Request $request): JsonResponse
    {
        $packageId = $request->package_id;
        $userId = auth('api')->user()->id;
        $providerId = $this->provider::where('user_id', $userId)->value('id');
        $package = $this->packageSubscriber->where('subscription_package_id', $packageId)->where('provider_id', $providerId)->first();
        if ($package){
            $package->is_canceled = 1;
            $package->save();

            return response()->json(response_formatter(DEFAULT_200), 200);
        }
        return response()->json(response_formatter(DEFAULT_400), 400);

    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function transactions(Request $request): JsonResponse
    {
        $search = $request->has('search') ? $request->get('search') : '';
        $startDate = $request->has('start_date') ? $request->get('start_date') : null;
        $endDate = $request->has('end_date') ? $request->get('end_date') : null;

        $queryParams = ['search' => $search];
        $providerUserId = auth('api')->user()->id;
        $providerId = $this->provider::where('user_id', $providerUserId)->value('id');
        if (!$providerId) {
            return response()->json(response_formatter(DEFAULT_404), 404);
        }

        $transactions = $this->transactions
            ->where(function ($q) use ($providerUserId) {
                $q->where('from_user_id', $providerUserId)
                    ->orWhere('to_user_id', $providerUserId);
            })
            ->when($search, function ($query) use ($search) {
                $keys = explode(' ', $search);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('id', 'LIKE', '%' . $key . '%')
                            ->orWhereHas('packageLog.payment', function ($query) use ($key) {
                                $query->where('transaction_id', 'LIKE', '%' . $key . '%');
                            });
                    }
                });
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $startDate = Carbon::parse($startDate)->startOfDay();
                $endDate = Carbon::parse($endDate)->endOfDay();
                return $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->with('packageLog.payment', 'packageLog.provider')
            ->whereIn('trx_type', ['subscription_purchase', 'subscription_renew', 'subscription_shift', 'subscription_refund'])
            ->latest()
            ->paginate($request['limit'], ['*'], 'offset', $request['offset'])
            ->withPath('')
            ->appends($queryParams);

        return response()->json(response_formatter(DEFAULT_200, $transactions), 200);
    }

}
