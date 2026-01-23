<?php

namespace Modules\TransactionModule\Http\Controllers\Web\Admin;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controller;
use Modules\TransactionModule\Entities\Transaction;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class TransactionController extends Controller
{
    private Transaction $transaction;

    use AuthorizesRequests;

    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     * @throws AuthorizationException
     */
    public function index(Request $request): Renderable
    {
        $this->authorize('transaction_view');
        $request->validate([
            'start_date' => 'date',
            'end_date' => 'date',
            'trx_type' => 'in:debit,credit,all'
        ]);

        $search = $request->has('search') ? $request['search'] : '';
        $trxType = $request->has('trx_type') ? $request['trx_type'] : 'all';
        $queryParam = ['search' => $search, 'trx_type' => $trxType];


        $transactions = $this->transaction->with(['from_user.provider', 'to_user.provider'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('id', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->when($request['trx_type'] != 'all', function ($query) use ($request) {
                if ($request['trx_type'] == 'debit') {
                    return $query->where('debit', '!=', 0);
                } else {
                    return $query->where('credit', '!=', 0);
                }
            })
            ->when($request->has('from_date') && $request->has('to_date'), function ($query) use ($request) {
                $query->whereBetween('created_at', [date('Y-m-d', strtotime($request['from_date'])), date('Y-m-d', strtotime($request['to_date']))]);
            })
            ->latest()->paginate(pagination_limit())->appends($queryParam);

        $data = [
            'commissionEarning' => $this->transaction->where('trx_type', 'commission')->whereIn('to_user_account', ['received_balance'])
                ->when($request->has('start_date') && $request->has('end_date'), function ($query) use ($request) {
                    return $query->whereBetween('created_at', [$request['start_date'], $request['end_date']]);
                })->sum('credit'),
            'totalDebit' => $this->transaction
                ->when($request->has('start_date') && $request->has('end_date'), function ($query) use ($request) {
                    return $query->whereBetween('created_at', [$request['start_date'], $request['end_date']]);
                })->sum('debit'),
            'totalCredit' => $this->transaction
                ->when($request->has('start_date') && $request->has('end_date'), function ($query) use ($request) {
                    return $query->whereBetween('created_at', [$request['start_date'], $request['end_date']]);
                })->sum('credit')
        ];

        return view('transactionmodule::admin.list',compact('transactions', 'data', 'trxType', 'search'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
            'from_user_id' => 'required|uuid',
            'to_user_id' => 'required|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        DB::transaction(function () use ($request) {
            $transaction = $this->transaction;
            $data = [
                'ref_trx_id' => null,
                'booking_id' => $request->booking_id,
                'trx_type' => null,
                'debit' => $request->amount,
                'credit' => 0,
                'balance' => 0,
                'from_user_id' => $request->from_user_id,
                'to_user_id' => $request->to_user_id
            ];
            $transaction::create($data);

            $data = [
                'ref_trx_id' => $transaction['trx_id'],
                'booking_id' => $request->booking_id,
                'trx_type' => null,
                'debit' => 0,
                'credit' => $request->amount,
                'balance' => 0,
                'from_user_id' => $request->to_user_id,
                'to_user_id' => $request->from_user_id
            ];
            $transaction::create($data);
        });

        return response()->json(response_formatter(DEFAULT_STORE_200), 200);
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return string|StreamedResponse
     */
    public function download(Request $request): string|StreamedResponse
    {
        $request->validate([
            'start_date' => 'date',
            'end_date' => 'date',
            'trx_type' => 'in:debit,credit,all'
        ]);

        $items = $this->transaction
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('id', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->when($request['trx_type'] != 'all', function ($query) use ($request) {
                if ($request['trx_type'] == 'debit') {
                    return $query->where('debit', '!=', 0);
                } else {
                    return $query->where('credit', '!=', 0);
                }
            })
            ->when($request->has('from_date') && $request->has('to_date'), function ($query) use ($request) {
                $query->whereBetween('created_at', [date('Y-m-d', strtotime($request['from_date'])), date('Y-m-d', strtotime($request['to_date']))]);
            })
            ->latest()->get();

        return (new FastExcel($items))->download(time().'-file.xlsx');
    }
}
