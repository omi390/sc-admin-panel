<?php

namespace Modules\AdminModule\Http\Controllers\Web\Admin\Report\Business;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\BookingModule\Entities\Booking;
use Modules\BookingModule\Entities\BookingDetailsAmount;
use Modules\BusinessSettingsModule\Entities\PackageSubscriber;
use Modules\BusinessSettingsModule\Entities\SubscriptionPackage;
use Modules\CategoryManagement\Entities\Category;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ServiceManagement\Entities\Service;
use Modules\TransactionModule\Entities\Account;
use Modules\TransactionModule\Entities\Transaction;
use Modules\UserManagement\Entities\User;
use Modules\ZoneManagement\Entities\Zone;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EarningReportController extends Controller
{
    protected Zone $zone;
    protected Provider $provider;
    protected Category $categories;
    protected Booking $booking;

    protected Account $account;
    protected Service $service;
    protected User $user;
    protected Transaction $transaction;
    protected BookingDetailsAmount $bookingDetailsAmount;
    protected SubscriptionPackage $subscriptionPackage;
    protected PackageSubscriber $packageSubscriber;

    public function __construct(Zone $zone, SubscriptionPackage $subscriptionPackage, PackageSubscriber $packageSubscriber, Provider $provider, Category $categories, Service $service, Booking $booking, Account $account, User $user, Transaction $transaction, BookingDetailsAmount $bookingDetailsAmount)
    {
        $this->zone = $zone;
        $this->provider = $provider;
        $this->categories = $categories;
        $this->booking = $booking;

        $this->service = $service;
        $this->account = $account;
        $this->user = $user;
        $this->transaction = $transaction;
        $this->bookingDetailsAmount = $bookingDetailsAmount;
        $this->subscriptionPackage = $subscriptionPackage;
        $this->packageSubscriber = $packageSubscriber;
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function getBusinessEarningReport(Request $request)
    {
        Validator::make($request->all(), [
            'zone_ids' => 'array',
            'zone_ids.*' => 'uuid',
            'category_ids' => 'array',
            'category_ids.*' => 'uuid',
            'sub_category_ids' => 'array',
            'sub_category_ids.*' => 'uuid',
            'date_range' => 'in:all_time, this_week, last_week, this_month, last_month, last_15_days, this_year, last_year, last_6_month, this_year_1st_quarter, this_year_2nd_quarter, this_year_3rd_quarter, this_year_4th_quarter, custom_date',
            'from' => $request['date_range'] == 'custom_date' ? 'required' : '',
            'to' => $request['date_range'] == 'custom_date' ? 'required' : '',
        ]);

        $zones = $this->zone->ofStatus(1)->select('id', 'name')->get();
        $categories = $this->categories->ofType('main')->select('id', 'name')->get();
        $sub_categories = $this->categories->ofType('sub')->select('id', 'name')->get();

        $search = $request['search'];
        $queryParams = ['search' => $search];
        if ($request->has('zone_ids')) {
            $queryParams['zone_ids'] = $request['zone_ids'];
        }
        if ($request->has('category_ids')) {
            $queryParams['category_ids'] = $request['category_ids'];
        }
        if ($request->has('sub_category_ids')) {
            $queryParams['sub_category_ids'] = $request['sub_category_ids'];
        }
        if ($request->has('date_range')) {
            $queryParams['date_range'] = $request['date_range'];
        }
        if ($request->has('date_range') && $request['date_range'] == 'custom_date') {
            $queryParams['from'] = $request['from'];
            $queryParams['to'] = $request['to'];
        }


        $date_range = $request['date_range'];
        if (is_null($date_range) || $date_range == 'all_time') {
            $deterministic = 'year';
        } elseif ($date_range == 'this_week' || $date_range == 'last_week') {
            $deterministic = 'week';
        } elseif ($date_range == 'this_month' || $date_range == 'last_month' || $date_range == 'last_15_days') {
            $deterministic = 'day';
        } elseif ($date_range == 'this_year' || $date_range == 'last_year' || $date_range == 'last_6_month' || $date_range == 'this_year_1st_quarter' || $date_range == 'this_year_2nd_quarter' || $date_range == 'this_year_3rd_quarter' || $date_range == 'this_year_4th_quarter') {
            $deterministic = 'month';
        } elseif ($date_range == 'custom_date') {
            $from = Carbon::parse($request['from'])->startOfDay();
            $to = Carbon::parse($request['to'])->endOfDay();
            $diff = Carbon::parse($from)->diffInDays($to);

            if ($diff <= 7) {
                $deterministic = 'week';
            } elseif ($diff <= 30) {
                $deterministic = 'day';
            } elseif ($diff <= 365) {
                $deterministic = 'month';
            } else {
                $deterministic = 'year';
            }
        }
        $group_by_deterministic = $deterministic == 'week' ? 'day' : $deterministic;

        $amounts = $this->bookingDetailsAmount
            ->whereHas('booking', function ($query) use ($request) {
                self::filterQuery($query, $request)->ofBookingStatus('completed');
            })
            ->when(isset($group_by_deterministic), function ($query) use ($group_by_deterministic) {
                $query->select(
                    DB::raw('sum(service_unit_cost) as service_unit_cost'),
                    DB::raw('sum(discount_by_admin) as discount_by_admin'),
                    DB::raw('sum(discount_by_provider) as discount_by_provider'),
                    DB::raw('sum(coupon_discount_by_admin) as coupon_discount_by_admin'),
                    DB::raw('sum(coupon_discount_by_provider) as coupon_discount_by_provider'),
                    DB::raw('sum(campaign_discount_by_admin) as campaign_discount_by_admin'),
                    DB::raw('sum(campaign_discount_by_provider) as campaign_discount_by_provider'),
                    DB::raw('sum(admin_commission) as admin_commission'),

                    DB::raw($group_by_deterministic . '(created_at) ' . $group_by_deterministic)
                );
            })
            ->groupby($group_by_deterministic)
            ->get()->toArray();

        $earningAmount = $this->transaction
            ->whereIn('trx_type', [
                TRX_TYPE['received_extra_fee'],
                TRX_TYPE['subscription_purchase'],
                TRX_TYPE['subscription_renew'],
                TRX_TYPE['subscription_shift']
            ])
            ->when($request->has('date_range'), function ($query) use($request) {
                $this->applyDateRangeConditions($query, $request);
            })
            ->when(isset($group_by_deterministic), function ($query) use ($group_by_deterministic) {
                // Ensure that we are using proper SQL syntax with quotes around transaction types
                $subscriptionTypes = implode(',', [
                    "'" . TRX_TYPE['subscription_purchase'] . "'",
                    "'" . TRX_TYPE['subscription_renew'] . "'",
                    "'" . TRX_TYPE['subscription_shift'] . "'"
                ]);

                $query->select(
                    DB::raw("SUM(CASE WHEN trx_type IN ($subscriptionTypes) THEN credit ELSE 0 END) as subscription_earning"),
                    DB::raw("SUM(CASE WHEN trx_type = '" . TRX_TYPE['received_extra_fee'] . "' THEN credit ELSE 0 END) as platform_fee"),
                    DB::raw("$group_by_deterministic(created_at) as $group_by_deterministic")
                );
            })
            ->groupBy($group_by_deterministic)
            ->get()
            ->toArray();

        $all_earnings = [];

// Merge logic here remains unchanged
        if (empty($amounts) && !empty($earningAmount)) {
            foreach ($earningAmount as $key => $earning) {
                $all_earnings[$key] = $earning;
                if (!array_key_exists('subscription_earning', $all_earnings[$key])) {
                    $all_earnings[$key]['subscription_earning'] = 0;
                }
                if (!array_key_exists('platform_fee', $all_earnings[$key])) {
                    $all_earnings[$key]['platform_fee'] = 0;
                }
            }
        } else {
            foreach ($amounts as $amount) {
                $merged = false;
                foreach ($earningAmount as $key => $earning) {
                    if ($amount[$group_by_deterministic] == $earning[$group_by_deterministic]) {
                        $all_earnings[$key] = array_merge($amount, $earning);
                        $merged = true;
                    }
                }
                if (!$merged) {
                    $all_earnings[] = $amount;
                    end($all_earnings);
                    $lastKey = key($all_earnings);
                    if (!array_key_exists('subscription_earning', $all_earnings[$lastKey])) {
                        $all_earnings[$lastKey]['subscription_earning'] = 0;
                    }
                    if (!array_key_exists('platform_fee', $all_earnings[$lastKey])) {
                        $all_earnings[$lastKey]['platform_fee'] = 0;
                    }
                }
            }
        }

        if (empty($earningAmount) && !empty($amounts)) {
            foreach ($amounts as $key => $amount) {
                $all_earnings[$key] = $amount;
                if (!array_key_exists('subscription_earning', $all_earnings[$key])) {
                    $all_earnings[$key]['subscription_earning'] = 0;
                }
                if (!array_key_exists('platform_fee', $all_earnings[$key])) {
                    $all_earnings[$key]['platform_fee'] = 0;
                }
            }
        }



        $chart_data = ['net_profit' => array(), 'total_earning' => array(), 'commission_earning' => array(), 'subscription_earning' => array(), 'platform_fee' => array(), 'timeline' => array()];
        if ($deterministic == 'month') {
            $months = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
            foreach ($months as $month) {
                $found = 0;
                $chart_data['timeline'][] = $month;
                foreach ($all_earnings as $key => $item) {
                    if (isset($item['month']) && $item['month'] == $month) {
                        $admin_commission = $item['admin_commission'] ?? 0;
                        $subscriptionEarning = $item['subscription_earning'] ?? 0;
                        $platformFee = $item['platform_fee'] ?? 0;
                        $discount_by_admin = $item['discount_by_admin'] ?? 0;
                        $coupon_discount_by_admin = $item['coupon_discount_by_admin'] ?? 0;
                        $campaign_discount_by_admin = $item['campaign_discount_by_admin'] ?? 0;

                        $chart_data['net_profit'][] = with_decimal_point(($admin_commission + $subscriptionEarning + $platformFee) - ($discount_by_admin + $coupon_discount_by_admin + $campaign_discount_by_admin));
                        $chart_data['total_earning'][] = with_decimal_point($admin_commission + $subscriptionEarning + $platformFee);
                        $chart_data['commission_earning'][] = with_decimal_point($admin_commission);
                        $chart_data['subscription_earning'][] = with_decimal_point($subscriptionEarning);
                        $chart_data['platform_fee'][] = with_decimal_point($platformFee);

                        $found = 1;
                    }
                }
                if (!$found) {
                    $chart_data['net_profit'][] = with_decimal_point(0);
                    $chart_data['total_earning'][] = with_decimal_point(0);
                    $chart_data['commission_earning'][] = with_decimal_point(0);
                    $chart_data['subscription_earning'][] = with_decimal_point(0);
                    $chart_data['platform_fee'][] = with_decimal_point(0);
                }
            }

        } elseif ($deterministic == 'year') {
            foreach ($all_earnings as $key => $item) {
                $admin_commission = $item['admin_commission'] ?? 0;
                $subscriptionEarning = $item['subscription_earning'] ?? 0;
                $platformFee = $item['platform_fee'] ?? 0;
                $discount_by_admin = $item['discount_by_admin'] ?? 0;
                $coupon_discount_by_admin = $item['coupon_discount_by_admin'] ?? 0;
                $campaign_discount_by_admin = $item['campaign_discount_by_admin'] ?? 0;

                $chart_data['net_profit'][] = with_decimal_point(($admin_commission + $subscriptionEarning + $platformFee) - ($discount_by_admin + $coupon_discount_by_admin + $campaign_discount_by_admin));
                $chart_data['total_earning'][] = with_decimal_point($admin_commission  + $subscriptionEarning + $platformFee);
                $chart_data['commission_earning'][] = with_decimal_point($admin_commission);
                $chart_data['subscription_earning'][] = with_decimal_point($subscriptionEarning);
                $chart_data['platform_fee'][] = with_decimal_point($platformFee);
                $chart_data['timeline'][] = $item[$deterministic];

            }
        } elseif ($deterministic == 'day') {
            if ($date_range == 'this_month') {
                $to = Carbon::now()->lastOfMonth();
            } elseif ($date_range == 'last_month') {
                $to = Carbon::now()->subMonth()->endOfMonth();
            } elseif ($date_range == 'last_15_days') {
                $to = Carbon::now();
            }

            $number = date('d', strtotime($to));

            for ($i = 1; $i <= $number; $i++) {
                $found = 0;
                $chart_data['timeline'][] = $i;
                foreach ($all_earnings as $key => $item) {
                    if (isset($item['day']) && $item['day'] == $i) {
                        $admin_commission = $item['admin_commission'] ?? 0;
                        $subscriptionEarning = $item['subscription_earning'] ?? 0;
                        $platformFee = $item['platform_fee'] ?? 0;
                        $discount_by_admin = $item['discount_by_admin'] ?? 0;
                        $coupon_discount_by_admin = $item['coupon_discount_by_admin'] ?? 0;
                        $campaign_discount_by_admin = $item['campaign_discount_by_admin'] ?? 0;

                        $chart_data['net_profit'][] = with_decimal_point(($admin_commission + $subscriptionEarning + $platformFee) - ($discount_by_admin + $coupon_discount_by_admin + $campaign_discount_by_admin));
                        $chart_data['total_earning'][] = with_decimal_point($admin_commission + $subscriptionEarning + $platformFee);
                        $chart_data['commission_earning'][] = with_decimal_point($admin_commission);
                        $chart_data['subscription_earning'][] = with_decimal_point($subscriptionEarning);
                        $chart_data['platform_fee'][] = with_decimal_point($platformFee);

                        $found = 1;
                    }
                }
                if (!$found) {
                    $chart_data['net_profit'][] = with_decimal_point(0);
                    $chart_data['total_earning'][] = with_decimal_point(0);
                    $chart_data['commission_earning'][] = with_decimal_point(0);
                    $chart_data['subscription_earning'][] = with_decimal_point(0);
                    $chart_data['platform_fee'][] = with_decimal_point(0);
                }
            }
        } elseif ($deterministic == 'week') {
            if ($date_range == 'this_week') {
                $from = Carbon::now()->startOfWeek();
                $to = Carbon::now()->endOfWeek();
            } elseif ($date_range == 'last_week') {
                $from = Carbon::now()->subWeek()->startOfWeek();
                $to = Carbon::now()->subWeek()->endOfWeek();
            }

            for ($i = (int)$from->format('d'); $i <= (int)$to->format('d'); $i++) {
                $found = 0;
                $chart_data['timeline'][] = $i;
                foreach ($all_earnings as $key => $item) {
                    if (isset($item['day']) && $item['day'] == $i) {
                        $admin_commission = $item['admin_commission'] ?? 0;
                        $subscriptionEarning = $item['subscription_earning'] ?? 0;
                        $platformFee = $item['platform_fee'] ?? 0;
                        $discount_by_admin = $item['discount_by_admin'] ?? 0;
                        $coupon_discount_by_admin = $item['coupon_discount_by_admin'] ?? 0;
                        $campaign_discount_by_admin = $item['campaign_discount_by_admin'] ?? 0;

                        $chart_data['net_profit'][] = with_decimal_point(
                            ($admin_commission + $subscriptionEarning + $platformFee) - ($discount_by_admin + $coupon_discount_by_admin + $campaign_discount_by_admin)
                        );
                        $chart_data['total_earning'][] = with_decimal_point($admin_commission + $subscriptionEarning + $platformFee);
                        $chart_data['commission_earning'][] = with_decimal_point($admin_commission);
                        $chart_data['subscription_earning'][] = with_decimal_point($subscriptionEarning);
                        $chart_data['platform_fee'][] = with_decimal_point($platformFee);

                        $found = 1;
                    }
                }
                if (!$found) {
                    $chart_data['net_profit'][] = with_decimal_point(0);
                    $chart_data['total_earning'][] = with_decimal_point(0);
                    $chart_data['commission_earning'][] = with_decimal_point(0);
                    $chart_data['subscription_earning'][] = with_decimal_point(0);
                    $chart_data['platform_fee'][] = with_decimal_point(0);
                }
            }
        }

        $bookings = self::filterQuery($this->booking, $request)
            ->with(['details_amounts'])
            ->ofBookingStatus('completed')
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->where('readable_id', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->latest()
            ->latest()->paginate(pagination_limit())->appends($queryParams);

        return view('adminmodule::admin.report.business.earning', compact('zones', 'categories', 'sub_categories', 'bookings', 'chart_data', 'queryParams'));
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function getBusinessSubscriptionEarningReport(Request $request): Renderable
    {
        $barFilter = $request->input('bar_filter', 1);
        $currentDate = Carbon::now();

        $subscriptionPackages = $this->subscriptionPackage->get();

        $subscriptionTotal = $subscriptionPackages->reduce(function ($carry, $package) {
            return $carry + $package->subscriberPackageLogs->sum('package_price');
        }, 0);

        $groupedData = [];
        $currentYear = now()->year;
        $lastYear = now()->subYear()->year;
        $lastMonth = now()->subMonth()->format('F');
        $thisMonth = now()->format('F');

        foreach ($subscriptionPackages as $package) {
            foreach ($package->subscriberPackageLogs as $log) {
                $year = \Carbon\Carbon::parse($log->created_at)->year;
                $month = \Carbon\Carbon::parse($log->created_at)->format('F');
                $packagePrice = $log->package_price;

                if (!isset($groupedData[$year])) {
                    $groupedData[$year] = ['yearly_sum' => 0, 'monthly_data' => []];
                }

                $groupedData[$year]['yearly_sum'] += $packagePrice;

                if (!isset($groupedData[$year]['monthly_data'][$month])) {
                    $groupedData[$year]['monthly_data'][$month] = 0;
                }
                $groupedData[$year]['monthly_data'][$month] += $packagePrice;
            }
        }

        $years = array_keys($groupedData);
        $yearCount = count($years);

        $chartData = [];
        $categories = [];
        $monthOrder = [
            'January' => 1,
            'February' => 2,
            'March' => 3,
            'April' => 4,
            'May' => 5,
            'June' => 6,
            'July' => 7,
            'August' => 8,
            'September' => 9,
            'October' => 10,
            'November' => 11,
            'December' => 12,
        ];

        switch ($barFilter) {
            case 2:
                foreach ($groupedData as $year => $data) {
                    if (isset($data['monthly_data'][$lastMonth])) {
                        $categories[] = $lastMonth;
                        $chartData[] = $data['monthly_data'][$lastMonth];
                    }
                }
                break;

            case 3:
                if (isset($groupedData[$currentYear])) {
                    $categories[] = $currentYear;
                    $chartData[] = $groupedData[$currentYear]['yearly_sum'];
                } else {
                    $categories[] = 'No Data';
                    $chartData[] = 0;
                }
                break;

            case 4:
                if (isset($groupedData[$lastYear])) {
                    $categories[] = $lastYear;
                    $chartData[] = $groupedData[$lastYear]['yearly_sum'];
                } else {
                    $categories[] = 'No Data';
                    $chartData[] = 0;
                }
                break;

            case 5:
                foreach ($groupedData as $year => $data) {
                    if (isset($data['monthly_data'][$thisMonth])) {
                        $categories[] = $thisMonth;
                        $chartData[] = $data['monthly_data'][$thisMonth];
                    }
                }
                break;

            case 1:
            default:
                if ($yearCount > 1) {
                    foreach ($groupedData as $year => $data) {
                        $categories[] = $year;
                        $chartData[] = $data['yearly_sum'];
                    }
                } else {
                    $singleYear = $years[0] ?? null;
                    if ($singleYear) {
                        foreach ($groupedData[$singleYear]['monthly_data'] as $month => $price) {
                            $categories[] = $month;
                            $chartData[] = $price;
                        }
                    } else {
                        $categories[] = 'No Data';
                        $chartData[] = 0;
                    }
                }
                break;
        }

        if ($barFilter == 2 || $barFilter == 5 || $yearCount == 1) {
            array_multisort(array_map(function($month) use ($monthOrder) {
                return $monthOrder[$month] ?? 0; // Ensure the month exists in $monthOrder
            }, $categories), $categories, $chartData);
        }


        $search = $request->input('search');
        $filter = $request->input('filter');

        $packagesSubscribers = $this->packageSubscriber
            ->where('trial_duration', '==', 0)
            ->when($search, function ($query, $search) {
                return $query->where('package_name', 'like', '%' . $search . '%');
            })
            ->latest()->paginate(pagination_limit());

        $packagesSubscriber = $this->packageSubscriber
            ->where('trial_duration', 0)
            ->when($filter, function ($query, $filter) use ($currentDate) {
                if ($filter == 2) {
                    return $query->whereBetween('updated_at', [
                        $currentDate->copy()->subMonth()->startOfMonth(),
                        $currentDate->copy()->subMonth()->endOfMonth()
                    ]);
                } elseif ($filter == 3) {
                    return $query->whereYear('created_at', $currentDate->year);
                } elseif ($filter == 4) {
                    return $query->whereYear('created_at', $currentDate->copy()->subYear()->year);
                } elseif ($filter == 5) {
                    return $query->whereBetween('updated_at', [
                        $currentDate->copy()->startOfMonth(),
                        $currentDate->copy()->endOfMonth()
                    ]);
                }
            })
            ->get();

        $currentDate = Carbon::now();
        $totalSubscribers = $packagesSubscriber->count();

        $activeSubscribers = $packagesSubscriber->filter(function($subscriber) use ($currentDate) {
            return !$subscriber->is_canceled &&
                $subscriber->package_end_date >= $currentDate &&
                $subscriber->provider && $subscriber->provider->is_active == 1;
        })->count();

        $inactiveSubscribers = $packagesSubscriber->filter(function($subscriber) use ($currentDate) {
            return $subscriber->is_canceled ||
                $subscriber->package_end_date <= $currentDate ||
                !$subscriber->provider || $subscriber->provider->is_active == 0;
        })->count();

        $pieChartData = [
            'totalSubscribers' => $totalSubscribers,
            'activeSubscribers' => $activeSubscribers,
            'inactiveSubscribers' => $inactiveSubscribers
        ];

        return view('adminmodule::admin.report.business.subscription-earning', compact('subscriptionPackages', 'packagesSubscribers', 'subscriptionTotal', 'chartData', 'categories', 'pieChartData', 'packagesSubscriber', 'search', 'filter', 'barFilter'));
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function getBusinessCommissionEarningReport(Request $request)
    {
        $barFilter = $request->input('bar_filter', 1);
        $filter = $request->input('filter');
        $search = $request->input('search');
        $currentDate = Carbon::now();

        $query = $this->bookingDetailsAmount->where('admin_commission', '!=' , 0)
            ->whereHas('booking', function ($query) use ($request) {
                self::filterQuery($query, $request)->ofBookingStatus('completed');
            });

        $commissionEarning = $query->get();
        $commissionEarningList = $query
            ->when($search, function ($query, $search) {
                return $query->where('booking_id', 'like', '%' . $search . '%');
            })
            ->latest()->paginate(pagination_limit());

        $groupedData = [];
        $currentYear = now()->year;
        $lastYear = now()->subYear()->year;
        $lastMonth = now()->startOfMonth()->subMonth()->format('F');
        $thisMonth = now()->format('F');

        foreach ($commissionEarning as $earning) {
            $year = \Carbon\Carbon::parse($earning->created_at)->year;
            $month = \Carbon\Carbon::parse($earning->created_at)->format('F');
            $commission = $earning->admin_commission;

            if (!isset($groupedData[$year])) {
                $groupedData[$year] = ['yearly_sum' => 0, 'monthly_data' => []];
            }

            $groupedData[$year]['yearly_sum'] += $commission;

            if (!isset($groupedData[$year]['monthly_data'][$month])) {
                $groupedData[$year]['monthly_data'][$month] = 0;
            }
            $groupedData[$year]['monthly_data'][$month] += $commission;
        }

        $years = array_keys($groupedData);
        $yearCount = count($years);

        $chartData = [];
        $categories = [];

        switch ($barFilter) {
            case 2:
                $startOfLastMonth = Carbon::now()->startOfMonth()->subMonth();
                $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth();

                $commissionEarning = $query->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->get();

                if ($commissionEarning->isNotEmpty()) {
                    $categories[] = $lastMonth;
                    $monthlySum = $commissionEarning->sum('admin_commission');
                    $chartData[] = $monthlySum;
                } else {
                    $categories[] = 'No Data';
                    $chartData[] = 0;
                }
                break;

            case 3:
                if (isset($groupedData[$currentYear])) {
                    $categories[] = $currentYear;
                    $chartData[] = $groupedData[$currentYear]['yearly_sum'];
                } else {
                    $categories[] = 'No Data';
                    $chartData[] = 0;
                }
                break;

            case 4:
                if (isset($groupedData[$lastYear])) {
                    $categories[] = $lastYear;
                    $chartData[] = $groupedData[$lastYear]['yearly_sum'];
                } else {
                    $categories[] = 'No Data';
                    $chartData[] = 0;
                }
                break;

            case 5:
                if (isset($groupedData[$currentYear]['monthly_data'][$thisMonth])) {
                    $categories[] = $thisMonth;
                    $chartData[] = $groupedData[$currentYear]['monthly_data'][$thisMonth];
                } else {
                    $categories[] = 'No Data';
                    $chartData[] = 0;
                }
                break;

            case 1:
            default:
                if (!empty($years)) {
                    if ($yearCount > 1) {
                        foreach ($groupedData as $year => $data) {
                            $categories[] = $year;
                            $chartData[] = $data['yearly_sum'];
                        }
                    } else {
                        $singleYear = $years[0];
                        if (isset($groupedData[$singleYear]['monthly_data'])) {
                            foreach ($groupedData[$singleYear]['monthly_data'] as $month => $price) {
                                $categories[] = $month;
                                $chartData[] = $price;
                            }
                        } else {
                            $categories[] = 'No Data';
                            $chartData[] = 0;
                        }
                    }
                } else {
                    $categories[] = 'No Data';
                    $chartData[] = 0;
                }
                break;
        }

        $providerList = $this->provider->whereDoesntHave('packageSubscriptions')->ofApproval(1);
        $allProvidersQuery = $providerList->clone()->when($filter, function ($query, $filter) use ($currentDate) {
        if ($filter == 2) {
            return $query->whereBetween('created_at', [
                $currentDate->copy()->subMonth()->startOfMonth(),
                $currentDate->copy()->subMonth()->endOfMonth()
            ]);
        } elseif ($filter == 3) {
            return $query->whereYear('created_at', $currentDate->year);
        } elseif ($filter == 4) {
            return $query->whereYear('created_at', $currentDate->copy()->subYear()->year);
        } elseif ($filter == 5) {
            return $query->whereBetween('created_at', [
                $currentDate->copy()->startOfMonth(),
                $currentDate->copy()->endOfMonth()
            ]);
        }
    });

        $totalProvider = $providerList->count();
        $inActiveProvider = $allProvidersQuery->clone()->ofStatus(0)->count();
        $activeProvider = $allProvidersQuery->clone()->ofStatus(1)->count();

        $providers = [
            'total_provider' => $totalProvider,
            'active_provider' => $activeProvider,
            'inactive_provider' => $inActiveProvider
        ];

        return view('adminmodule::admin.report.business.comission-earning', compact('commissionEarning', 'commissionEarningList', 'providers','categories', 'chartData', 'barFilter', 'filter', 'search'));
    }

    public function getBusinessEarningReportDownload(Request $request): StreamedResponse|string
    {
        Validator::make($request->all(), [
            'zone_ids' => 'array',
            'zone_ids.*' => 'uuid',
            'category_ids' => 'array',
            'category_ids.*' => 'uuid',
            'sub_category_ids' => 'array',
            'sub_category_ids.*' => 'uuid',
            'date_range' => 'in:all_time, this_week, last_week, this_month, last_month, last_15_days, this_year, last_year, last_6_month, this_year_1st_quarter, this_year_2nd_quarter, this_year_3rd_quarter, this_year_4th_quarter, custom_date',
            'from' => $request['date_range'] == 'custom_date' ? 'required' : '',
            'to' => $request['date_range'] == 'custom_date' ? 'required' : '',
        ]);

        $bookings = self::filterQuery($this->booking, $request)
            ->with(['details_amounts'])
            ->ofBookingStatus('completed')
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->where('readable_id', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->latest()
            ->get();

        foreach ($bookings as $booking) {
            $admin_commission_without_earning = 0;

            $discount_by_admin = 0;
            $discount_by_provider = 0;
            $coupon_discount_by_admin = 0;
            $coupon_discount_by_provider = 0;
            $campaign_discount_by_admin = 0;
            $campaign_discount_by_provider = 0;

            $admin_commission_with_cost = 0;

            $admin_net_income = 0;
            $provider_net_income = 0;

            foreach ($booking->details_amounts as $key => $item) {
                $discount_by_admin += $item['discount_by_admin'];
                $discount_by_provider += $item['discount_by_provider'];
                $coupon_discount_by_admin += $item['coupon_discount_by_admin'];
                $coupon_discount_by_provider += $item['coupon_discount_by_provider'];
                $campaign_discount_by_admin += $item['campaign_discount_by_admin'];
                $campaign_discount_by_provider += $item['campaign_discount_by_provider'];

                $admin_commission_with_cost += $item->admin_commission;

            }
            $booking->discount_by_admin = $discount_by_admin;
            $booking->discount_by_provider = $discount_by_provider;
            $booking->coupon_discount_by_admin = $coupon_discount_by_admin;
            $booking->coupon_discount_by_provider = $coupon_discount_by_provider;
            $booking->campaign_discount_by_admin = $campaign_discount_by_admin;
            $booking->campaign_discount_by_provider = $campaign_discount_by_provider;
            $booking->admin_commission_with_cost = $admin_commission_with_cost;

            $admin_commission_without_cost = $admin_commission_with_cost - ($discount_by_admin + $coupon_discount_by_admin + $campaign_discount_by_admin);
            $admin_net_income = $admin_commission_without_cost;
            $provider_net_income = $booking['total_booking_amount'] - $admin_commission_without_cost;
            $booking->admin_net_income = $admin_net_income;
            $booking->provider_net_income = $provider_net_income;
        }

        return (new FastExcel($bookings))->download(time() . '-business-earning-report.xlsx', function ($item) {
            return [
                'Booking ID' => $item->readable_id ?? '',
                'Booking Amount (' . currency_symbol() . ')' => with_decimal_point($item['total_booking_amount']),

                'Total Service Discount (' . currency_symbol() . ')' => with_decimal_point($item['total_discount_amount']),
                'Discount on service by admin (' . currency_symbol() . ')' => with_decimal_point($item['discount_by_admin']),
                'Discount on service by provider (' . currency_symbol() . ')' => with_decimal_point($item['discount_by_provider']),

                'Total Coupon Discount (' . currency_symbol() . ')' => with_decimal_point($item['total_coupon_discount_amount']),
                'Coupon Discount on service by admin (' . currency_symbol() . ')' => with_decimal_point($item['coupon_discount_by_admin']),
                'Coupon Discount on service by provider (' . currency_symbol() . ')' => with_decimal_point($item['coupon_discount_by_provider']),

                'Total Campaign Discount (' . currency_symbol() . ')' => with_decimal_point($item['total_campaign_discount_amount']),
                'Campaign Discount on service by admin (' . currency_symbol() . ')' => with_decimal_point($item['campaign_discount_by_admin']),
                'Campaign Discount on service by provider (' . currency_symbol() . ')' => with_decimal_point($item['campaign_discount_by_provider']),

                'Subtotal (' . currency_symbol() . ')' => with_decimal_point($item['total_booking_amount']),
                'VAT / Tax (' . currency_symbol() . ')' => with_decimal_point($item['total_tax_amount']),
                'Admin Commission (' . currency_symbol() . ')' => with_decimal_point($item['admin_commission_with_cost']),
                'Provider Net Income (' . currency_symbol() . ')' => with_decimal_point($item['provider_net_income']),
                'Admin Net Income (' . currency_symbol() . ')' => with_decimal_point($item['admin_net_income']),
            ];
        });
    }

    /**
     * @param $instance
     * @param $request
     * @return mixed
     */
    function filterQuery($instance, $request): mixed
    {
        return $instance
            ->when($request->has('zone_ids'), function ($query) use ($request) {
                $query->whereIn('zone_id', $request['zone_ids']);
            })
            ->when($request->has('category_ids'), function ($query) use ($request) {
                $query->whereIn('category_id', $request['category_ids']);
            })
            ->when($request->has('sub_category_ids'), function ($query) use ($request) {
                $query->whereIn('sub_category_id', $request['sub_category_ids']);
            })
            ->when($request->has('date_range') && $request['date_range'] == 'custom_date', function ($query) use ($request) {
                $query->whereBetween('created_at', [Carbon::parse($request['from'])->startOfDay(), Carbon::parse($request['to'])->endOfDay()]);
            })
            ->when($request->has('date_range') && $request['date_range'] != 'custom_date', function ($query) use ($request) {
                if ($request['date_range'] == 'this_week') {
                    $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);

                } elseif ($request['date_range'] == 'last_week') {
                    $query->whereBetween('created_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()]);

                } elseif ($request['date_range'] == 'this_month') {
                    $query->whereMonth('created_at', Carbon::now()->month);

                } elseif ($request['date_range'] == 'last_month') {
                    $query->whereMonth('created_at', Carbon::now()->subMonth()->month);

                } elseif ($request['date_range'] == 'last_15_days') {
                    $query->whereBetween('created_at', [Carbon::now()->subDay(15), Carbon::now()]);

                } elseif ($request['date_range'] == 'this_year') {
                    $query->whereYear('created_at', Carbon::now()->year);

                } elseif ($request['date_range'] == 'last_year') {
                    $query->whereYear('created_at', Carbon::now()->subYear()->year);

                } elseif ($request['date_range'] == 'last_6_month') {
                    $query->whereBetween('created_at', [Carbon::now()->subMonth(6), Carbon::now()]);

                } elseif ($request['date_range'] == 'this_year_1st_quarter') {
                    $query->whereBetween('created_at', [Carbon::now()->month(1)->startOfQuarter(), Carbon::now()->month(1)->endOfQuarter()]);

                } elseif ($request['date_range'] == 'this_year_2nd_quarter') {
                    $query->whereBetween('created_at', [Carbon::now()->month(4)->startOfQuarter(), Carbon::now()->month(4)->endOfQuarter()]);

                } elseif ($request['date_range'] == 'this_year_3rd_quarter') {
                    $query->whereBetween('created_at', [Carbon::now()->month(7)->startOfQuarter(), Carbon::now()->month(7)->endOfQuarter()]);

                } elseif ($request['date_range'] == 'this_year_4th_quarter') {
                    $query->whereBetween('created_at', [Carbon::now()->month(10)->startOfQuarter(), Carbon::now()->month(10)->endOfQuarter()]);
                }
            });
    }

    private function applyDateRangeConditions($query, $request): void
    {
        if ($request['date_range'] == 'custom_date') {
            $query->whereBetween('created_at', [Carbon::parse($request['from'])->startOfDay(), Carbon::parse($request['to'])->endOfDay()]);
        } else {
            switch ($request['date_range']) {
                case 'this_week':
                    $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    break;
                case 'last_week':
                    $query->whereBetween('created_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereMonth('created_at', Carbon::now()->month);
                    break;
                case 'last_month':
                    $query->whereMonth('created_at', Carbon::now()->subMonth()->month);
                    break;
                case 'last_15_days':
                    $query->whereBetween('created_at', [Carbon::now()->subDay(15), Carbon::now()]);
                    break;
                case 'this_year':
                    $query->whereYear('created_at', Carbon::now()->year);
                    break;
                case 'last_year':
                    $query->whereYear('created_at', Carbon::now()->subYear()->year);
                    break;
                case 'last_6_month':
                    $query->whereBetween('created_at', [Carbon::now()->subMonth(6), Carbon::now()]);
                    break;
                case 'this_year_1st_quarter':
                    $query->whereBetween('created_at', [Carbon::now()->month(1)->startOfQuarter(), Carbon::now()->month(1)->endOfQuarter()]);
                    break;
                case 'this_year_2nd_quarter':
                    $query->whereBetween('created_at', [Carbon::now()->month(4)->startOfQuarter(), Carbon::now()->month(4)->endOfQuarter()]);
                    break;
                case 'this_year_3rd_quarter':
                    $query->whereBetween('created_at', [Carbon::now()->month(7)->startOfQuarter(), Carbon::now()->month(7)->endOfQuarter()]);
                    break;
                case 'this_year_4th_quarter':
                    $query->whereBetween('created_at', [Carbon::now()->month(10)->startOfQuarter(), Carbon::now()->month(10)->endOfQuarter()]);
                    break;
            }
        }
    }


    public function subEarningDownload(Request $request)
    {
        $search = $request->input('search');
        $packagesSubscribers = $this->packageSubscriber
            ->where('trial_duration', '==', 0)
            ->when($search, function ($query, $search) {
                return $query->where('package_name', 'like', '%' . $search . '%');
            })
            ->latest()
            ->get();
        return (new FastExcel($packagesSubscribers))->download(time() . '-file.xlsx');
    }
    public function comEarningDownload(Request $request)
    {
        $search = $request->input('search');
        $commissionEarningList = $this->bookingDetailsAmount->where('admin_commission', '!=' , 0)
            ->when($search, function ($query, $search) {
                return $query->where('booking_id', 'like', '%' . $search . '%');
            })
            ->latest()->get();
        return (new FastExcel($commissionEarningList))->download(time() . '-file.xlsx');
    }
}
