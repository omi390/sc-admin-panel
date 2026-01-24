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
use Modules\BusinessSettingsModule\Entities\Translation;
use Modules\ProviderManagement\Entities\WithdrawRequest;
use Modules\TransactionModule\Entities\Account;
use Modules\TransactionModule\Entities\Transaction;
use Modules\TransactionModule\Entities\WithdrawalMethod;
use Modules\UserManagement\Entities\User;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class WithdrawnController extends Controller
{
    protected User $user;
    protected Account $account;
    protected WithdrawRequest $withdraw_request;
    protected Transaction $transaction;
    protected WithdrawalMethod $withdrawalMethod;
    use AuthorizesRequests;

    public function __construct(User $user, Account $account, WithdrawRequest $withdraw_request, Transaction $transaction, WithdrawalMethod $withdrawalMethod)
    {
        $this->user = $user;
        $this->account = $account;
        $this->withdraw_request = $withdraw_request;
        $this->transaction = $transaction;
        $this->withdrawal_method = $withdrawalMethod;
    }


    //*** WITHDRAW METHOD RELATED FUNCTIONS ***

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     * @throws AuthorizationException
     */
    public function methodList(Request $request): Renderable
    {
        $this->authorize('withdraw_view');
        Validator::make($request->all(), [
            'search' => 'max:255',
            'body' => 'required',
        ]);

        $withdrawalMethods = $this->withdrawal_method->withoutGlobalScope('translate')
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->where('method_name', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->paginate(pagination_limit());
        $status = null;
        $search = $request['search'];
        return View('transactionmodule::admin.withdraw.method.list', compact('withdrawalMethods', 'status', 'search'));
    }

    /**
     * Create resource.
     * @return Renderable
     * @throws AuthorizationException
     */
    public function methodCreate(): Renderable
    {
        $this->authorize('withdraw_add');
        return View('transactionmodule::admin.withdraw.method.create');
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function methodStore(Request $request): RedirectResponse
    {
        $this->authorize('withdraw_add');
        $request->validate([
            'method_name' => 'required',
            'method_name.0' => 'required',
            'field_type' => 'required|array',
            'field_name' => 'required|array',
            'placeholder_text' => 'required|array',
            'is_required' => '',
            'is_default' => 'in:0,1 ',
        ],
        [
            'method_name.0.required' => translate('default_method_name_is_required'),
        ]
        );

        $methodFields = [];
        foreach ($request->field_name as $key => $fieldName) {
            $methodFields[] = [
                'input_type' => $request->field_type[$key],
                'input_name' => strtolower(str_replace(' ', "_", $request->field_name[$key])),
                'placeholder' => $request->placeholder_text[$key],
                'is_required' => isset($request['is_required']) && isset($request['is_required'][$key]) ? 1 : 0,
            ];
        }

        $dataCount = $this->withdrawal_method->withoutGlobalScope('translate')->get()->count();

        $withdrawalMethodObject = $this->withdrawal_method->withoutGlobalScope('translate')->updateOrCreate(
            ['method_name' => $request->method_name[array_search('default', $request->lang)]],
            [
                'method_fields' => $methodFields,
                'is_default' => ($request->has('is_default') && $request->is_default || $dataCount == 0) == '1' ? 1 : 0,
            ]
        );

        if ($request->has('is_default') && $request->is_default == '1') {
            $this->withdrawal_method->withoutGlobalScope('translate')->where('id', '!=', $withdrawalMethodObject->id)->update(['is_default' => 0]);
        }

        $defaultLang = str_replace('_', '-', app()->getLocale());

        foreach ($request->lang as $index => $key) {
            if ($defaultLang == $key && !($request->method_name[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\TransactionModule\Entities\WithdrawalMethod',
                            'translationable_id' => $withdrawalMethodObject->id,
                            'locale' => $key,
                            'key' => 'method_name'
                        ],
                        ['value' => $withdrawalMethodObject->method_name]
                    );
                }
            } else {

                if ($request->method_name[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\TransactionModule\Entities\WithdrawalMethod',
                            'translationable_id' => $withdrawalMethodObject->id,
                            'locale' => $key,
                            'key' => 'method_name'
                        ],
                        ['value' => $request->method_name[$index]]
                    );
                }
            }
        }

        Toastr::success(translate(DEFAULT_STORE_200['message']));
        return back();
    }

    /**
     * Edit resource.
     * @param $id
     * @return Renderable
     * @throws AuthorizationException
     */
    public function methodEdit($id): Renderable
    {
        $this->authorize('withdraw_update');
        $withdrawalMethod = $this->withdrawal_method->withoutGlobalScope('translate')->find($id);
        return View('transactionmodule::admin.withdraw.method.edit', compact('withdrawalMethod'));
    }

    /**
     * Update resource.
     * @param Request $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function methodUpdate(Request $request): RedirectResponse
    {
        $this->authorize('withdraw_update');
        $request->validate([
            'method_name' => 'required',
            'method_name.0' => 'required',
            'field_type' => 'required|array',
            'field_name' => 'required|array',
            'placeholder_text' => 'required|array',
            'is_required' => '',
            'is_default' => 'in:0,1 ',
        ],
        [
            'method_name.0.required' => translate('default_method_name_is_required'),
        ]
        );

        $withdrawalMethod = $this->withdrawal_method->withoutGlobalScope('translate')->find($request['id']);

        if (!isset($withdrawalMethod)) {
            Toastr::error(translate(DEFAULT_404['message']));
            return back();
        }

        $methodFields = [];
        foreach ($request->field_name as $key => $fieldName) {
            $methodFields[] = [
                'input_type' => $request->field_type[$key],
                'input_name' => strtolower(str_replace(' ', "_", $request->field_name[$key])),
                'placeholder' => $request->placeholder_text[$key],
                'is_required' => isset($request['is_required']) && isset($request['is_required'][$key]) ? 1 : 0,
            ];
        }

        $withdrawalMethodObject = $this->withdrawal_method->withoutGlobalScope('translate')->updateOrCreate(
            ['method_name' => $request->method_name[array_search('default', $request->lang)]],
            [
                'method_fields' => $methodFields,
                'is_default' => $request->has('is_default') && $request->is_default == '1' ? 1 : 0,
            ]
        );

        if ($request->has('is_default') && $request->is_default == '1') {
            $this->withdrawal_method->where('id', '!=', $withdrawalMethodObject->id)->update(['is_default' => 0]);
        }

        $defaultLang = str_replace('_', '-', app()->getLocale());

        foreach ($request->lang as $index => $key) {
            if ($defaultLang == $key && !($request->method_name[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\TransactionModule\Entities\WithdrawalMethod',
                            'translationable_id' => $withdrawalMethodObject->id,
                            'locale' => $key,
                            'key' => 'method_name'
                        ],
                        ['value' => $withdrawalMethodObject->method_name]
                    );
                }
            } else {

                if ($request->method_name[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\TransactionModule\Entities\WithdrawalMethod',
                            'translationable_id' => $withdrawalMethodObject->id,
                            'locale' => $key,
                            'key' => 'method_name'
                        ],
                        ['value' => $request->method_name[$index]]
                    );
                }
            }
        }

        Toastr::success(translate(DEFAULT_UPDATE_200['message']));
        return back();
    }

    /**
     * Destroy resource.
     * @param $id
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function methodDestroy($id): RedirectResponse
    {
        $this->authorize('withdraw_delete');
        $methodData = $this->withdrawal_method->withoutGlobalScope('translate')->where('id', $id)->first();
        $methodData->translations()->delete();
        $this->withdrawal_method->where('id', $id)->withoutGlobalScope('translate')->delete();
        Toastr::success(translate(DEFAULT_DELETE_200['message']));
        return back();
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function methodStatusUpdate(Request $request, $id): JsonResponse
    {
        $this->authorize('withdraw_manage_status');
        $withdrawalMethod = $this->withdrawal_method->withoutGlobalScope('translate')->where('id', $id)->first();

        if (!$withdrawalMethod->is_default) {
            $this->withdrawal_method->withoutGlobalScope('translate')->where('id', $id)->update(['is_active' => !$withdrawalMethod->is_active]);
            return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
        }
        return response()->json(response_formatter(DEFAULT_400), 200);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function methodDefaultStatusUpdate(Request $request, $id): JsonResponse
    {
        $this->authorize('withdraw_manage_status');
        $withdrawalMethod = $this->withdrawal_method->withoutGlobalScope('translate')->where('id', $id)->first();
        if ($withdrawalMethod->is_default == 1) {
            return response()->json(response_formatter(DEFAULT_STATUS_FAILED_200), 200);
        }

        $this->withdrawal_method->withoutGlobalScope('translate')->where('id', '!=', $id)->update(['is_default' => $withdrawalMethod->is_default]);
        $this->withdrawal_method->withoutGlobalScope('translate')->where('id', $id)->update(['is_default' => !$withdrawalMethod->is_default]);

        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }

}
