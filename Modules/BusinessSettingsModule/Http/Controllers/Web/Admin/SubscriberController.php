<?php

namespace Modules\BusinessSettingsModule\Http\Controllers\Web\Admin;

use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\BusinessSettingsModule\Emails\CancelSubscriptionMail;
use Modules\BusinessSettingsModule\Entities\PackageSubscriber;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Modules\BusinessSettingsModule\Entities\PackageSubscriberLog;
use Modules\BusinessSettingsModule\Entities\SubscriptionPackage;
use Modules\ProviderManagement\Entities\Provider;
use Modules\TransactionModule\Entities\Transaction;
use OpenSpout\Common\Exception\InvalidArgumentException;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Common\Exception\UnsupportedTypeException;
use OpenSpout\Writer\Exception\WriterNotOpenedException;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubscriberController extends Controller
{
    private PackageSubscriber $packageSubscriber;
    private SubscriptionPackage $subscriptionPackage;
    private PackageSubscriberLog $packageSubscriberLog;
    private  Transaction $transactions;
    private  Provider $provider;

    use AuthorizesRequests;

    public function __construct(PackageSubscriber $packageSubscriber, SubscriptionPackage $subscriptionPackage, PackageSubscriberLog $packageSubscriberLog, Transaction $transactions, Provider $provider)
    {
        $this->subscriptionPackage = $subscriptionPackage;
        $this->packageSubscriber = $packageSubscriber;
        $this->packageSubscriberLog = $packageSubscriberLog;
        $this->transactions = $transactions;
        $this->provider = $provider;
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     * @throws AuthorizationException
     */
    public function index(Request $request)
    {
        $this->authorize('subscriber_view');

        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $search = $request->has('search') ? $request->get('search') : '';
        $packageId = $request->has('package') ? $request->get('package') : null;
        $dateRange = $request->has('date_range') ? $request->get('date_range') : 'all';
        $queryParams = ['search' => $search, 'package' => $packageId, 'date_range' =>$dateRange ];

        $deadlineWarning = (int)((business_config('deadline_warning', 'subscription_Setting'))->live_values ?? 0);

        $packageSubscribers = $this->packageSubscriber
            ->when($request->has('date_range'), function ($query) use ($request) {
                if ($request['date_range'] == 'this_week') {
                    $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                } elseif ($request['date_range'] == 'this_month') {
                    $query->whereMonth('created_at', Carbon::now()->month);
                } elseif ($request['date_range'] == 'this_year') {
                    $query->whereYear('created_at', Carbon::now()->year);
                }
            })
            ->get();

        $deadlineDate = Carbon::now()->addDays($deadlineWarning);

        $warningSubscribersCount = $packageSubscribers->filter(function($subscriber) use ($deadlineDate) {
            $packageEndDate = Carbon::parse($subscriber->package_end_date);
            $currentDate = Carbon::now();

            return $packageEndDate->lessThanOrEqualTo($deadlineDate) && $packageEndDate->greaterThan($currentDate);
        })->count();

        $subscriptions = $this->subscriptionPackage->ofStatus(1)->get();

        $subscribers = $this->packageSubscriber
            ->when($search, function ($query) use ($search) {
                $keys = explode(' ', $search);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('package_name', 'LIKE', '%' . $key . '%')
                            ->orWhere('package_price', 'LIKE', '%' . $key . '%')
                            ->orWhereHas('provider', function ($query) use ($key) {
                                $query->where('company_name', 'LIKE', '%' . $key . '%')
                                    ->orWhere('company_email', 'LIKE', '%' . $key . '%');
                            });
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
            ->when($packageId, function ($query) use ($packageId) {
                if ($packageId !== 'all') {
                    $query->where('subscription_package_id', $packageId);
                }
            })
            ->latest()
            ->paginate(pagination_limit())
            ->appends($queryParams);

        $totalEarning = $this->packageSubscriberLog
            ->sum(DB::raw('package_price + vat_amount'));

        $totalEarningThisMonth = $this->packageSubscriberLog
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->sum(DB::raw('package_price + vat_amount'));

        $totalTransactions = $this->transactions
            ->whereIn('trx_type', ['subscription_purchase', 'subscription_renew', 'subscription_shift', 'subscription_refund'])
            ->count();

        return view('businesssettingsmodule::admin.subscriber.list', compact('subscribers', 'subscriptions', 'packageSubscribers', 'warningSubscribersCount','search', 'packageId', 'queryParams', 'totalEarning', 'totalEarningThisMonth', 'totalTransactions'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     * @throws AuthorizationException
     */
    public function create()
    {
        $this->authorize('subscriber_add');
        return view('businesssettingsmodule::create');
    }

    public function details($id)
    {
        $this->authorize('subscriber_view');

        $subscriptionDetails = $this->packageSubscriber->where('id', $id)->first();
        $subscriptionPrice = $this->subscriptionPackage->where('id', $subscriptionDetails?->subscription_package_id)->value('price');
        $vatPercentage      = (int)((business_config('subscription_vat', 'subscription_Setting'))->live_values ?? 0);

        $start = Carbon::parse($subscriptionDetails?->package_start_date)->subDay() ?? '';
        $end = Carbon::parse($subscriptionDetails?->package_end_date)->endOfDay() ?? '';
        $monthsDifference = $start->diffInDays($end) ?? '';
        $providerId = $subscriptionDetails?->provider_id;

        $totalPurchase = $subscriptionDetails?->logs->where('provider_id', $providerId)->count() ?? 0;
        $calculationVat = $subscriptionPrice * ($vatPercentage / 100);
        $renewalPrice = $subscriptionPrice + $calculationVat;

        $bookingCheck = $subscriptionDetails?->limits->where('provider_id', $providerId)->where('key', 'booking')->first();
        $categoryCheck = $subscriptionDetails?->limits->where('provider_id', $providerId)->where('key', 'category')->first();
        $isBookingLimit = $bookingCheck?->is_limited;
        $isCategoryLimit = $categoryCheck?->is_limited;
        return view('businesssettingsmodule::admin.subscriber.details', compact('subscriptionDetails','monthsDifference', 'bookingCheck', 'categoryCheck', 'isBookingLimit', 'isCategoryLimit', 'renewalPrice', 'totalPurchase', 'subscriptionPrice'));
    }


    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function cancel( Request $request): RedirectResponse
    {
        $packageId = $request->package_id;
        $providerId = $request->provider_id;

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

            Toastr::error(translate('Subscription canceled successfully'));
            return back();
        }

        Toastr::error(translate('Please Select valid plan'));
        return back();

    }

    public function transactions(Request $request)
    {
        $search = $request->has('search') ? $request->get('search') : '';
        $packageId = $request->has('package_id') ? $request->get('package_id') : '';
        $providerId = $request->has('provider_id') ? $request->get('provider_id') : '';
        $transactionType = $request->input('transaction_type', 'all');

        $queryParams = ['search' => $search, 'transaction_type' => $transactionType, 'package_id' => $packageId, 'provider_id' => $providerId];

        if ($request->input('date_range') === 'custom_date') {
            $queryParams['from'] = $request->input('from');
            $queryParams['to'] = $request->input('to');
        }

        $providerId = $request['provider_id'];
        $provider = $this->provider->find($providerId);
        $providerUserId =$provider->user_id;

        $transactions = $this->transactions
            ->filterDateRange($request->input('date_range'), $request->input('from'), $request->input('to'))
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
            ->with('packageLog.payment', 'packageLog.provider')
            ->whereIn('trx_type', ['subscription_purchase', 'subscription_renew', 'subscription_shift', 'subscription_refund'])
            ->latest()
            ->paginate(pagination_limit())
            ->appends($queryParams);

        return view('businesssettingsmodule::admin.subscriber.transaction', compact('transactions', 'providerId', 'search','packageId', 'provider', 'queryParams'));

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
        $search = $request->has('search') ? $request->get('search') : '';
        $packageId = $request->has('package') ? $request->get('package') : null;
        $items = $this->packageSubscriber
            ->when($search, function ($query) use ($search) {
                $keys = explode(' ', $search);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('package_name', 'LIKE', '%' . $key . '%')
                            ->orWhere('package_price', 'LIKE', '%' . $key . '%')
                            ->orWhereHas('provider', function ($query) use ($key) {
                                $query->where('company_name', 'LIKE', '%' . $key . '%')
                                    ->orWhere('company_email', 'LIKE', '%' . $key . '%');
                            });
                    }
                });
            })
            ->when($packageId, function ($query) use ($packageId) {
                if ($packageId !== 'all') {
                    $query->where('subscription_package_id', $packageId);
                }
            })
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
        $providerId = $request['provider_id'];
        $provider = $this->provider->find($providerId);
        $providerUserId =$provider->user_id;
        $search = $request->has('search') ? $request->get('search') : '';

        $items = $this->transactions
            ->where(function ($q) use ($providerUserId) {
                $q->where('from_user_id', $providerUserId)
                    ->orWhere('to_user_id', $providerUserId);
            })
            ->where(function ($q) use ($providerUserId) {
                $q->where('from_user_id', $providerUserId)
                    ->orWhere('to_user_id', $providerUserId);
            })
            ->when($search, function ($query) use ($search) {
                $keys = explode(' ', $search);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('id', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->whereIn('trx_type', ['subscription_purchase', 'subscription_renew', 'subscription_shift', 'subscription_refund'])
            ->latest()
            ->get();

        return (new FastExcel($items))->download(time() . '-file.xlsx');
    }
    public function invoice($id)
    {
        $transaction = $this->transactions->with('packageLog.payment', 'packageLog.provider')->find($id);

        return view('businesssettingsmodule::admin.subscriber.invoice', compact('transaction'));
    }

}
