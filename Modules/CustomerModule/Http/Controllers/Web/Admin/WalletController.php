<?php

namespace Modules\CustomerModule\Http\Controllers\Web\Admin;

use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\TransactionModule\Entities\Transaction;
use Modules\UserManagement\Entities\User;
use Modules\ZoneManagement\Entities\Zone;
use Rap2hpoutre\FastExcel\FastExcel;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class WalletController extends Controller
{
    protected User $user;
    protected Zone $zone;
    protected Transaction $transaction;
    use AuthorizesRequests;

    public function __construct(User $user, Zone $zone, Transaction $transaction)
    {
        $this->user = $user;
        $this->zone = $zone;
        $this->transaction = $transaction;
    }


    /**
     * Display a listing of the resource.
     * @return Renderable
     * @throws AuthorizationException
     */
    public function addFund(): Renderable
    {
        $this->authorize('wallet_add');
        $users = $this->user->ofType(['customer'])->get();
        return view('customermodule::admin.wallet.fund-add', compact('users'));
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function storeFund(Request $request): RedirectResponse
    {
        $this->authorize('wallet_add');
        $request->validate([
            'user_id' => 'required|uuid',
            'amount' => 'required|min:0|not_in:0',
            'reference' => 'max:50',
        ]);

        addFundTransaction($request['user_id'], $request['amount'], $request['reference']);

        Toastr::success(translate(DEFAULT_STORE_200['message']));
        return back();
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     * @throws ValidationException|AuthorizationException
     */
    public function getFuncReport(Request $request): Renderable
    {
        $this->authorize('wallet_view');
        Validator::make($request->all(), [
            'zone_ids' => 'array',
            'zone_ids.*' => 'uuid',
            'date_range' => 'in:all_time,this_week,last_week,this_month,last_month,last_15_days,this_year,last_year,last_6_month,this_year_1st_quarter,this_year_2nd_quarter,this_year_3rd_quarter,this_year_4th_quarter,custom_date',
            'from' => $request['date_range'] == 'custom_date' ? 'required' : '',
            'to' => $request['date_range'] == 'custom_date' ? 'required' : '',

            'transaction_type' => 'in:all,debit,credit'
        ])->validate();

        $zones = $this->zone->select('id', 'name')->get();
        $customers = $this->user->ofType(['customer'])->select('id', 'first_name', 'last_name', 'phone')->get();

        $queryParams = [];
        $transactionType = $request->has('transaction_type') ? $request->transaction_type : 'all';
        $queryParams['transaction_type'] = $transactionType;
        $search = $request['search'];
        $queryParams['search'] = $search;
        if ($request->has('zone_ids')) {
            $queryParams['zone_ids'] = $request['zone_ids'];
        }
        if ($request->has('customer_ids')) {
            $queryParams['customer_ids'] = $request['customer_ids'];
        }
        if ($request->has('trx_type')) {
            $queryParams['trx_type'] = $request['trx_type'];
        }
        if ($request->has('date_range')) {
            $queryParams['date_range'] = $request['date_range'];
        }
        if ($request->has('date_range') && $request['date_range'] == 'custom_date') {
            $queryParams['from'] = $request['from'];
            $queryParams['to'] = $request['to'];
        }

        $totalCredit = $this->filterQuery($this->transaction, $request)
            ->with(['booking', 'from_user', 'to_user'])
            ->where('to_user_account', '!=', 'balance_pending')
            ->whereIn('trx_type', array_values(WALLET_TRX_TYPE))
            ->sum('credit');

        $totalDebit = $this->filterQuery($this->transaction, $request)
            ->with(['booking', 'from_user', 'to_user'])
            ->where('to_user_account', '!=', 'balance_pending')
            ->whereIn('trx_type', WALLET_TRX_TYPE)
            ->sum('debit');

        $filteredTransactions = $this->filterQuery($this->transaction, $request)
            ->with(['booking', 'from_user', 'to_user'])
            ->whereIn('trx_type', WALLET_TRX_TYPE)
            ->latest()->paginate(pagination_limit())->appends($queryParams);

        return view('customermodule::admin.wallet.report', compact('zones', 'customers', 'filteredTransactions', 'transactionType', 'totalCredit', 'totalDebit', 'queryParams'));
    }

    /**
     * Display a listing of the resource.
     * @return string|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function getFuncReportDownload(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse|string
    {
        $this->authorize('wallet_export');
        Validator::make($request->all(), [
            'zone_ids' => 'array',
            'zone_ids.*' => 'uuid',
            'date_range' => 'in:all_time,this_week,last_week,this_month,last_month,last_15_days,this_year,last_year,last_6_month,this_year_1st_quarter,this_year_2nd_quarter,this_year_3rd_quarter,this_year_4th_quarter,custom_date',
            'from' => $request['date_range'] == 'custom_date' ? 'required' : '',
            'to' => $request['date_range'] == 'custom_date' ? 'required' : '',

            'transaction_type' => 'in:all,debit,credit'
        ])->validate();

        $filteredTransactions = $this->filterQuery($this->transaction, $request)
            ->with(['booking', 'from_user', 'to_user'])
            ->whereIn('trx_type', WALLET_TRX_TYPE)
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


    /**
     * @param $instance
     * @param $request
     * @return mixed
     */
    function filterQuery($instance, $request): mixed
    {
        return $instance
            ->when($request->has('transaction_type') && $request['transaction_type'] != 'all', function ($query) use ($request) {
                if ($request['transaction_type'] == 'debit') {
                    $query->where('debit', '!=', 0);
                } elseif ($request['transaction_type'] == 'credit') {
                    $query->where('credit', '!=', 0);
                }
            })
            ->when($request->has('customer_ids'), function ($query) use ($request) {
                $query->whereHas('to_user', function ($query) use ($request) {
                    $query->whereIn('id', $request['customer_ids']);
                });
            })
            ->when($request->has('trx_type') && $request['trx_type'] == 'all', function ($query) use ($request) {
                $query->whereIn('trx_type', WALLET_TRX_TYPE);
            })
            ->when($request->has('trx_type') && $request['trx_type'] != 'all', function ($query) use ($request) {
                $query->where('trx_type', $request['trx_type']);
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
            })
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->whereHas('to_user', function ($query) use ($request, $keys) {
                    foreach ($keys as $key) {
                        $query->where('first_name', 'LIKE', '%' . $key . '%')
                            ->orWhere('last_name', 'LIKE', '%' . $key . '%')
                            ->orWhere('phone', 'LIKE', '%' . $key . '%');
                    }
                })->orWhereHas('from_user', function ($query) use ($request, $keys) {
                    foreach ($keys as $key) {
                        $query->where('first_name', 'LIKE', '%' . $key . '%')
                            ->orWhere('last_name', 'LIKE', '%' . $key . '%')
                            ->orWhere('phone', 'LIKE', '%' . $key . '%');
                    }
                });
            });
    }

}
