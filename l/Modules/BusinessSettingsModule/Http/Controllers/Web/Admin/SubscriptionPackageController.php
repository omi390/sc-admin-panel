<?php

namespace Modules\BusinessSettingsModule\Http\Controllers\Web\Admin;

use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\BusinessSettingsModule\Emails\CommissionToSubscriptionMail;
use Modules\BusinessSettingsModule\Emails\SubscriptionToCommissionMail;
use Modules\BusinessSettingsModule\Emails\SubscriptionUpdateMail;
use Modules\BusinessSettingsModule\Entities\BusinessSettings;
use Modules\BusinessSettingsModule\Entities\PackageSubscriber;
use Modules\BusinessSettingsModule\Entities\PackageSubscriberLog;
use Modules\BusinessSettingsModule\Entities\SubscriptionPackage;
use Modules\BusinessSettingsModule\Entities\SubscriptionPackageFeature;
use Modules\BusinessSettingsModule\Entities\SubscriptionPackageLimit;
use Modules\PaymentModule\Entities\PaymentRequest;
use Modules\PaymentModule\Traits\SubscriptionTrait;
use Modules\ProviderManagement\Emails\NewJoiningRequestMail;
use Modules\ProviderManagement\Entities\Provider;
use Modules\TransactionModule\Entities\Transaction;
use Modules\UserManagement\Entities\User;
use OpenSpout\Common\Exception\InvalidArgumentException;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Common\Exception\UnsupportedTypeException;
use OpenSpout\Writer\Exception\WriterNotOpenedException;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubscriptionPackageController extends Controller
{
    private SubscriptionPackage $subscriptionPackage;
    private SubscriptionPackageFeature $subscriptionPackageFeature;
    private SubscriptionPackageLimit $subscriptionPackageLimit;
    private  PackageSubscriber $packageSubscriber;
    private  PackageSubscriberLog $packageSubscriberLog;

    private BusinessSettings $businessSetting;
    use AuthorizesRequests;

    use SubscriptionTrait;
    private  Transaction $transactions;
    private  PaymentRequest $paymentRequest;
    private  BusinessSettings $businessSettings;
    private  Provider $provider;

    public function __construct(
        SubscriptionPackage $subscriptionPackage,
        SubscriptionPackageFeature $subscriptionPackageFeature,
        SubscriptionPackageLimit $subscriptionPackageLimit,
        BusinessSettings $businessSetting,
        Transaction $transactions,
        PackageSubscriber $packageSubscriber,
        PaymentRequest $paymentRequest,
        BusinessSettings $businessSettings,
        Provider $provider,
        PackageSubscriberLog $packageSubscriberLog
    )
    {
        $this->subscriptionPackage = $subscriptionPackage;
        $this->subscriptionPackageFeature = $subscriptionPackageFeature;
        $this->subscriptionPackageLimit = $subscriptionPackageLimit;
        $this->businessSetting = $businessSetting;
        $this->transactions = $transactions;
        $this->packageSubscriber = $packageSubscriber;
        $this->paymentRequest = $paymentRequest;
        $this->businessSettings = $businessSettings;
        $this->provider = $provider;
        $this->packageSubscriberLog = $packageSubscriberLog;
    }

    public function index(Request $request)
    {
        $this->authorize('subscription_package_view');

        $search = $request->has('search') ? $request['search'] : '';
        $dataRange = $request->has('date_range') ? $request['date_range'] : '';
        $queryParams = ['search' => $search, 'date_range' => $dataRange];

        $subscriptionPackage = $this->subscriptionPackage
            ->with(['subscriber' => function ($query) {
                $query->whereHas('provider', function ($query) {
                    $query->whereNull('deleted_at');
                });
            }])
            ->withCount(['subscriber' => function ($query) {
                $query->where('package_end_date', '>', Carbon::now()->subDay())
                    ->whereHas('provider', function ($query) {
                        $query->whereNull('deleted_at');
                    });
            }])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('name', 'LIKE', '%' . $key . '%')
                            ->orWhere('price', 'LIKE', '%' . $key . '%')
                            ->orWhere('duration', 'LIKE', '%' . $key . '%')
                            ->orWhere('description', 'LIKE', '%' . $key . '%');
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
            ->latest()->paginate(pagination_limit())->appends($queryParams);

        $subscriptionPackageCount = $this->subscriptionPackage->count();

        return view('businesssettingsmodule::admin.subscription-package.list', compact('subscriptionPackage','search', 'subscriptionPackageCount', 'queryParams'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create(): Renderable
    {
        $this->authorize('subscription_package_add');

        return view('businesssettingsmodule::admin.subscription-package.create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('subscription_package_add');

        $request->validate([
            'name' => 'required|unique:subscription_packages|max:100',
            'price' => 'required|numeric|gte:0.01|lte:999999999999',
            'duration' => 'required|integer|gte:1|lte:999999999',
            'description' => 'required|max:255',
            'feature' => 'required',
            'limit_count' => 'required_if:is_limited,0',
        ],
        [
            'name.required' => translate('Package name is required'),
            'price.required' => translate('Package price is required'),
            'duration.required' => translate('Package validity days is required'),
            'description.required' => translate('Package info is required'),
            'feature.required' => translate('Please select at latest one package feature'),
            'limit_count.required_if' => 'The maximum use limit field is required when select is use limit',
        ]);

        $subscriptionPackage = $this->subscriptionPackage;
        $subscriptionPackage->name = $request->name;
        $subscriptionPackage->price = $request->price;
        $subscriptionPackage->duration = $request->duration;
        $subscriptionPackage->description = $request->description;
        $subscriptionPackage->save();

        if ($request->feature){
            foreach ($request->feature as $key => $feature){
                $subscriptionPackageFeature = new SubscriptionPackageFeature();
                $subscriptionPackageFeature->subscription_package_id = $subscriptionPackage->id;
                $subscriptionPackageFeature->feature = $key;
                $subscriptionPackageFeature->save();
            }
        }

        if ($request->request_limit){
            foreach ($request->request_limit as $key => $limit){
                $subscriptionPackageLimit = new SubscriptionPackageLimit();
                $subscriptionPackageLimit->subscription_package_id = $subscriptionPackage->id;
                $subscriptionPackageLimit->key = $key;
                $subscriptionPackageLimit->is_limited = isset($limit['limit_type']) ? ($limit['limit_type'] == 'limited' ? 1 : 0) : 0;
                $subscriptionPackageLimit->limit_count = $limit['limit_count'] ?? 0;
                $subscriptionPackageLimit->save();
            }
        }

        Toastr::success(translate(CATEGORY_STORE_200['message']));
        return redirect('/admin/subscription/package/list');
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function details(Request $request, $id): Renderable
    {
        $this->authorize('subscription_package_view');

        $dateRange = $request->input('date_range', '');
        $queryParams = ['date_range' => $dateRange];

        $deadlineWarning = (int) (business_config('deadline_warning', 'subscription_Setting'))?->live_values ?? 0;
        $deadlineDate = \Carbon\Carbon::now()->addDays($deadlineWarning);

        $packageSubscribers = $this->packageSubscriber
            ->where('subscription_package_id', $id)
            ->whereHas('provider', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->when($dateRange, function ($query) use ($dateRange) {
                if ($dateRange == 'this_week') {
                    $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                } elseif ($dateRange == 'this_month') {
                    $query->whereMonth('created_at', Carbon::now()->month);
                } elseif ($dateRange == 'this_year') {
                    $query->whereYear('created_at', Carbon::now()->year);
                }
            })
            ->get();

        $warningSubscribersCount = $packageSubscribers->filter(function ($subscriber) use ($deadlineDate) {
            return \Carbon\Carbon::parse($subscriber->package_end_date)->lessThanOrEqualTo($deadlineDate);
        })->count();

        $freeTrialCount = $this->packageSubscriber
            ->where('subscription_package_id', $id)
            ->where('trial_duration', '>', 0)
            ->where('package_end_date', '>', now())
            ->count();

        $subscriberLogs = $this->packageSubscriberLog
            ->where('subscription_package_id', $id)->get();

        $transactionIds = $subscriberLogs->pluck('primary_transaction_id')->toArray();

        $totalRenewPrice = DB::table('transactions')
            ->whereIn('id', $transactionIds)
            ->where('trx_type', 'subscription_renew')
            ->sum('credit');

        $totalEarning = $this->packageSubscriberLog
            ->where('subscription_package_id', $id)
            ->sum(DB::raw('package_price'));

        $subscriptionPackage = $this->subscriptionPackage->find($id);
        $subscriptions = $this->subscriptionPackage->ofStatus(1)->get();

        return view('businesssettingsmodule::admin.subscription-package.details', compact(
            'subscriptionPackage',
            'subscriptions',
            'warningSubscribersCount',
            'packageSubscribers',
            'deadlineWarning',
            'queryParams',
            'freeTrialCount',
            'totalEarning',
            'totalRenewPrice'
        ));
    }


    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $this->authorize('subscription_package_update');

        $subscriptionPackage = $this->subscriptionPackage->where('id',$id)->first();
        return view('businesssettingsmodule::admin.subscription-package.edit', compact('subscriptionPackage'));
    }


    /**
     * @param Request $request
     * @param $id
     * @return Application|\Illuminate\Foundation\Application|RedirectResponse|Redirector
     * @throws AuthorizationException
     */
    public function update(Request $request, $id): \Illuminate\Foundation\Application|Redirector|Application|RedirectResponse
    {
        $this->authorize('subscription_package_update');

        $request->validate([
            'name'          => 'required|max:100|unique:subscription_packages,name,'.$id,
            'price'         => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/|gte:1|lte:99999999999',
            'duration'      => 'required|integer|gte:1|lte:999999999',
            'description'   => 'required|max:255',
            'feature'       => 'required',
            'limit_count'   => 'required_if:is_limited,0',
        ],
            [
                'name.required'             => translate('Package name is required'),
                'price.required'            => translate('Package price is required'),
                'duration.required'         => translate('Package validity days is required'),
                'description.required'      => translate('Package info is required'),
                'feature.required'          => translate('Please select at latest one package feature'),
                'limit_count.required_if'   => 'The maximum use limit field is required when select is use limit',
            ]);

        $subscriptionPackage = SubscriptionPackage::findOrFail($id);

        $originalSubscriptionPackage = $subscriptionPackage->getOriginal();
        $originalFeatures = $subscriptionPackage->subscriptionPackageFeature()->pluck('feature')->toArray();
        $originalLimits = $subscriptionPackage->subscriptionPackageLimit->mapWithKeys(function ($item) {
            return [$item->key => $item->only('is_limited', 'limit_count')];
        })->toArray();

        $subscriptionPackage->name = $request->name;
        $subscriptionPackage->price = $request->price;
        $subscriptionPackage->duration = $request->duration;
        $subscriptionPackage->description = $request->description;
        $subscriptionPackage->save();

        $subscriptionPackage->subscriptionPackageFeature()->delete();
        $newFeatures = [];
        if ($request->has('feature')) {
            foreach ($request->feature as $key => $feature) {
                $subscriptionPackageFeature = new SubscriptionPackageFeature();
                $subscriptionPackageFeature->subscription_package_id = $subscriptionPackage->id;
                $subscriptionPackageFeature->feature = $key;
                $subscriptionPackageFeature->save();
                $newFeatures[] = $key;
            }
        }

        $limits = $request->get('request_limit', []);
        $newLimits = [];
        foreach (['booking', 'category'] as $limitKey) {
            $limit = $subscriptionPackage->subscriptionPackageLimit->where('key', $limitKey)->first();
            if (!$limit) {
                $limit = new SubscriptionPackageLimit();
                $limit->subscription_package_id = $subscriptionPackage->id;
                $limit->key = $limitKey;
            }

            if (isset($limits[$limitKey]['is_limited'])) {
                $limit->is_limited = 1;
                $limit->limit_count = $limits[$limitKey]['limit_count'] ?? 0;
            } else {
                $limit->is_limited = 0;
                $limit->limit_count = 0;
            }

            $limit->save();
            $newLimits[$limitKey] = $limit->only('is_limited', 'limit_count');
        }

        $changes = $this->isChanged(originalSubscriptionPackage: $originalSubscriptionPackage ,subscriptionPackage: $subscriptionPackage, originalFeatures: $originalFeatures, newFeatures: $newFeatures, originalLimits: $originalLimits, newLimits: $newLimits);

        if (!empty($changes)) {
            $subscribers = PackageSubscriber::with('provider')->where(['subscription_package_id' => $subscriptionPackage->id])->get();
            foreach ($subscribers as $subscriber) {
                $provider = $subscriber->provider;
                $email = optional($provider)->company_email;
                if ($provider && $email) {
                    try {
                        Mail::to($email)->send(new SubscriptionUpdateMail($provider));
                    } catch (\Exception $exception) {
                        info($exception);
                    }
                }
            }
        }

        Toastr::success(translate(DEFAULT_UPDATE_200['message']));
        return redirect('/admin/subscription/package/list');
    }

    /**
     * @param $originalSubscriptionPackage
     * @param $subscriptionPackage
     * @param $originalFeatures
     * @param $newFeatures
     * @param $originalLimits
     * @param $newLimits
     * @return array
     */
    private function isChanged($originalSubscriptionPackage, $subscriptionPackage, $originalFeatures, $newFeatures, $originalLimits, $newLimits): array
    {
        $changes = [];

        $dirtyAttributes = array_diff_assoc($subscriptionPackage->getAttributes(), $originalSubscriptionPackage);
        unset($dirtyAttributes['updated_at']);
        if (!empty($dirtyAttributes)) {
            $changes['package'] = $dirtyAttributes;
        }

        if (array_diff($originalFeatures, $newFeatures) || array_diff($newFeatures, $originalFeatures)) {
            $changes['features'] = ['old' => $originalFeatures, 'new' => $newFeatures];
        }

        foreach ($originalLimits as $limitKey => $originalLimit) {
            if (isset($newLimits[$limitKey]) && (array_diff_assoc($originalLimit, $newLimits[$limitKey]) || array_diff_assoc($newLimits[$limitKey], $originalLimit))) {
                $changes['limits'][$limitKey] = ['old' => $originalLimit, 'new' => $newLimits[$limitKey]];
            }
        }
        return $changes;
    }

    public function statusUpdate($id)
    {
        $this->authorize('subscription_package_manage_status');

        $user = $this->subscriptionPackage->where('id', $id)->first();
        $this->subscriptionPackage->where('id', $id)->update(['is_active' => !$user->is_active]);

        if ($user->is_active){
            Toastr::success(translate(DEFAULT_STATUS_UPDATE_200['message']));
            return back();
        }

        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }

    /**
     * @throws AuthorizationException
     */
    public function changeSubscription(Request $request): JsonResponse|RedirectResponse
    {
        $this->authorize('subscription_package_manage_status');

        $oldSubscriptionId = $request->old_subscription;
        $newSubscriptionId = $request->new_subscription;

        if (!$oldSubscriptionId || !$newSubscriptionId || $newSubscriptionId == '') {
            Toastr::error(translate('Please Select a valid Package'));
            return back();
        }

        $subscription = $this->subscriptionPackage->where('id', $newSubscriptionId)->first();
        $oldPackageSubscriber = $this->packageSubscriber
            ->where('subscription_package_id', $oldSubscriptionId)
            ->where('package_end_date', '>', now())
            ->get();

        $price = $subscription->price;
        $name = $subscription->name;
        if ($oldPackageSubscriber){
            foreach ($oldPackageSubscriber as $subscriber){
                $payment = $this->paymentRequest;
                $payment->payment_amount = $price;
                $payment->success_hook = 'subscription_success';
                $payment->failure_hook = 'subscription_fail';
                $payment->payment_method = 'manually';
                $payment->additional_data = json_encode($request->all());
                $payment->attribute = 'provider-reg';
                $payment->attribute_id = $subscriber->provider_id;
                $payment->payment_platform = 'web';
                $payment->is_paid = 1;
                $payment->save();
                $request['payment_id'] = $payment->id;

                $result =  $this->handleShiftPackageSubscription($newSubscriptionId, $subscriber->provider_id, $request->all(), $price, $name);
                if (!$result) {
                    Toastr::error(translate('Something went wrong'));
                    return back();
                }
            }
        }else{
            Toastr::error(translate('Something went wrong'));
            return back();
        }

        $oldSubscription = $this->subscriptionPackage->where('id', $oldSubscriptionId)->first();
        $oldSubscription->is_active = 0 ;
        $oldSubscription->save();

        Toastr::success(translate('successfully status updated'));
        return back();
    }
    /**
     * @throws AuthorizationException
     */
    public function subscriptionToCommission(Request $request): JsonResponse|RedirectResponse
    {
        $this->authorize('subscription_package_manage_status');
        $usedTime   = (int)((business_config('usage_time', 'subscription_Setting'))->live_values ?? 0);
        $packageSubscriber = $this->packageSubscriber->get();

        foreach ($packageSubscriber as $subscriber) {
            $packageStartDate = Carbon::parse($subscriber->package_start_date)->subDay();
            $packageEndDate = Carbon::parse($subscriber->package_end_date);
            $now = Carbon::now();
            $providerId = $subscriber->provider_id;
            $provider = $this->provider::where('id', $providerId)->first();
            if ($now->lessThanOrEqualTo($packageEndDate)){
                $totalDuration = $packageStartDate->diffInDays($packageEndDate);
                $daysPassed = $packageStartDate->diffInDays($now);
                $percentageUsed = ($daysPassed / $totalDuration) * 100;
                $roundedPercentageUsed = ceil($percentageUsed);

                if ($usedTime > $roundedPercentageUsed && $provider) {
                    shiftRefundSubscriptionTransaction(
                        provider_id: $providerId
                    );
                }
            }

            try {
                Mail::to($subscriber?->provider?->owner?->email)->send(new SubscriptionToCommissionMail($provider));
            } catch (\Exception $exception) {
                info($exception);
            }

            $subscriber->delete();
        }

        $subscriptionStatus = $this->businessSettings
            ->where('key_name', 'provider_subscription')
            ->where('settings_type', 'provider_config')
            ->first();

        if ($subscriptionStatus){
            $subscriptionStatus->live_values = 0;
            $subscriptionStatus->save();
        }

        Toastr::success(translate('successfully subscription status updated'));
        return back();
    }

    public function commissionToSubscription(Request $request): JsonResponse|RedirectResponse
    {
        $this->authorize('subscription_package_manage_status');

        $id = $request->subscription_id;
        $subscription = $this->subscriptionPackage->find($id);

        if (!$subscription) {
            Toastr::error(translate('Subscription not found'));
            return back();
        }

        $providers = $this->provider->doesntHave('packageSubscriptions')->get();

        foreach ($providers as $provider) {
            $freeTrial = $this->handleFreeTrialPackageSubscription(
                id : $id,
                provider : $provider->id,
                price : $subscription->price,
                name : $subscription->name
            );

            if (!$freeTrial) {
                Toastr::error(translate('Something went wrong'));
                return back();
            }

            try {
                Mail::to($provider?->owner?->email)->send(new CommissionToSubscriptionMail($provider));
            } catch (\Exception $exception) {
                info($exception);
            }
        }

        $commissionStatus = $this->businessSettings
            ->where('key_name', 'provider_commision')
            ->where('settings_type', 'provider_config')
            ->first();

        if ($commissionStatus) {
            $commissionStatus->live_values = 0;
            $commissionStatus->save();
        }

        Toastr::success(translate('successfully commission status updated'));
        return back();
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
    public function download(Request $request): string|StreamedResponse
    {
        $this->authorize('subscription_package_export');

        $items = $this->subscriptionPackage
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('name', 'LIKE', '%' . $key . '%')
                            ->orWhere('price', 'LIKE', '%' . $key . '%')
                            ->orWhere('duration', 'LIKE', '%' . $key . '%')
                            ->orWhere('description', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->latest()->get();

        return (new FastExcel($items))->download(time() . '-file.xlsx');
    }

    public function transactions(Request $request)
    {
        $search = $request->has('search') ? $request->get('search') : '';
        $packageId = $request->has('package_id') ? $request->get('package_id') : '';
        $transactionType = $request->input('transaction_type', 'all');
        $dataRange = $request->has('date_range') ? $request['date_range'] : '';

        $queryParams = ['search' => $search, 'transaction_type' => $transactionType, 'package_id' => $packageId, 'date_range' => $dataRange];

        if ($request->input('date_range') === 'custom_date') {
            $queryParams['from'] = $request->input('from');
            $queryParams['to'] = $request->input('to');
        }

        $transactions = $this->transactions
            ->when($search, function ($query) use ($search) {
                $keys = explode(' ', $search);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('id', 'LIKE', '%' . $key . '%')
                            ->orWhereHas('packageLog.payment', function ($query) use ($key) {
                                $query->where('transaction_id', 'LIKE', '%' . $key . '%');
                            })
                            ->orWhereHas('packageLog.provider', function ($query) use ($key) {
                                $query->where('company_name', 'LIKE', '%' . $key . '%');
                            });
                    }
                });
            })
            ->when($packageId, function ($query) use ($packageId) {
                return $query->whereHas('packageLog', function ($query) use ($packageId) {
                    $query->where('subscription_package_id', $packageId);
                });
            })
            ->when($request->has('date_range'), function ($query) use ($request) {
                if ($request['date_range'] == 'this_week') {
                    $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                } elseif ($request['date_range'] == 'this_month') {
                    $query->whereMonth('created_at', Carbon::now()->month);
                } elseif ($request['date_range'] == 'this_year') {
                    $query->whereYear('created_at', Carbon::now()->year);
                } elseif ($request['date_range'] == 'custom_date') {
                    $startDate = Carbon::parse($request['from'])->startOfDay();
                    $endDate = Carbon::parse($request['to'])->endOfDay();
                    $endDate->setHour(23)->setMinute(59)->setSecond(59);

                    $query->whereBetween('created_at', [$startDate, $endDate]);
                }
            })
            ->with('packageLog.payment', 'packageLog.provider')
            ->whereIn('trx_type', ['subscription_purchase', 'subscription_renew', 'subscription_shift', 'subscription_refund'])
            ->latest()
            ->paginate(pagination_limit())
            ->appends($queryParams);

        return view('businesssettingsmodule::admin.subscription-package.transactions', compact('transactions', 'search','packageId', 'queryParams'));

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
        $search = $request->has('search') ? $request->get('search') : '';
        $packageId = $request->has('package_id') ? $request->get('package_id') : '';

        $items = $this->transactions
            ->when($search, function ($query) use ($search) {
                $keys = explode(' ', $search);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('id', 'LIKE', '%' . $key . '%')
                            ->orWhereHas('packageLog.payment', function ($query) use ($key) {
                                $query->where('transaction_id', 'LIKE', '%' . $key . '%');
                            })
                            ->orWhereHas('packageLog.provider', function ($query) use ($key) {
                                $query->where('company_name', 'LIKE', '%' . $key . '%');
                            });
                    }
                });
            })
            ->when($packageId, function ($query) use ($packageId) {
                return $query->whereHas('packageLog', function ($query) use ($packageId) {
                    $query->where('subscription_package_id', $packageId);
                });
            })
            ->when($request->has('date_range'), function ($query) use ($request) {
                if ($request['date_range'] == 'this_week') {
                    $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                } elseif ($request['date_range'] == 'this_month') {
                    $query->whereMonth('created_at', Carbon::now()->month);
                } elseif ($request['date_range'] == 'this_year') {
                    $query->whereYear('created_at', Carbon::now()->year);
                } elseif ($request['date_range'] == 'custom_date') {
                    $startDate = Carbon::parse($request['from'])->startOfDay();
                    $endDate = Carbon::parse($request['to'])->endOfDay();
                    $endDate->setHour(23)->setMinute(59)->setSecond(59);

                    $query->whereBetween('created_at', [$startDate, $endDate]);
                }
            })
            ->with('packageLog.payment', 'packageLog.provider')
            ->whereIn('trx_type', ['subscription_purchase', 'subscription_renew', 'subscription_shift', 'subscription_refund'])
            ->latest()
            ->get();

        return (new FastExcel($items))->download(time() . '-file.xlsx');
    }

    public function invoice($id)
    {
        $transaction = $this->transactions->with('packageLog.payment', 'packageLog.provider')->find($id);

        return view('businesssettingsmodule::admin.subscription-package.invoice', compact('transaction'));
    }

    public function subscriptionInvoice($id, $lang)
    {
        App::setLocale($lang);
        $transaction = $this->transactions->with('packageLog.payment', 'packageLog.provider')->find($id);

        return view('businesssettingsmodule::admin.subscription-package.invoice', compact('transaction'));
    }

}
