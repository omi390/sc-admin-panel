<?php

namespace Modules\BusinessSettingsModule\Http\Controllers\Web\Provider;

use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Modules\BookingModule\Entities\SubscriptionSubscriberBooking;
use Modules\BusinessSettingsModule\Emails\CancelSubscriptionMail;
use Modules\BusinessSettingsModule\Emails\ShiftSubscriptionMail;
use Modules\BusinessSettingsModule\Entities\PackageSubscriber;
use Modules\BusinessSettingsModule\Entities\PackageSubscriberLimit;
use Modules\BusinessSettingsModule\Entities\SubscriptionPackage;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ProviderManagement\Entities\SubscribedService;
use Modules\TransactionModule\Entities\Transaction;
use OpenSpout\Common\Exception\InvalidArgumentException;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Common\Exception\UnsupportedTypeException;
use OpenSpout\Writer\Exception\WriterNotOpenedException;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubscriptionPackageController extends Controller
{
    private SubscriptionPackage $subscriptionPackage;
    private PackageSubscriber $packageSubscriber;
    private Provider $provider;
    private Transaction $transactions;
    private SubscriptionSubscriberBooking $subscriptionSubscriberBooking;
    private SubscribedService $subscribedService;


    public function __construct(PackageSubscriber $packageSubscriber, Provider $provider, SubscriptionPackage $subscriptionPackage, Transaction $transactions, SubscriptionSubscriberBooking $subscriptionSubscriberBooking, SubscribedService $subscribedService)
    {
        $this->packageSubscriber = $packageSubscriber;
        $this->provider = $provider;
        $this->subscriptionPackage = $subscriptionPackage;
        $this->transactions = $transactions;
        $this->subscriptionSubscriberBooking = $subscriptionSubscriberBooking;
        $this->subscribedService = $subscribedService;
    }

    /**
     * Show the specified resource.
     * @return Renderable
     */
    public function details(): Renderable
    {
        $provider = $this->provider->where('user_id', auth()->user()->id)->first();
        $providerId = $provider->id;
        $commission = $provider->commission_status == 1 ? $provider->commission_percentage : (business_config('default_commission', 'business_information'))->live_values;
        $subscriptionDetails = $this->packageSubscriber->where('provider_id', $providerId)->first();

        if ($subscriptionDetails){
            $subscriptionPrice = $this->subscriptionPackage->where('id', $subscriptionDetails?->subscription_package_id)->value('price');
            $vatPercentage      = (int)((business_config('subscription_vat', 'subscription_Setting'))->live_values ?? 0);

            $start = Carbon::parse($subscriptionDetails?->package_start_date)->subDay() ?? '';
            $end = Carbon::parse($subscriptionDetails?->package_end_date) ?? '';
            $startDate = $subscriptionDetails?->package_start_date;
            $endDate = $subscriptionDetails?->package_end_date;
            $monthsDifference = $start->diffInDays($end) ?? '';

            $bookingCheck = $subscriptionDetails?->limits->where('provider_id', $providerId)->where('key', 'booking')->first();
            $categoryCheck = $subscriptionDetails?->limits->where('provider_id', $providerId)->where('key', 'category')->first();
            $isBookingLimit = $bookingCheck?->is_limited;
            $isCategoryLimit = $categoryCheck?->is_limited;

            $totalBill = $subscriptionDetails?->logs->where('provider_id', $providerId)->sum('package_price') ?? 0.00;
            $totalPurchase = $subscriptionDetails?->logs->where('provider_id', $providerId)->count() ?? 0;
            $calculationVat = $subscriptionPrice * ($vatPercentage / 100);
            $renewalPrice = $subscriptionPrice + $calculationVat;


            $bookingCount = $this->subscriptionSubscriberBooking->where('provider_id', $providerId)
                ->where('package_subscriber_log_id', $subscriptionDetails?->package_subscriber_log_id)
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                    $startDate = Carbon::parse($startDate)->startOfDay();
                    $endDate = Carbon::parse($endDate)->endOfDay();
                    return $query->whereBetween('updated_at', [$startDate, $endDate]);
                })
                ->count();

            $categoryCount = $this->subscribedService->where('provider_id', $providerId)->where('is_subscribed', 1)
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                    $startDate = Carbon::parse($startDate)->startOfDay();
                    $endDate = Carbon::parse($endDate)->endOfDay();
                    return $query->whereBetween('updated_at', [$startDate, $endDate]);
                })
                ->count();

            $limitLeft = [
                'booking' => 0,
                'category' => 0
            ];


            foreach ($subscriptionDetails->limits->where('provider_id', $providerId) as $limit) {
                if ($limit->key === 'booking' && $limit->is_limited) {
                    $limitLeft['booking'] = $limit->limit_count - $bookingCount;
                }

                if ($limit->key === 'category' && $limit->is_limited) {
                    $limitLeft['category'] = $limit->limit_count - $categoryCount;
                }
            }

            $subscriptionDetails['feature_limit_left'] = $limitLeft;

            return view('businesssettingsmodule::provider.subscription-package.details', compact('subscriptionDetails', 'monthsDifference', 'bookingCheck', 'categoryCheck', 'isBookingLimit', 'isCategoryLimit', 'totalBill', 'totalPurchase', 'renewalPrice'));
        }

        return view('businesssettingsmodule::provider.subscription-package.details', compact('subscriptionDetails','commission'));


    }

    public function ajaxRenewPackage(Request $request)
    {
        $packageId = $request->id;
        $providerId = $this->provider->where('user_id',auth()->user()->id)->value('id');
        $subscriptionPackage = $this->subscriptionPackage->where('id', $packageId)->first();
        if ($subscriptionPackage){

            $isPublished = 0;
            try {
                $fullData = include('Modules/Gateways/Addon/info.php');
                $isPublished = $fullData['is_published'] == 1 ? 1 : 0;
            } catch (\Exception $exception) {
            }

            $paymentGateways = collect($this->getPaymentMethods())
                ->filter(function ($query) use ($isPublished) {
                    if (!$isPublished) {
                        return in_array($query['gateway'], array_column(PAYMENT_METHODS, 'key'));
                    } else return $query;
                })->map(function ($query) {
                    $query['label'] = ucwords(str_replace('_', ' ', $query['gateway']));
                    return $query;
                })->values();

            $html = view('providermanagement::layouts.partials.renew-content', compact('subscriptionPackage', 'paymentGateways', 'providerId'))->render();

            return response()->json($html);

        }
    }

    public function ajaxPurchasePackage(Request $request)
    {
        $packageId = $request->id;
        $providerId = $this->provider->where('user_id',auth()->user()->id)->value('id');
        $subscriptionPackage = $this->subscriptionPackage->where('id', $packageId)->first();
        if ($subscriptionPackage){

            $isPublished = 0;
            try {
                $fullData = include('Modules/Gateways/Addon/info.php');
                $isPublished = $fullData['is_published'] == 1 ? 1 : 0;
            } catch (\Exception $exception) {
            }

            $paymentGateways = collect($this->getPaymentMethods())
                ->filter(function ($query) use ($isPublished) {
                    if (!$isPublished) {
                        return in_array($query['gateway'], array_column(PAYMENT_METHODS, 'key'));
                    } else return $query;
                })->map(function ($query) {
                    $query['label'] = ucwords(str_replace('_', ' ', $query['gateway']));
                    return $query;
                })->values();

            $html = view('providermanagement::layouts.partials.purchase-content', compact('subscriptionPackage', 'paymentGateways', 'providerId'))->render();

            return response()->json($html);

        }
    }
    public function ajaxShiftPackage(Request $request)
    {
        $id = $request->id;
        $providerId = $this->provider->where('user_id',auth()->user()->id)->value('id');
        $subscriptionPackage = $this->subscriptionPackage->where('id', $id)->first();
        $packageSubscriber = $this->packageSubscriber->where('provider_id',$providerId)->first();
        if ($subscriptionPackage){

            $isPublished = 0;
            try {
                $fullData = include('Modules/Gateways/Addon/info.php');
                $isPublished = $fullData['is_published'] == 1 ? 1 : 0;
            } catch (\Exception $exception) {
            }

            $paymentGateways = collect($this->getPaymentMethods())
                ->filter(function ($query) use ($isPublished) {
                    if (!$isPublished) {
                        return in_array($query['gateway'], array_column(PAYMENT_METHODS, 'key'));
                    } else return $query;
                })->map(function ($query) {
                    $query['label'] = ucwords(str_replace('_', ' ', $query['gateway']));
                    return $query;
                })->values();


            $html = view('providermanagement::layouts.partials.shift-content', compact('subscriptionPackage', 'paymentGateways', 'providerId', 'packageSubscriber'))->render();

            return response()->json($html);

        }
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function renewPayment( Request $request): RedirectResponse
    {
        $userId = auth()->user()->id;
        $providerId = $this->provider::where('user_id', $userId)->value('id');

        $package = $this->subscriptionPackage->where('id',$request->package_id)->ofStatus(1)->first();
        if (!$package){
            Toastr::error(translate('Please Select valid plan'));
            return back();
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
                'package_id=' . $id . '&' .
                'amount=' . $vatWithPrice . '&' .
                'name=' . $name . '&' .
                'payment_platform=' . 'web' . '&' .
                'package_status=' . 'subscription_renew' . '&' .
                http_build_query($request->all());

            return redirect($paymentUrl);
        }
        return back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function shiftPayment( Request $request): RedirectResponse
    {
        $userId = auth()->user()->id;
        $providerId = $this->provider::where('user_id', $userId)->value('id');

        $package = $this->subscriptionPackage->where('id',$request->package_id)->ofStatus(1)->first();
        if (!$package){
            Toastr::error(translate('Please Select valid plan'));
            return back();
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
            'package_id=' . $id . '&' .
            'amount=' . $vatWithPrice . '&' .
            'name=' . $name . '&' .
            'payment_platform=' . 'web' . '&' .
            'package_status=' . 'subscription_shift' . '&' .
            http_build_query($request->all());

        return redirect($paymentUrl);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function purchasePayment( Request $request): RedirectResponse
    {
        $userId = auth()->user()->id;
        $providerId = $this->provider::where('user_id', $userId)->value('id');

        $package = $this->subscriptionPackage->where('id',$request->package_id)->ofStatus(1)->first();
        if (!$package){
            Toastr::error(translate('Please Select valid plan'));
            return back();
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
                'package_id=' . $id . '&' .
                'amount=' . $vatWithPrice . '&' .
                'name=' . $name . '&' .
                'payment_platform=' . 'web' . '&' .
                'package_status=' . 'business_plan_change' . '&' .
                http_build_query($request->all());

            return redirect($paymentUrl);
    }
    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function toCommission( Request $request): RedirectResponse
    {
        $userId = auth()->user()->id;
        $providerId = $this->provider::where('user_id', $userId)->value('id');
        $subscriber = $this->packageSubscriber->where('provider_id',$providerId)->with('logs')->first();
        $usedTime   = (int)((business_config('usage_time', 'subscription_Setting'))->live_values ?? 0);

        if (!$subscriber){
            Toastr::error(translate('Something wrong'));
            return back();
        }

        $packageStartDate = Carbon::parse($subscriber->package_start_date)->subDay();
        $packageEndDate = Carbon::parse($subscriber->package_end_date);
        $now = Carbon::now();

        if ($now->lessThanOrEqualTo($packageEndDate)) {
            $totalDuration = $packageStartDate->diffInDays($packageEndDate);
            $daysPassed = $packageStartDate->diffInDays($now);
            $percentageUsed = ($daysPassed / $totalDuration) * 100;
            $roundedPercentageUsed = ceil($percentageUsed);

            if ($usedTime > $roundedPercentageUsed) {
                shiftRefundSubscriptionTransaction(
                    provider_id: $providerId
                );
            }
        }

        PackageSubscriberLimit::where('provider_id', $providerId)->delete();
        $subscriber->delete();

        Toastr::success(translate('Subscription change successfully'));
        return back();

    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function cancel( Request $request): RedirectResponse
    {
        $packageId = $request->package_id;
        $userId = auth()->user()->id;
        $providerId = $this->provider::where('user_id', $userId)->value('id');

        $emailPermission = isNotificationActive($providerId, 'subscription', 'email', 'provider');
        $packageSubscriber = $this->packageSubscriber->where('subscription_package_id', $packageId)->where('provider_id', $providerId)->first();
        if ($packageSubscriber){
            try {
                $packageSubscriber->is_canceled = 1;
                $packageSubscriber->save();
                if ($emailPermission) {
                    Mail::to($packageSubscriber?->provider?->owner?->email)->send(new CancelSubscriptionMail($packageSubscriber->provider));
                }

            } catch (\Exception $exception) {
                info($exception);
            }

            Toastr::success(translate('Subscription canceled successfully'));
            return back();
        }

        Toastr::error(translate('Please Select valid plan'));
        return back();

    }

    public function transactions(Request $request)
    {
        $search = $request->input('search', '');
        $transactionType = $request->input('transaction_type', 'all');
        $dateRange = $request->has('date_range') ? $request->get('date_range') : 'all';
        $queryParams = ['search' => $search, 'date_range' =>$dateRange, 'transaction_type' => $transactionType];

        if ($request->input('date_range') === 'custom_date') {
            $queryParams['from'] = $request->input('from');
            $queryParams['to'] = $request->input('to');
        }

        $providerUserId = auth()->user()->id;

        if (!$providerUserId) {
            Toastr::error(translate('Provider not found'));
            return back();
        }

        $transactions = $this->transactions
            ->filterDateRange($request->input('date_range'), $request->input('from'), $request->input('to'))
            ->where(function ($q) use ($providerUserId) {
                $q->where('from_user_id', $providerUserId)
                    ->orWhere('to_user_id', $providerUserId);
            })
            ->when($search, function ($query) use ($search) {
                $keys = explode(' ', $search);
                $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->where('id', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->when($request->has('date_range'), function ($query) use ($request) {
                if ($request['date_range'] == 'this_week') {
                    $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                } elseif ($request['date_range'] == 'this_month') {
                    $query->whereMonth('created_at', Carbon::now()->month);
                } elseif ($request['date_range'] == 'this_year') {
                    $query->whereYear('created_at', Carbon::now()->year);
                }
            })
            ->with(['packageLog.payment', 'packageLog.provider'])
            ->whereIn('trx_type', ['subscription_purchase', 'subscription_renew', 'subscription_shift', 'subscription_refund'])
            ->latest()
            ->paginate(pagination_limit())
            ->appends($queryParams);

        return view('businesssettingsmodule::provider.subscription-package.transaction', compact('transactions', 'queryParams', 'search'));
    }

    public function download(Request $request): string|StreamedResponse
    {
        $userId = auth()->user()->id;
        $providerId = $this->provider::where('user_id', $userId)->value('id');
        $search = $request->has('search') ? $request->get('search') : '';
        $items = $this->transactions
            ->when($search, function ($query) use ($search) {
                $keys = explode(' ', $search);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('id', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->whereIn('trx_type', ['subscription_purchase', 'subscription_renew', 'subscription_shift', 'subscription_refund'])
            ->where('from_user_id', $providerId)
            ->latest()
            ->get();

        return (new FastExcel($items))->download(time() . '-file.xlsx');
    }

    /**
     * @param Request $request
     * @return string|StreamedResponse
     * @throws AuthorizationException
     * @throws IOException
     * @throws InvalidArgumentException
     * @throws UnsupportedTypeException
     * @throws WriterNotOpenedException
     */
    public function transactionsDownload(Request $request): string|StreamedResponse
    {
        $providerUserId = auth()->user()->id ?? $request->provider_user_id;
        $search = $request->has('search') ? $request->get('search') : '';
        $items = $this->transactions
            ->when($search, function ($query) use ($search) {
                $keys = explode(' ', $search);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('id', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->where(function ($q) use ($providerUserId) {
                $q->where('from_user_id', $providerUserId)
                    ->orWhere('to_user_id', $providerUserId);
            })
            ->whereIn('trx_type', ['subscription_purchase', 'subscription_renew', 'subscription_shift', 'subscription_refund'])
            ->latest()
            ->get();

        return (new FastExcel($items))->download(time() . '-file.xlsx');
    }
    public function invoice($id)
    {
        $transaction = $this->transactions->with('packageLog.payment', 'packageLog.provider')->find($id);

        return view('businesssettingsmodule::provider.subscription-package.invoice', compact('transaction'));
    }

    private function getPaymentMethods(): array
    {
        if (!Schema::hasTable('addon_settings')) {
            return [];
        }

        $methods = DB::table('addon_settings')->where('settings_type', 'payment_config')->get();
        $env = env('APP_ENV') == 'live' ? 'live' : 'test';
        $credentials = $env . '_values';

        $data = [];
        foreach ($methods as $method) {
            $gateway_image = getPaymentGatewayImageFullPath(key: $method->key_name, settingsType: $method->settings_type, defaultPath: 'public/assets/admin-module/img/placeholder.png');
            $credentialsData = json_decode($method->$credentials);
            $additional_data = json_decode($method->additional_data);
            if ($credentialsData->status == 1) {
                $data[] = [
                    'gateway' => $method->key_name,
                    'gateway_image' => $gateway_image
                ];
            }
        }
        return $data;
    }
}
