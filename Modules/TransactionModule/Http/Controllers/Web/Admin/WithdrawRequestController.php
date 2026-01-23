<?php

namespace Modules\TransactionModule\Http\Controllers\Web\Admin;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\ProviderManagement\Entities\WithdrawRequest;
use Modules\TransactionModule\Entities\Account;
use Modules\TransactionModule\Entities\Transaction;
use Modules\TransactionModule\Entities\WithdrawalMethod;
use Modules\UserManagement\Entities\User;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class WithdrawRequestController extends Controller
{
    protected User $user;
    protected Account $account;
    protected WithdrawRequest $withdrawRequest;
    protected Transaction $transaction;
    protected WithdrawalMethod $withdrawal_method;

    use AuthorizesRequests;

    public function __construct(User $user, Account $account, WithdrawRequest $withdrawRequest, Transaction $transaction, WithdrawalMethod $withdrawal_method)
    {
        $this->user = $user;
        $this->account = $account;
        $this->withdraw_request = $withdrawRequest;
        $this->transaction = $transaction;
        $this->withdrawal_method = $withdrawal_method;
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     * @throws ValidationException|AuthorizationException
     */
    public function index(Request $request): Renderable
    {
        $this->authorize('withdraw_view');
        Validator::make($request->all(), [
            'status' => 'required|in:pending,approved,denied,settled,all',
            'search' => 'max:255'
        ])->validate();

        $search = $request['search']??"";
        $status = $request['status']??'all';
        $queryParam = ['search' => $request['search'], 'status' => $status];

        $withdrawRequests = $this->withdraw_request
            ->with(['provider.bank_detail'])
            ->when($request->has('status') && $request['status'] != 'all', function ($query) use ($request) {
                return $query->where('request_status', $request->status);
            })
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->whereHas('provider', function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->where('company_name', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->latest()
            ->paginate(pagination_limit())->appends($queryParam);

        return View('transactionmodule::admin.withdraw.request.list', compact('withdrawRequests', 'status', 'search'));
    }

    /**
     * Display a listing of the resource.
     * @return string|StreamedResponse
     */
    public function download(Request $request): StreamedResponse|string
    {
        $this->authorize('withdraw_export');
        Validator::make($request->all(), [
            'status' => 'required|in:pending,approved,denied,settled,all',
        ])->validate();

        $withdrawRequests = $this->withdraw_request
            ->with(['provider.bank_detail', 'withdraw_method'])
            ->when($request->has('status') && $request['status'] != 'all', function ($query) use ($request) {
                return $query->where('request_status', $request->status);
            })
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('amount', 'LIKE', '%' . $key . '%')
                            ->orWhere('note', 'LIKE', '%' . $key . '%')
                            ->orWhere('request_status', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->latest()
            ->get();

        $withdrawRequests->map(function ($query) {
            $query->company_name = isset($query->provider) ? $query->provider->company_name : '';
            $query->company_phone = isset($query->provider) ? $query->provider->company_phone : '';
            $query->company_address = isset($query->provider) ? $query->provider->company_address : '';
            $query->company_email = isset($query->provider) ? $query->provider->company_email : '';

            $query->withdraw_id = $query->id;
            $query->withdrawal_amount = $query->amount;
            $query->payment_status = $query->is_paid ? 'paid' : 'unpaid';
            $query->optional_note = $query->admin_note;

            $query->withdraw_method_name = isset($query->withdraw_method) ? $query->withdraw_method->method_name : '';
            foreach ($query->withdrawal_method_fields as $key=>$field) {
                $query[$key] = $field;
            }
        });

        foreach ($withdrawRequests as $key=>$item) {
            unset($item['id']);
            unset($item['user_id']);
            unset($item['request_updated_by']);
            unset($item['created_at']);
            unset($item['updated_at']);
            unset($item['amount']);
            unset($item['is_paid']);
            unset($item['note']);
            unset($item['admin_note']);
            unset($item['withdrawal_method_fields']);
            unset($item['withdrawal_method_id']);
            unset($item['provider']);
            unset($item['withdraw_method']);
        }

        return (new FastExcel($withdrawRequests))->download(time().'-withdraw-request.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        Validator::make($request->all(), [
            'withdraw_request_file' => 'required|mimes:xlsx',
        ])->validate();

        try {
            $collections = (new FastExcel)->import($request->file('withdraw_request_file'));
        } catch (\Exception $exception) {
            Toastr::error(translate('You have uploaded a wrong format file, please upload the right file.'));
            return back();
        }

        $fieldArray = ['company_name', 'company_phone', 'company_address', 'payment_status', 'request_status'];
        if(count($collections) < 1) {
            Toastr::error(translate('At least one row value is required'));
            return back();
        }

        foreach ($fieldArray as $item) {
            if(!array_key_exists($item, $collections->first())) {
                Toastr::error(translate($item) . ' ' . translate('must not be empty.'));
                return back();
            }
        }

        foreach ($collections as $collection) {
            $withdrawRequest = $this->withdraw_request->find($collection['withdraw_id']);

            if ($collection['request_status'] == 'approved' && $withdrawRequest && $withdrawRequest->request_status == 'pending') {
                withdrawRequestAcceptTransaction($withdrawRequest['request_updated_by'], $withdrawRequest['amount']);

                $withdrawRequest->request_status = 'approved';
                $withdrawRequest->request_updated_by = $request->user()->id;
                $withdrawRequest->admin_note = $collection['optional_note'];
                $withdrawRequest->is_paid = 1;
                $withdrawRequest->save();
            }
            elseif($collection['request_status'] == 'settled' && $withdrawRequest && $withdrawRequest->request_status == 'approved') {
                $withdrawRequest->request_status = 'settled';
                $withdrawRequest->request_updated_by = $request->user()->id;
                $withdrawRequest->admin_note = $collection['optional_note'];
                $withdrawRequest->save();
            }
            elseif ($collection['request_status'] == 'denied' && $withdrawRequest && $withdrawRequest->request_status == 'pending') {
                withdrawRequestDenyTransaction($withdrawRequest['request_updated_by'], $withdrawRequest['amount']);

                $withdrawRequest->request_status = 'denied';
                $withdrawRequest->request_updated_by = $request->user()->id;
                $withdrawRequest->admin_note = $collection['optional_note'];
                $withdrawRequest->is_paid = 0;
                $withdrawRequest->save();
            }
        }

        Toastr::success(translate('Updated successfully!'));
        return back();
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param string $id
     * @return RedirectResponse
     * @throws ValidationException|AuthorizationException
     */
    public function updateStatus(Request $request, string $id): RedirectResponse
    {
        $this->authorize('withdraw_manage_status');
        Validator::make($request->all(), [
            'status' => 'required|in:approved,denied,settled',
            'note' => 'max:255',
        ])->validate();

        $withdrawRequest = $this->withdraw_request::find($id);

        if ($request['status'] == 'approved') {
            withdrawRequestAcceptTransaction($withdrawRequest['request_updated_by'], $withdrawRequest['amount']);

            $withdrawRequest->request_status = 'approved';
            $withdrawRequest->request_updated_by = $request->user()->id;
            $withdrawRequest->admin_note = $request->note;
            $withdrawRequest->is_paid = 1;
            $withdrawRequest->save();


            $user = $this->user->where('id', $withdrawRequest['user_id'])->first();
            $notification = isNotificationActive($user?->provider?->id, 'booking', 'notification', 'provider');

            $title = get_push_notification_message('widthdraw_request_approve', 'provider_notification', $user?->current_language_key);
            if ($title && $user && $user->fcm_token && $notification) {
                $dataInfo = [
                    'provider_name' => $user->provider->company_name,
                ];
                device_notification($user->fcm_token, $title, null, null, null, 'withdraw', null, $user->id, $dataInfo);
            }

        } else if ($request['status'] == 'settled') {
            $withdrawRequest->request_status = 'settled';
            $withdrawRequest->request_updated_by = $request->user()->id;
            $withdrawRequest->admin_note = $request->note;
            $withdrawRequest->save();

        } else if ($request['status'] == 'denied') {
            withdrawRequestDenyTransaction($withdrawRequest['request_updated_by'], $withdrawRequest['amount']);

            $withdrawRequest->request_status = 'denied';
            $withdrawRequest->request_updated_by = $request->user()->id;
            $withdrawRequest->admin_note = $request->note;
            $withdrawRequest->is_paid = 0;
            $withdrawRequest->save();

            $user = $this->user->where('id', $withdrawRequest['user_id'])->first();
            $notification = isNotificationActive($user?->provider?->id, 'booking', 'notification', 'provider');

            $dataInfo = [
                'provider_name' => $user?->provider?->company_name,
            ];
            $title = get_push_notification_message('widthdraw_request_deny', 'provider_notification', $user?->current_language_key);
            if ($title && $user && $user->fcm_token && $notification) {
                device_notification($user->fcm_token, $title, null, null, null, 'withdraw', null, $user->id, $dataInfo);
            }

        }

        Toastr::success(translate(DEFAULT_UPDATE_200['message']));
        return back();

    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @return JsonResponse
     */
    public function updateMultipleStatus(Request $request): JsonResponse
    {
        $this->authorize('withdraw_manage_status');
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,denied,settled',
            'request_ids' => 'required|array',
            'request_ids.*' => 'uuid',
            'note' => 'max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $withdrawRequests = $this->withdraw_request::whereIn('id', $request->request_ids)->get();

        if ($request['status'] == 'approved') {
            foreach ($withdrawRequests as $withdrawRequest) {
                if($withdrawRequest->request_status == 'pending') {
                    withdrawRequestAcceptTransaction($withdrawRequest['request_updated_by'], $withdrawRequest['amount']);

                    $withdrawRequest->request_status = 'approved';
                    $withdrawRequest->request_updated_by = $request->user()->id;
                    $withdrawRequest->admin_note = $request->note;
                    $withdrawRequest->is_paid = 1;
                    $withdrawRequest->save();
                }
            }

        } else if ($request['status'] == 'settled') {
            foreach ($withdrawRequests as $withdrawRequest) {
                if($withdrawRequest->request_status == 'approved') {
                    $withdrawRequest->request_status = 'settled';
                    $withdrawRequest->request_updated_by = $request->user()->id;
                    $withdrawRequest->admin_note = $request->note;
                    $withdrawRequest->save();
                }
            }

        } else if ($request['status'] == 'denied') {
            foreach ($withdrawRequests as $withdrawRequest) {
                if($withdrawRequest->request_status == 'pending') {
                    withdrawRequestDenyTransaction($withdrawRequest['request_updated_by'], $withdrawRequest['amount']);

                    $withdrawRequest->request_status = 'denied';
                    $withdrawRequest->request_updated_by = $request->user()->id;
                    $withdrawRequest->admin_note = $request->note;
                    $withdrawRequest->is_paid = 0;
                    $withdrawRequest->save();
                }
            }

        }

        return response()->json(response_formatter(DEFAULT_UPDATE_200), 200);
    }

}
