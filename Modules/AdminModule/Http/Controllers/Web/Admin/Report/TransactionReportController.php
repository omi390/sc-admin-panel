<?php

namespace Modules\AdminModule\Http\Controllers\Web\Admin\Report;

use Auth;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
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
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use function pagination_limit;
use function view;
use function with_currency_symbol;
use Illuminate\Support\Facades\DB;

class TransactionReportController extends Controller
{
    protected Zone $zone;
    protected Provider $provider;
    protected Category $categories;
    protected Booking $booking;

    protected Account $account;
    protected Service $service;
    protected User $user;
    protected Transaction $transaction;
    use AuthorizesRequests;


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
     * @throws AuthorizationException
     */
    public function getTransactionReport(Request $request): Renderable
    {
        $this->authorize('report_view');
        Validator::make($request->all(), [
            'zone_ids' => 'array',
            'zone_ids.*' => 'uuid',
            'provider_ids' => 'array',
            'provider_ids.*' => 'uuid',
            'date_range' => 'in:all_time, this_week, last_week, this_month, last_month, last_15_days, this_year, last_year, last_6_month, this_year_1st_quarter, this_year_2nd_quarter, this_year_3rd_quarter, this_year_4th_quarter, custom_date',
            'from' => $request['date_range'] == 'custom_date' ? 'required' : '',
            'to' => $request['date_range'] == 'custom_date' ? 'required' : '',
            'filter_by' => 'in:collect_cash,payment,withdraw,commission,all',

            'transaction_type' => 'in:all,debit,credit'
        ]);

        $zones = $this->zone->select('id', 'name')->get();
        $providers = $this->provider->ofApproval(1)->select('id', 'company_name', 'company_phone')->get();

        //params
        $queryParams = ['transaction_type' => $request->input('transaction_type', 'all')];
        $queryParams += $request->only(['search', 'zone_ids', 'provider_ids', 'date_range', 'filter_by']);
        if ($request['date_range'] === 'custom_date') {
            $queryParams['from'] = $request['from'];
            $queryParams['to'] = $request['to'];
        }

        //card data
        $adminAccount = Account::where('user_id', Auth::user()->id)->first();
        $commission_earning = BookingDetailsAmount::whereHas('booking', function ($query) use ($request) {
            $query->ofBookingStatus('completed');
        })
            ->sum('admin_commission');

        $subscription_amounts = $this->transaction->whereIn('trx_type', ['subscription_purchase', 'subscription_renew', 'subscription_shift'])->sum('credit');

        $extra_fee = $this->transaction
            ->where('trx_type', TRX_TYPE['received_extra_fee'])
            ->sum('credit');

        $adminTotalEarning = $commission_earning + $subscription_amounts + $extra_fee;

        //table data
        $filteredTransactions = $this->transaction
            ->with(['booking', 'from_user.provider', 'to_user.provider'])
            ->when($request->has('transaction_type') && $request->transaction_type != 'all', function ($query) use ($request) {
                if ($request->transaction_type == 'debit') {
                    $query->where('debit', '!=', 0);
                } elseif ($request->transaction_type == 'credit') {
                    $query->where('credit', '!=', 0);
                }
            })
            ->when($request->has('zone_ids'), function ($query) use ($request) {
                $query->whereHas('booking', function ($query) use ($request) {
                    $query->whereIn('zone_id', $request['zone_ids']);
                })->orWhereHas('to_user.provider', function ($query) use ($request) {
                    $query->whereIn('zone_id', $request['zone_ids']);
                })->orWhereHas('from_user.provider', function ($query) use ($request) {
                    $query->whereIn('zone_id', $request['zone_ids']);
                });
            })
            ->when($request->has('provider_ids'), function ($query) use ($request) {
                $query->whereHas('to_user.provider', function ($query) use ($request) {
                    $query->whereIn('id', $request['provider_ids']);
                })->orWhereHas('from_user.provider', function ($query) use ($request) {
                    $query->whereIn('id', $request['provider_ids']);
                });
            })
            ->when($request->has('date_range'), function ($query) use ($request) {
                $this->applyDateRangeConditions($query, $request);
            })
            ->when($request->has('filter_by') && $request['filter_by'] != 'all', function ($query) use ($request) {
                $query->where('trx_type', $request['filter_by']);
            })
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->where('id', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->latest()->paginate(pagination_limit())->appends($queryParams);

        return view('adminmodule::admin.report.transaction', compact('zones', 'providers', 'filteredTransactions', 'adminAccount', 'commission_earning', 'adminTotalEarning','extra_fee', 'queryParams'));
    }


    /**
     * @param Request $request
     * @return StreamedResponse|string
     * @throws IOException
     * @throws InvalidArgumentException
     * @throws UnsupportedTypeException
     * @throws WriterNotOpenedException|AuthorizationException
     */
    public function downloadTransactionReport(Request $request): StreamedResponse|string
    {
        $this->authorize('report_export');
        Validator::make($request->all(), [
            'zone_ids' => 'array',
            'zone_ids.*' => 'uuid',
            'provider_ids' => 'array',
            'provider_ids.*' => 'uuid',
            'date_range' => 'in:all_time, this_week, last_week, this_month, last_month, last_15_days, this_year, last_year, last_6_month, this_year_1st_quarter, this_year_2nd_quarter, this_year_3rd_quarter, this_year_4th_quarter, custom_date',
            'from' => $request['date_range'] == 'custom_date' ? 'required' : '',
            'to' => $request['date_range'] == 'custom_date' ? 'required' : '',
            'filter_by' => 'in:collect_cash,payment,withdraw,commission,all',

            'transaction_type' => 'in:all,debit,credit'
        ]);

        $filteredTransactions = $this->transaction
            ->with(['booking', 'from_user', 'to_user'])
            ->when($request->has('transaction_type') && $request->transaction_type != 'all', function ($query) use ($request) {
                if ($request->transaction_type == 'debit') {
                    $query->where('debit', '!=', 0);
                } elseif ($request->transaction_type == 'credit') {
                    $query->where('credit', '!=', 0);
                }
            })
            ->when($request->has('zone_ids'), function ($query) use ($request) {
                $query->whereHas('booking', function ($query) use ($request) {
                    $query->whereIn('zone_id', $request['zone_ids']);
                });
            })
            ->when($request->has('provider_ids'), function ($query) use ($request) {
                $query->whereHas('booking', function ($query) use ($request) {
                    $query->whereIn('provider_id', $request['provider_ids']);
                });
            })
            ->when($request->has('date_range'), function ($query) use ($request) {
                $this->applyDateRangeConditions($query, $request);
            })
            ->when($request->has('filter_by') && $request['filter_by'] != 'all', function ($query) use ($request) {
                $query->where('trx_type', $request['filter_by']);
            })
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->where('id', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->latest()->get();

        return (new FastExcel($filteredTransactions))->download(time() . '-provider-report.xlsx', function ($transaction) {
            return [
                'Transaction ID' => $transaction->id,
                'Transaction Date' => date('d-M-Y h:ia', strtotime($transaction->created_at)),
                'Transaction To (full name)' => isset($transaction->to_user) ? $transaction->to_user->first_name . ' ' . $transaction->to_user->last_name : null,
                'Transaction To (phone)' => isset($transaction->to_user) ? $transaction->to_user->phone : null,
                'Transaction To (email)' => isset($transaction->to_user) ? $transaction->to_user->email : null,
                'Debit' => with_currency_symbol($transaction->debit),
                'Credit' => with_currency_symbol($transaction->credit),
                'Transactional Balance' => with_currency_symbol($transaction->balance),
            ];
        });
    }

    private function applyDateRangeConditions($query, $request): void
    {
        if ($request['date_range'] == 'custom_date') {
            $startDate = Carbon::parse($request['from'])->startOfDay();
            $endDate = Carbon::parse($request['to'])->endOfDay();
            $endDate->setHour(23)->setMinute(59)->setSecond(59);

            $query->whereBetween('created_at', [$startDate, $endDate]);
        } else {
            switch ($request['date_range']) {
                case 'this_week':
                    $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    break;
                case 'last_week':
                    $startOfWeek = Carbon::now()->subWeek()->startOfWeek();
                    $endOfWeek = Carbon::now()->subWeek()->endOfWeek();
                    $query->whereBetween('created_at', [$startOfWeek, $endOfWeek]);
                    break;
                case 'this_month':
                    $query->whereMonth('created_at', Carbon::now()->month);
                    break;
                case 'last_month':
                    $startOfMonth = Carbon::now()->subMonth()->startOfMonth();
                    $endOfMonth = Carbon::now()->subMonth()->endOfMonth();
                    $query->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
                    break;
                case 'last_15_days':
                    $startDate = Carbon::now()->subDay(15);
                    $endDate = Carbon::now();
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                    break;
                case 'this_year':
                    $query->whereYear('created_at', Carbon::now()->year);
                    break;
                case 'last_year':
                    $startOfYear = Carbon::now()->subYear()->startOfYear();
                    $endOfYear = Carbon::now()->subYear()->endOfYear();
                    $query->whereBetween('created_at', [$startOfYear, $endOfYear]);
                    break;
                case 'last_6_month':
                    $startDate = Carbon::now()->subMonth(6);
                    $endDate = Carbon::now();
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                    break;
                case 'this_year_1st_quarter':
                    $startDate = Carbon::now()->month(1)->startOfQuarter();
                    $endDate = Carbon::now()->month(1)->endOfQuarter();
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                    break;
                case 'this_year_2nd_quarter':
                    $startDate = Carbon::now()->month(4)->startOfQuarter();
                    $endDate = Carbon::now()->month(4)->endOfQuarter();
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                    break;
                case 'this_year_3rd_quarter':
                    $startDate = Carbon::now()->month(7)->startOfQuarter();
                    $endDate = Carbon::now()->month(7)->endOfQuarter();
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                    break;
                case 'this_year_4th_quarter':
                    $startDate = Carbon::now()->month(10)->startOfQuarter();
                    $endDate = Carbon::now()->month(10)->endOfQuarter();
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                    break;
                default:
                    break;
            }
        }
    }
   public function tabs()
    {
    //     $parent_id =  DB::table('categories')->where('parent_id','!=',0)->get();
    //     dd($parent_id);
    //     $cats = DB::table('categories')
    // // ->whereRaw('CAST(parent_id AS UNSIGNED) != ?', [0])
    // ->limit(100)
    // ->get();


$categories = DB::table('categories')->get();

// Initialize an empty array to store the processed data
$cats = [];

// Loop through each category
foreach ($categories as $category) {
    // Check if parent_id is not 0, then add to the array
    if ($category->parent_id != 0) {
        $cats[] = [
            'id' => $category->id,
            'name' => $category->name,
            'parent_id' => $category->parent_id,
            'description' => $category->description,
            'created_at' => $category->created_at,
            // Add other fields as needed
        ];
    }
}

      $tabsList =  DB::table('Main_Category')->get();
        $tabs = DB::table('category_tabs')
            ->select(
                'Main_Category.title',
                'categories.name as category_name',
                'Main_Category.zone_id',
                'category_tabs.id',
                'zones.name as zone_name'
            )
            ->leftJoin('categories', 'categories.id', '=', 'category_tabs.category_id')
            ->leftJoin('Main_Category', 'Main_Category.id', '=', 'category_tabs.Main_Category_id')
            ->leftJoin('zones', 'zones.id', '=', 'Main_Category.zone_id')
            ->orderBy('Main_Category.order')
            ->get();
    
        return view('adminmodule::admin.tabs', compact('tabs','cats','tabsList'));
    }
    public function tabsDelete($id){
        DB::table('category_tabs')->where('id', $id)->delete();
        return redirect()->back();
    }
    
    public function tabSave(Request $request){
        
        $catId = $request['category_id'];
        $tabId = $request['tab_id'];
        
        DB::table('category_tabs')->insert([
    'category_id' => $catId,
    'tab_id' => 0,
    'Main_Category_id' => $tabId,
]);
        return redirect()->back();

    }


}
