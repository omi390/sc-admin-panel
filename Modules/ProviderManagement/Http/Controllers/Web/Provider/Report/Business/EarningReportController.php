<?php

namespace Modules\ProviderManagement\Http\Controllers\Web\Provider\Report\Business;

use Carbon\Carbon;
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

    public function __construct(Zone $zone, Provider $provider, Category $categories, Service $service, Booking $booking, Account $account, User $user, Transaction $transaction, BookingDetailsAmount $booking_details_amount)
    {
        $this->zone = $zone;
        $this->provider = $provider;
        $this->categories = $categories;
        $this->booking = $booking;

        $this->service = $service;
        $this->account = $account;
        $this->user = $user;
        $this->transaction = $transaction;
        $this->booking_details_amount = $booking_details_amount;
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

        //Dropdown data
        $zones = $this->zone->ofStatus(1)->select('id', 'name')->get();
        $categories = $this->categories->ofType('main')->select('id', 'name')->get();
        $subCategories = $this->categories->ofType('sub')->select('id', 'name')->get();

        //params
        $search = $request['search'];
        $queryParams = ['search' => $search];
        if($request->has('zone_ids')) {
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


        $dateRange = $request['date_range'];
        if(is_null($dateRange) || $dateRange == 'all_time') {
            $deterministic = 'year';
        } elseif ($dateRange == 'this_week' || $dateRange == 'last_week') {
            $deterministic = 'week';
        } elseif ($dateRange == 'this_month' || $dateRange == 'last_month' || $dateRange == 'last_15_days') {
            $deterministic = 'day';
        } elseif ($dateRange == 'this_year' || $dateRange == 'last_year' || $dateRange == 'last_6_month' || $dateRange == 'this_year_1st_quarter' || $dateRange == 'this_year_2nd_quarter' || $dateRange == 'this_year_3rd_quarter' || $dateRange == 'this_year_4th_quarter') {
            $deterministic = 'month';
        } elseif($dateRange == 'custom_date') {
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
        $groupByDeterministic = $deterministic=='week'?'day':$deterministic;

        // Data for chart
        $amounts = $this->booking_details_amount
            ->whereHas('booking', function ($query) use ($request) {
                self::filterQuery($query, $request)->ofBookingStatus('completed');
            })
            ->when(isset($groupByDeterministic), function ($query) use ($groupByDeterministic) {
                $query->select(
                    DB::raw('sum(service_unit_cost) as service_unit_cost'),
                    DB::raw('sum(service_quantity) as service_quantity'),
                    DB::raw('sum(service_tax) as service_tax'),
                    DB::raw('sum(discount_by_admin) as discount_by_admin'),
                    DB::raw('sum(discount_by_provider) as discount_by_provider'),
                    DB::raw('sum(coupon_discount_by_admin) as coupon_discount_by_admin'),
                    DB::raw('sum(coupon_discount_by_provider) as coupon_discount_by_provider'),
                    DB::raw('sum(campaign_discount_by_admin) as campaign_discount_by_admin'),
                    DB::raw('sum(campaign_discount_by_provider) as campaign_discount_by_provider'),
                    DB::raw('sum(admin_commission) as admin_commission'),
                    DB::raw('sum(provider_earning) as provider_earning'),

                    DB::raw($groupByDeterministic.'(created_at) '.$groupByDeterministic)
                );
            })
            ->groupby($groupByDeterministic)
            ->get()->toArray();

        $chartData = ['net_profit'=>array(), 'total_earning'=>array(), 'total_expense'=>array(), 'timeline'=>array()];
        //data filter for deterministic
        if($deterministic == 'month') {
            $months = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
            foreach ($months as $month) {
                $found=0;
                $chartData['timeline'][] = $month;
                foreach ($amounts as $key=>$item) {
                    if ($item['month'] == $month) {
                        $chartData['net_profit'][] = with_decimal_point($item['provider_earning'] - ($item['discount_by_provider'] + $item['coupon_discount_by_provider'] + $item['campaign_discount_by_provider']));
                        $chartData['total_earning'][] = with_decimal_point($item['provider_earning']);
                        $chartData['total_expense'][] = with_decimal_point($item['discount_by_provider'] + $item['coupon_discount_by_provider'] + $item['campaign_discount_by_provider']);
                        $found=1;
                    }
                }
                if(!$found){
                    $chartData['net_profit'][] = with_decimal_point(0);
                    $chartData['total_earning'][] = with_decimal_point(0);
                    $chartData['total_expense'][] = with_decimal_point(0);
                }
            }

        }
        elseif ($deterministic == 'year') {
            foreach ($amounts as $key=>$item) {
                $chartData['net_profit'][] = with_decimal_point($item['provider_earning'] - ($item['discount_by_provider'] + $item['coupon_discount_by_provider'] + $item['campaign_discount_by_provider']));
                $chartData['total_earning'][] = with_decimal_point($item['provider_earning']);
                $chartData['total_expense'][] = with_decimal_point($item['discount_by_provider'] + $item['coupon_discount_by_provider'] + $item['campaign_discount_by_provider']);
                $chartData['timeline'][] = $item[$deterministic];
            }
        }
        elseif ($deterministic == 'day') {
            if ($dateRange == 'this_month') {
                $to = Carbon::now()->lastOfMonth();
            } elseif ($dateRange == 'last_month') {
                $to = Carbon::now()->subMonth()->endOfMonth();
            } elseif ($dateRange == 'last_15_days') {
                $to = Carbon::now();
            }

            $number = date('d',strtotime($to));

            for ($i = 1; $i <= $number; $i++) {
                $found=0;
                $chartData['timeline'][] = $i;
                foreach ($amounts as $key=>$item) {
                    if ($item['day'] == $i) {
                        $chartData['net_profit'][] = with_decimal_point($item['provider_earning'] - ($item['discount_by_provider'] + $item['coupon_discount_by_provider'] + $item['campaign_discount_by_provider']));
                        $chartData['total_earning'][] = with_decimal_point($item['provider_earning']);
                        $chartData['total_expense'][] = with_decimal_point($item['discount_by_provider'] + $item['coupon_discount_by_provider'] + $item['campaign_discount_by_provider']);
                        $found=1;
                    }
                }
                if(!$found){
                    $chartData['net_profit'][] = with_decimal_point(0);
                    $chartData['total_earning'][] = with_decimal_point(0);
                    $chartData['total_expense'][] = with_decimal_point(0);
                }
            }
        }
        elseif ($deterministic == 'week') {
            if ($dateRange == 'this_week') {
                $from = Carbon::now()->startOfWeek();
                $to = Carbon::now()->endOfWeek();
            } elseif ($dateRange == 'last_week') {
                $from = Carbon::now()->subWeek()->startOfWeek();
                $to = Carbon::now()->subWeek()->endOfWeek();
            }

            for ($i = (int)$from->format('d'); $i <= (int)$to->format('d'); $i++) {
                $found=0;
                $chartData['timeline'][] = $i;
                foreach ($amounts as $key=>$item) {
                    if ($item['day'] == $i) {
                        $chartData['net_profit'][] = with_decimal_point($item['provider_earning'] - ($item['discount_by_provider'] + $item['coupon_discount_by_provider'] + $item['campaign_discount_by_provider']));
                        $chartData['total_earning'][] = with_decimal_point($item['provider_earning']);
                        $chartData['total_expense'][] = with_decimal_point($item['discount_by_provider'] + $item['coupon_discount_by_provider'] + $item['campaign_discount_by_provider']);
                        $found=1;
                    }
                }
                if(!$found){
                    $chartData['net_profit'][] = with_decimal_point(0);
                    $chartData['total_earning'][] = with_decimal_point(0);
                    $chartData['total_expense'][] = with_decimal_point(0);
                }
            }
        }

        //Data for booking list
        $bookings = self::filterQuery($this->booking, $request)
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
            ->paginate(pagination_limit());

        return view('providermanagement::provider.report.business.earning', compact('zones', 'categories', 'subCategories', 'bookings', 'chartData', 'queryParams'));
    }

    public function getBusinessEarningReportDownload(Request $request)
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

        //Data for booking list
        $bookings = self::filterQuery($this->booking, $request)
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

            //promotional cost
            $discountByAdmin = 0;
            $discountByProvider = 0;
            $couponDiscountByAdmin = 0;
            $couponDiscountByProvider = 0;
            $campaignDiscountByAdmin = 0;
            $campaignDiscountByProvider = 0;
            $adminCommissionWithCost = 0;

            foreach ($booking->details_amounts as $key=>$item) {
                //promotional
                $discountByAdmin += $item['discount_by_admin'];
                $discountByProvider += $item['discount_by_provider'];
                $couponDiscountByAdmin += $item['coupon_discount_by_admin'];
                $couponDiscountByProvider += $item['coupon_discount_by_provider'];
                $campaignDiscountByAdmin += $item['campaign_discount_by_admin'];
                $campaignDiscountByProvider += $item['campaign_discount_by_provider'];

                $adminCommissionWithCost += $item->admin_commission;

            }
            //set in the booking object
            $booking->discount_by_admin = $discountByAdmin;
            $booking->discount_by_provider = $discountByProvider;
            $booking->coupon_discount_by_admin = $couponDiscountByAdmin;
            $booking->coupon_discount_by_provider = $couponDiscountByProvider;
            $booking->campaign_discount_by_admin = $campaignDiscountByAdmin;
            $booking->campaign_discount_by_provider = $campaignDiscountByProvider;
            $booking->admin_commission_with_cost = $adminCommissionWithCost;

            //booking table
            $adminCommissionWithoutCost = $adminCommissionWithCost - ($discountByAdmin + $couponDiscountByAdmin + $campaignDiscountByAdmin);
            $adminNetIncome = $adminCommissionWithoutCost;
            $providerNetIncome = $booking['total_booking_amount'] - $adminCommissionWithoutCost;
            //set in the booking object
            $booking->admin_net_income = $adminNetIncome;
            $booking->provider_net_income = $providerNetIncome;
        }

        return (new FastExcel($bookings))->download(time().'-business-earning-report.xlsx', function ($item) {
            return [
                'Booking ID' => $item->readable_id??'',
                'Booking Amount ('.currency_symbol().')' => with_decimal_point($item['total_booking_amount']),

                'Total Service Discount ('.currency_symbol().')' => with_decimal_point($item['total_discount_amount']),
                'Discount on service by provider ('.currency_symbol().')' => with_decimal_point($item['discount_by_provider']),

                'Total Coupon Discount ('.currency_symbol().')' => with_decimal_point($item['total_coupon_discount_amount']),
                'Coupon Discount on service by provider ('.currency_symbol().')' => with_decimal_point($item['coupon_discount_by_provider']),

                'Total Campaign Discount ('.currency_symbol().')' => with_decimal_point($item['total_campaign_discount_amount']),
                'Campaign Discount on service by provider ('.currency_symbol().')' => with_decimal_point($item['campaign_discount_by_provider']),

                'Subtotal ('.currency_symbol().')' => with_decimal_point($item['total_booking_amount']),
                'VAT / Tax ('.currency_symbol().')' => with_decimal_point($item['total_tax_amount']),
                'Admin Commission ('.currency_symbol().')' => with_decimal_point($item['admin_commission_with_cost']),
                'Provider Net Income ('.currency_symbol().')' => with_decimal_point($item['provider_net_income']),
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
            ->where('provider_id', $request->user()->provider->id)
            ->when($request->has('zone_ids'), function ($query) use($request) {
                $query->whereIn('zone_id', $request['zone_ids']);
            })
            ->when($request->has('category_ids'), function ($query) use($request) {
                $query->whereIn('category_id', $request['category_ids']);
            })
            ->when($request->has('sub_category_ids'), function ($query) use($request) {
                $query->whereIn('sub_category_id', $request['sub_category_ids']);
            })
            ->when($request->has('date_range') && $request['date_range'] == 'custom_date', function ($query) use($request) {
                $query->whereBetween('created_at', [Carbon::parse($request['from'])->startOfDay(), Carbon::parse($request['to'])->endOfDay()]);
            })
            ->when($request->has('date_range') && $request['date_range'] != 'custom_date', function ($query) use($request) {
                //DATE RANGE
                if($request['date_range'] == 'this_week') {
                    //this week
                    $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);

                } elseif ($request['date_range'] == 'last_week') {
                    //last week
                    $query->whereBetween('created_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()]);

                } elseif ($request['date_range'] == 'this_month') {
                    //this month
                    $query->whereMonth('created_at', Carbon::now()->month);

                } elseif ($request['date_range'] == 'last_month') {
                    //last month
                    $query->whereMonth('created_at', Carbon::now()->subMonth()->month);

                } elseif ($request['date_range'] == 'last_15_days') {
                    //last 15 days
                    $query->whereBetween('created_at', [Carbon::now()->subDay(15), Carbon::now()]);

                } elseif ($request['date_range'] == 'this_year') {
                    //this year
                    $query->whereYear('created_at', Carbon::now()->year);

                } elseif ($request['date_range'] == 'last_year') {
                    //last year
                    $query->whereYear('created_at', Carbon::now()->subYear()->year);

                } elseif ($request['date_range'] == 'last_6_month') {
                    //last 6month
                    $query->whereBetween('created_at', [Carbon::now()->subMonth(6), Carbon::now()]);

                } elseif ($request['date_range'] == 'this_year_1st_quarter') {
                    //this year 1st quarter
                    $query->whereBetween('created_at', [Carbon::now()->month(1)->startOfQuarter(), Carbon::now()->month(1)->endOfQuarter()]);

                } elseif ($request['date_range'] == 'this_year_2nd_quarter') {
                    //this year 2nd quarter
                    $query->whereBetween('created_at', [Carbon::now()->month(4)->startOfQuarter(), Carbon::now()->month(4)->endOfQuarter()]);

                } elseif ($request['date_range'] == 'this_year_3rd_quarter') {
                    //this year 3rd quarter
                    $query->whereBetween('created_at', [Carbon::now()->month(7)->startOfQuarter(), Carbon::now()->month(7)->endOfQuarter()]);

                } elseif ($request['date_range'] == 'this_year_4th_quarter') {
                    //this year 4th quarter
                    $query->whereBetween('created_at', [Carbon::now()->month(10)->startOfQuarter(), Carbon::now()->month(10)->endOfQuarter()]);
                }
            });
    }
}
