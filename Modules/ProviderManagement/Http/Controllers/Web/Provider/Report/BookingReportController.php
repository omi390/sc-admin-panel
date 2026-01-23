<?php

namespace Modules\ProviderManagement\Http\Controllers\Web\Provider\Report;

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
use OpenSpout\Common\Exception\InvalidArgumentException;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Common\Exception\UnsupportedTypeException;
use OpenSpout\Writer\Exception\WriterNotOpenedException;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use function pagination_limit;
use function view;
use function with_currency_symbol;

class BookingReportController extends Controller
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
    public function getBookingReport(Request $request): Renderable
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
            'booking_status' => 'in:' . implode(',', array_column(BOOKING_STATUSES, 'key')) . ',all',
        ]);

        //Dropdown data
        $zones = $this->zone->ofStatus(1)->select('id', 'name')->get();
        $categories = $this->categories->ofType('main')->select('id', 'name')->get();
        $subCategories = $this->categories->ofType('sub')->select('id', 'name')->get();

        //params
        $queryParams = ['booking_status' => $request->input('booking_status', 'pending')];
        $queryParams += $request->only(['search', 'booking_status', 'zone_ids', 'category_ids',  'sub_category_ids', 'date_range']);
        if ($request['date_range'] === 'custom_date') {
            $queryParams['from'] = $request['from'];
            $queryParams['to'] = $request['to'];
        }

        //** Table Data **
        $filteredBookings = self::filterQuery($this->booking, $request)
            ->with(['provider.owner', 'customer' => function ($query) {
                $query->withTrashed();
            }])
            ->when($request->has('booking_status') && $request['booking_status'] != 'all' , function ($query) use($request) {
                $query->where('booking_status', $request['booking_status']);
            })
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->where('readable_id', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->latest()->paginate(pagination_limit())
            ->appends($queryParams);

        //** Card Data **
        $bookingsForAmount = self::filterQuery($this->booking, $request)
            ->with(['customer', 'provider.owner'])
            ->whereIn('booking_status', ['accepted', 'ongoing', 'completed', 'canceled'])
            ->get();

        $bookingsCount = [];
        $bookingsCount['total_bookings'] = $bookingsForAmount->count();
        $bookingsCount['accepted'] = $bookingsForAmount->where('booking_status', 'accepted')->count();
        $bookingsCount['ongoing'] = $bookingsForAmount->where('booking_status', 'ongoing')->count();
        $bookingsCount['completed'] = $bookingsForAmount->where('booking_status', 'completed')->count();
        $bookingsCount['canceled'] = $bookingsForAmount->where('booking_status', 'canceled')->count();

        $bookingAmount = [];
        $bookingAmount['total_booking_amount'] = $bookingsForAmount->sum('total_booking_amount');
        $bookingAmount['total_paid_booking_amount'] = $bookingsForAmount->where('payment_method', '!=', 'cash_after_service')->where('booking_status', 'completed')->sum('total_booking_amount');
        $bookingAmount['total_unpaid_booking_amount'] = $bookingsForAmount->where('payment_method', '!=', 'cash_after_service')->where('booking_status', '!=', 'completed')->sum('total_booking_amount');

        //** Chart Data **

        //deterministic
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

        $amounts = $this->booking_details_amount
            ->whereHas('booking', function ($query) use ($request) {
                self::filterQuery($query, $request)->whereIn('booking_status', ['accepted', 'ongoing', 'completed', 'canceled']);
            })
            ->when(isset($groupByDeterministic), function ($query) use ($groupByDeterministic) {
                $query->select(
                    DB::raw('sum(admin_commission) as admin_commission'),

                    DB::raw($groupByDeterministic.'(created_at) '.$groupByDeterministic)
                );
            })
            ->groupby($groupByDeterministic)
            ->get()->toArray();

        $bookings = self::filterQuery($this->booking, $request)
            ->whereIn('booking_status', ['accepted', 'ongoing', 'completed', 'canceled'])
            ->when(isset($groupByDeterministic), function ($query) use ($groupByDeterministic) {
                $query->select(
                    DB::raw('sum(total_booking_amount) as total_booking_amount'),
                    DB::raw('sum(total_tax_amount) as total_tax_amount'),

                    DB::raw($groupByDeterministic.'(created_at) '.$groupByDeterministic)
                );
            })
            ->groupby($groupByDeterministic)
            ->get()->toArray();

        $chartData = ['booking_amount'=>array(), 'tax_amount'=>array(), 'admin_commission'=>array(), 'timeline'=>array()];
        //data filter for deterministic
        if($deterministic == 'month') {
            $months = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
            foreach ($months as $month) {
                $found=0;
                $chartData['timeline'][] = $month;
                foreach ($bookings as $key=>$item) {
                    if ($item['month'] == $month) {
                        $chartData['booking_amount'][] = $item['total_booking_amount'];
                        $chartData['tax_amount'][] = $item['total_tax_amount'];

                        $chartData['admin_commission'][] = $amounts[$key]['admin_commission']??0;
                        $found=1;
                    }
                }
                if(!$found){
                    $chartData['booking_amount'][] = 0;
                    $chartData['tax_amount'][] = 0;
                    $chartData['admin_commission'][] = 0;
                }
            }

        }
        elseif ($deterministic == 'year') {
            foreach ($bookings as $key=>$item) {
                $chartData['booking_amount'][] = $item['total_booking_amount'];
                $chartData['tax_amount'][] = $item['total_tax_amount'];
                $chartData['timeline'][] = $item[$deterministic];

                $chartData['admin_commission'][] = $amounts[$key]['admin_commission']??0;
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
                foreach ($bookings as $key=>$item) {
                    if ($item['day'] == $i) {
                        $chartData['booking_amount'][] = $item['total_booking_amount'];
                        $chartData['tax_amount'][] = $item['total_tax_amount'];

                        $chartData['admin_commission'][] = $amounts[$key]['admin_commission']??0;
                        $found=1;
                    }
                }
                if(!$found){
                    $chartData['booking_amount'][] = 0;
                    $chartData['tax_amount'][] = 0;
                    $chartData['admin_commission'][] = 0;
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
                foreach ($bookings as $key=>$item) {
                    if ($item['day'] == $i) {
                        $chartData['booking_amount'][] = $item['total_booking_amount'];
                        $chartData['tax_amount'][] = $item['total_tax_amount'];

                        $chartData['admin_commission'][] = $amounts[$key]['admin_commission']??0;
                        $found=1;
                    }
                }
                if(!$found) {
                    $chartData['booking_amount'][] = 0;
                    $chartData['tax_amount'][] = 0;
                    $chartData['admin_commission'][] = 0;
                }
            }
        }

        return view('providermanagement::provider.report.booking', compact('zones', 'categories', 'subCategories', 'filteredBookings', 'bookingsCount', 'bookingAmount', 'chartData', 'queryParams'));
    }


    /**
     * @param Request $request
     * @return string|StreamedResponse
     * @throws IOException
     * @throws InvalidArgumentException
     * @throws UnsupportedTypeException
     * @throws WriterNotOpenedException
     */
    public function getBookingReportDownload(Request $request): string|StreamedResponse
    {
        Validator::make($request->all(), [
            'zone_ids' => 'array',
            'zone_ids.*' => 'uuid',
            'sub_category_ids' => 'array',
            'sub_category_ids.*' => 'uuid',
            'date_range' => 'in:all_time, this_week, last_week, this_month, last_month, last_15_days, this_year, last_year, last_6_month, this_year_1st_quarter, this_year_2nd_quarter, this_year_3rd_quarter, this_year_4th_quarter, custom_date',
            'from' => $request['date_range'] == 'custom_date' ? 'required' : '',
            'to' => $request['date_range'] == 'custom_date' ? 'required' : '',
            'booking_status' => 'in:' . implode(',', array_column(BOOKING_STATUSES, 'key')) . ',all',
        ]);


        $filteredBookings = self::filterQuery($this->booking, $request)
            ->with(['customer', 'provider.owner', ])
            ->when($request->has('booking_status') && $request['booking_status'] != 'all', function ($query) use($request) {
                $query->where('booking_status', $request['booking_status']);

            })
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->where('readable_id', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->latest()->get();

        return (new FastExcel($filteredBookings))->download(time().'-booking-report.xlsx', function ($booking) {
            return [
                'Booking ID' => $booking->readable_id,
                'Customer Name' => isset($booking->customer) ? ($booking->customer->first_name . ' ' . $booking->customer->last_name) : '',
                'Customer Phone' => isset($booking->customer) ? ($booking->customer->phone??'') : '',
                'Customer Email' => isset($booking->customer) ? ($booking->customer->email??'') : '',
                'Provider Name' => isset($booking->provider) && isset($booking->provider->owner) ? ($booking->provider->owner->first_name . ' ' . $booking->provider->owner->last_name) : '',
                'Provider Phone' => isset($booking->provider) && isset($booking->provider->owner) ? ($booking->provider->owner->phone??'') : '',
                'Provider Email' => isset($booking->provider) && isset($booking->provider->owner) ? ($booking->provider->owner->email??'') : '',

                'Booking Amount' => with_currency_symbol($booking['total_booking_amount']),
                'Service Discount' => with_currency_symbol($booking['total_discount_amount']),
                'Coupon Discount' => with_currency_symbol($booking['total_coupon_amount']),
                'VAT / Tax' => with_currency_symbol($booking['total_tax_amount']),
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
