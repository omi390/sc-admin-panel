<?php

namespace Modules\AdminModule\Http\Controllers\Web\Admin\Report\Business;

use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\BookingModule\Entities\Booking;
use Modules\BookingModule\Entities\BookingDetailsAmount;
use Modules\CategoryManagement\Entities\Category;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ServiceManagement\Entities\Service;
use Modules\TransactionModule\Entities\Account;
use Modules\TransactionModule\Entities\Transaction;
use Modules\UserManagement\Entities\User;
use Modules\ZoneManagement\Entities\Zone;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class OverviewReportController extends Controller
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
    use AuthorizesRequests;

    public function __construct(Zone $zone, Provider $provider, Category $categories, Service $service, Booking $booking, Account $account, User $user, Transaction $transaction, BookingDetailsAmount $bookingDetailsAmount)
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
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     * @throws AuthorizationException
     */
    public function getBusinessOverviewReport(Request $request)
    {
        $this->authorize('report_view');
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

        $queryParams = $request->only('search', 'zone_ids', 'category_ids', 'sub_category_ids', 'date_range');
        if ($request->date_range === 'custom_date') {
            $queryParams['from'] = $request->from;
            $queryParams['to'] = $request->to;
        }

        $date_range = $request['date_range'];
        if(is_null($date_range) || $date_range == 'all_time') {
            $deterministic = 'year';
        } elseif ($date_range == 'this_week' || $date_range == 'last_week') {
            $deterministic = 'week';
        } elseif ($date_range == 'this_month' || $date_range == 'last_month' || $date_range == 'last_15_days') {
            $deterministic = 'day';
        } elseif ($date_range == 'this_year' || $date_range == 'last_year' || $date_range == 'last_6_month' || $date_range == 'this_year_1st_quarter' || $date_range == 'this_year_2nd_quarter' || $date_range == 'this_year_3rd_quarter' || $date_range == 'this_year_4th_quarter') {
            $deterministic = 'month';
        } elseif($date_range == 'custom_date') {
            $from = Carbon::parse($request['from'])->startOfDay();
            $to = Carbon::parse($request['to'])->endOfDay();
            $diff = Carbon::parse($from)->diffInDays($to);

            if($diff <= 7) {
                $deterministic = 'week';
            } elseif ($diff <= 30) {
                $deterministic = 'day';
            } elseif ($diff <= 365) {
                $deterministic = 'month';
            } else {
                $deterministic = 'year';
            }
        }
        $group_by_deterministic = $deterministic=='week'?'day':$deterministic;

        $amounts = $this->bookingDetailsAmount
            ->whereHas('booking', function ($query) use ($request) {
                $query->ofBookingStatus('completed')
                    ->when($request->has('zone_ids'), function ($query) use($request) {
                        $query->whereIn('zone_id', $request['zone_ids']);
                    })
                    ->when($request->has('category_ids'), function ($query) use($request) {
                        $query->whereIn('category_id', $request['category_ids']);
                    })
                    ->when($request->has('sub_category_ids'), function ($query) use($request) {
                        $query->whereIn('sub_category_id', $request['sub_category_ids']);
                    })
                    ->when($request->has('date_range'), function ($query) use ($request) {
                        $this->applyDateRangeConditions($query, $request);
                    });
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

                    DB::raw($group_by_deterministic.'(created_at) '.$group_by_deterministic)
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
                $query->select(
                    DB::raw('sum(credit) as earning'),

                    DB::raw($group_by_deterministic.'(created_at) '.$group_by_deterministic)
                );
            })
            ->groupby($group_by_deterministic)
            ->get()->toArray();

        $bonus_amounts = $this->transaction
            ->where('trx_type', TRX_TYPE['add_fund_bonus'])
            ->when($request->has('date_range'), function ($query) use($request) {
                $this->applyDateRangeConditions($query, $request);
            })
            ->when(isset($group_by_deterministic), function ($query) use ($group_by_deterministic) {
                $query->select(
                    DB::raw('sum(credit) as bonus'),

                    DB::raw($group_by_deterministic.'(created_at) '.$group_by_deterministic)
                );
            })
            ->groupby($group_by_deterministic)
            ->get()->toArray();

        $all_expenses_and_earnings = [];

        if (empty($amounts) && (!empty($earningAmount) || !empty($bonus_amounts))) {
            foreach ($earningAmount as $key => $earning) {
                $all_expenses_and_earnings[$key] = $earning;
                if (!array_key_exists('bonus', $all_expenses_and_earnings[$key])) {
                    $all_expenses_and_earnings[$key]['bonus'] = 0;
                }
            }

            foreach ($bonus_amounts as $key => $bonus) {
                if (isset($all_expenses_and_earnings[$key])) {
                    $all_expenses_and_earnings[$key] = array_merge($all_expenses_and_earnings[$key], $bonus);
                } else {
                    $all_expenses_and_earnings[$key] = $bonus;
                    if (!array_key_exists('earning', $all_expenses_and_earnings[$key])) {
                        $all_expenses_and_earnings[$key]['earning'] = 0;
                    }
                }
            }
        } else {
            foreach ($amounts as $index => $amount) {
                foreach ($earningAmount as $key => $earning) {
                    if ($amount[$group_by_deterministic] == $earning[$group_by_deterministic]) {
                        $all_expenses_and_earnings[$index] = array_merge($amount, $earning);
                    }
                }
                foreach ($bonus_amounts as $bonus) {
                    if ($amount[$group_by_deterministic] == $bonus[$group_by_deterministic]) {
                        $all_expenses_and_earnings[$index] = isset($all_expenses_and_earnings[$index]) ? array_merge($all_expenses_and_earnings[$index], $bonus) : array_merge($amount, $bonus);
                    }
                }

                if (!isset($all_expenses_and_earnings[$index])) {
                    $all_expenses_and_earnings[$index] = $amount;
                }

                if (!array_key_exists('earning', $all_expenses_and_earnings[$index])) {
                    $all_expenses_and_earnings[$index]['earning'] = 0;
                }

                if (!array_key_exists('bonus', $all_expenses_and_earnings[$index])) {
                    $all_expenses_and_earnings[$index]['bonus'] = 0;
                }
            }
        }

        $chart_data = ['earnings'=>array(), 'expenses'=>array(), 'timeline'=>array()];
        $all_expenses = ['discount' => 0, 'coupon' => 0, 'campaign' => 0, 'bonus' => 0];

        if($deterministic == 'month') {
            $months = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
            foreach ($months as $month) {
                $found = 0;
                $chart_data['timeline'][] = $month;

                foreach ($all_expenses_and_earnings as $item) {
                    if ($item['month'] == $month) {
                        $admin_commission = data_get($item, 'admin_commission', 0);
                        $earning = data_get($item, 'earning', 0);

                        $chart_data['earnings'][] = with_decimal_point($admin_commission + $earning);
                        $chart_data['expenses'][] = with_decimal_point(
                            data_get($item, 'discount_by_admin', 0) +
                            data_get($item, 'coupon_discount_by_admin', 0) +
                            data_get($item, 'campaign_discount_by_admin', 0) +
                            data_get($item, 'bonus', 0)
                        );

                        $found = 1;

                        $all_expenses['discount'] += data_get($item, 'discount_by_admin', 0);
                        $all_expenses['coupon'] += data_get($item, 'coupon_discount_by_admin', 0);
                        $all_expenses['campaign'] += data_get($item, 'campaign_discount_by_admin', 0);
                        $all_expenses['bonus'] += data_get($item, 'bonus', 0);
                    }
                }

                if (!$found) {
                    $chart_data['earnings'][] = with_decimal_point(0);
                    $chart_data['expenses'][] = with_decimal_point(0);
                }
            }

        }
        elseif ($deterministic == 'year') {
            foreach ($all_expenses_and_earnings as $item) {
                $admin_commission = data_get($item, 'admin_commission', 0); // Use data_get with a default value

                $chart_data['earnings'][] = with_decimal_point($admin_commission + data_get($item, 'earning', 0));
                $chart_data['expenses'][] = with_decimal_point(
                    data_get($item, 'discount_by_admin', 0) +
                    data_get($item, 'coupon_discount_by_admin', 0) +
                    data_get($item, 'campaign_discount_by_admin', 0) +
                    data_get($item, 'bonus', 0)
                );
                $chart_data['timeline'][] = $item[$deterministic];

                $all_expenses['discount'] += data_get($item, 'discount_by_admin', 0);
                $all_expenses['coupon'] += data_get($item, 'coupon_discount_by_admin', 0);
                $all_expenses['campaign'] += data_get($item, 'campaign_discount_by_admin', 0);
                $all_expenses['bonus'] += data_get($item, 'bonus', 0);
            }
        }
        elseif ($deterministic == 'day') {
            if ($date_range == 'this_month') {
                $to = Carbon::now()->lastOfMonth();
            } elseif ($date_range == 'last_month') {
                $to = Carbon::now()->subMonth()->endOfMonth();
            } elseif ($date_range == 'last_15_days') {
                $to = Carbon::now();
            }

            $number = date('d',strtotime($to));

            for ($i = 1; $i <= $number; $i++) {
                $found=0;
                $chart_data['timeline'][] = $i;
                foreach ($all_expenses_and_earnings as $item) {
                    if ($item['day'] == $i) {
                        $admin_commission = data_get($item, 'admin_commission', 0);
                        $earning = data_get($item, 'earning', 0);

                        $chart_data['earnings'][] = with_decimal_point($admin_commission + $earning);
                        $chart_data['expenses'][] = with_decimal_point(
                            data_get($item, 'discount_by_admin', 0) +
                            data_get($item, 'coupon_discount_by_admin', 0) +
                            data_get($item, 'campaign_discount_by_admin', 0) +
                            data_get($item, 'bonus', 0)
                        );

                        $found = 1;

                        $all_expenses['discount'] += data_get($item, 'discount_by_admin', 0);
                        $all_expenses['coupon'] += data_get($item, 'coupon_discount_by_admin', 0);
                        $all_expenses['campaign'] += data_get($item, 'campaign_discount_by_admin', 0);
                        $all_expenses['bonus'] += data_get($item, 'bonus', 0);
                    }
                }
                if(!$found){
                    $chart_data['earnings'][] = with_decimal_point(0);
                    $chart_data['expenses'][] = with_decimal_point(0);
                }
            }
        }
        elseif ($deterministic == 'week') {
            if ($date_range == 'this_week') {
                $from = Carbon::now()->startOfWeek();
                $to = Carbon::now()->endOfWeek();
            } elseif ($date_range == 'last_week') {
                $from = Carbon::now()->subWeek()->startOfWeek();
                $to = Carbon::now()->subWeek()->endOfWeek();
            }

            for ($i = (int)$from->format('d'); $i <= (int)$to->format('d'); $i++) {
                $found=0;
                $chart_data['timeline'][] = $i;
                foreach ($all_expenses_and_earnings as $item) {
                    if ($item['day'] == $i) {
                        $admin_commission = data_get($item, 'admin_commission', 0);
                        $earning = data_get($item, 'earning', 0);

                        $chart_data['earnings'][] = with_decimal_point($admin_commission + $earning);
                        $chart_data['expenses'][] = with_decimal_point(
                            data_get($item, 'discount_by_admin', 0) +
                            data_get($item, 'coupon_discount_by_admin', 0) +
                            data_get($item, 'campaign_discount_by_admin', 0) +
                            data_get($item, 'bonus', 0)
                        );
                        $found = 1;

                        $all_expenses['discount'] += data_get($item, 'discount_by_admin', 0);
                        $all_expenses['coupon'] += data_get($item, 'coupon_discount_by_admin', 0);
                        $all_expenses['campaign'] += data_get($item, 'campaign_discount_by_admin', 0);
                        $all_expenses['bonus'] += data_get($item, 'bonus', 0);
                    }
                }
                if(!$found){
                    $chart_data['earnings'][] = with_decimal_point(0);
                    $chart_data['expenses'][] = with_decimal_point(0);
                }
            }
        }

        return view('adminmodule::admin.report.business.overview', compact('zones', 'categories', 'sub_categories', 'amounts', 'chart_data', 'all_expenses', 'deterministic', 'queryParams'));
    }

    public function getBusinessOverviewReportDownload(Request $request): StreamedResponse|string
    {
        $this->authorize('report_export');
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

        $date_range = $request['date_range'];
        if(is_null($date_range) || $date_range == 'all_time') {
            $deterministic = 'year';
        } elseif ($date_range == 'this_week' || $date_range == 'last_week') {
            $deterministic = 'week';
        } elseif ($date_range == 'this_month' || $date_range == 'last_month' || $date_range == 'last_15_days') {
            $deterministic = 'day';
        } elseif ($date_range == 'this_year' || $date_range == 'last_year' || $date_range == 'last_6_month' || $date_range == 'this_year_1st_quarter' || $date_range == 'this_year_2nd_quarter' || $date_range == 'this_year_3rd_quarter' || $date_range == 'this_year_4th_quarter') {
            $deterministic = 'month';
        } elseif($date_range == 'custom_date') {
            $from = Carbon::parse($request['from'])->startOfDay();
            $to = Carbon::parse($request['to'])->endOfDay();
            $diff = Carbon::parse($from)->diffInDays($to);

            if($diff <= 7) {
                $deterministic = 'week';
            } elseif ($diff <= 30) {
                $deterministic = 'day';
            } elseif ($diff <= 365) {
                $deterministic = 'month';
            } else {
                $deterministic = 'year';
            }
        }
        $group_by_deterministic = $deterministic=='week'?'day':$deterministic;

        $amounts = $this->bookingDetailsAmount
            ->whereHas('booking', function ($query) use ($request) {
                $query->ofBookingStatus('completed')
                    ->when($request->has('zone_ids'), function ($query) use($request) {
                        $query->whereIn('zone_id', $request['zone_ids']);
                    })
                    ->when($request->has('category_ids'), function ($query) use($request) {
                        $query->whereIn('category_id', $request['category_ids']);
                    })
                    ->when($request->has('sub_category_ids'), function ($query) use($request) {
                        $query->whereIn('sub_category_id', $request['sub_category_ids']);
                    })
                    ->when($request->has('date_range'), function ($query) use ($request) {
                        $this->applyDateRangeConditions($query, $request);
                    });
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

                    DB::raw($group_by_deterministic.'(created_at) '.$group_by_deterministic)
                );
            })
            ->groupby($group_by_deterministic)
            ->get();

        foreach ($amounts as $amount) {
            $total_earning = $amount['admin_commission'];
            $total_expense = $amount['discount_by_admin'] + $amount['coupon_discount_by_admin'] + $amount['campaign_discount_by_admin'];

            $net_profit = $total_earning-$total_expense;
            $net_profit_rate = $total_earning!=0 ? ($net_profit*100)/$total_earning : $net_profit*100;

            $amount->total_expense = $total_expense;
            $amount->net_profit = $net_profit;
            $amount->net_profit_rate = $net_profit_rate;
        }

        return (new FastExcel($amounts))->download(time().'-business-overview-report.xlsx', function ($item) use ($deterministic) {
            return [
                $deterministic => $item[$deterministic]??'',
                'Commission Earnings ('.currency_symbol().')' => with_decimal_point($item['admin_commission']),
                'Total Expenses ('.currency_symbol().')' => with_decimal_point($item['total_expense']),
                'Net Profit ('.currency_symbol().')' => with_decimal_point($item['net_profit']),
                'Net Profit Rate (%)' => with_decimal_point($item['net_profit_rate']),
            ];
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
}
