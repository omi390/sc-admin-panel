<?php

namespace Modules\PaymentModule\Http\Controllers\Web\Admin;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\PaymentModule\Entities\OfflinePayment;

class OfflinePaymentController extends Controller
{
    protected OfflinePayment $offlinePayment;

    public function __construct(OfflinePayment $offlinePayment)
    {
        $this->offlinePayment = $offlinePayment;
    }


    //*** WITHDRAW METHOD RELATED FUNCTIONS ***

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Renderable
     */
    public function methodList(Request $request): Renderable
    {
        Validator::make($request->all(), [
            'search' => 'max:255',
            'body' => 'required',
        ]);

        $withdrawalMethods = $this->offlinePayment
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
        $type = 'offline_payment';
        $webPage = 'payment_config';
        return View('paymentmodule::admin.offline-payments.list', compact('withdrawalMethods', 'status', 'search', 'type', 'webPage'));
    }

    /**
     * Create resource.
     * @return Renderable
     */
    public function methodCreate(): Renderable
    {
        $type = 'offline_payment';
        $webPage = 'payment_config';
        return View('paymentmodule::admin.offline-payments.create', compact('type', 'webPage'));
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return RedirectResponse
     */
    public function methodStore(Request $request): RedirectResponse
    {
        $request->validate([
            'method_name' => 'required',
            'data' => 'required|array',
            'title' => 'required|array',
            'field_name' => 'required|array',
            'placeholder' => 'required|array',
            'is_required' => '',
        ]);

        //payment note for all
        $customer_information [] = [
            'field_name' => 'payment_note',
            'placeholder' => 'payment_note',
            'is_required' => 0
        ];

        foreach ($request->field_name as $key => $field_name) {
            $customer_information[] = [
                'field_name' => strtolower(str_replace(' ', "_", $request->field_name[$key])),
                'placeholder' => $request->placeholder[$key],
                'is_required' => isset($request['is_required']) && isset($request['is_required'][$key]) ? 1 : 0,
            ];
        }

        $paymentInformation = [];
        foreach ($request->data as $key => $data) {
            $paymentInformation[] = [
                'title' => strtolower(str_replace(' ', "_", $request->title[$key])),
                'data' => $request->data[$key],
            ];
        }

        $this->offlinePayment->updateOrCreate(
            ['method_name' => $request->method_name],
            [
                'customer_information' => $customer_information,
                'payment_information' => $paymentInformation
            ]
        );

        Toastr::success(translate(DEFAULT_STORE_200['message']));
        return back();
    }

    /**
     * Edit resource.
     * @param $id
     * @return Renderable
     */
    public function methodEdit($id): Renderable
    {
        $withdrawalMethod = $this->offlinePayment->find($id);
        $type = 'offline_payment';
        $webPage = 'payment_config';
        return View('paymentmodule::admin.offline-payments.edit', compact('withdrawalMethod', 'type', 'webPage'));
    }

    /**
     * Update resource.
     * @param Request $request
     * @return RedirectResponse
     */
    public function methodUpdate(Request $request): RedirectResponse
    {
        $request->validate([
            'method_name' => 'required',
            'data' => 'required|array',
            'title' => 'required|array',
            'field_name' => 'required|array',
            'placeholder' => 'required|array',
            'is_required' => '',
        ]);

        $withdrawal_method = $this->offlinePayment->find($request['id']);

        if (!isset($withdrawal_method)) {
            Toastr::error(translate(DEFAULT_404['message']));
            return back();
        }

        //payment note for all
        $customer_information [] = [
            'field_name' => 'payment_note',
            'placeholder' => 'payment_note',
            'is_required' => 0
        ];

        foreach ($request->field_name as $key => $field_name) {
            $customer_information[] = [
                'field_name' => strtolower(str_replace(' ', "_", $request->field_name[$key])),
                'placeholder' => $request->placeholder[$key],
                'is_required' => isset($request['is_required']) && isset($request['is_required'][$key]) ? $request['is_required'][$key] : 0,
            ];
        }

        $paymentInformation = [];
        foreach ($request->data as $key => $data) {
            $paymentInformation[] = [
                'title' => strtolower(str_replace(' ', "_", $request->title[$key])),
                'data' => $request->data[$key],
            ];
        }

        $this->offlinePayment->updateOrCreate(
            ['method_name' => $request->method_name],
            [
                'customer_information' => $customer_information,
                'payment_information' => $paymentInformation
            ]
        );

        Toastr::success(translate(DEFAULT_UPDATE_200['message']));
        return back();
    }

    /**
     * Destroy resource.
     * @param $id
     * @return RedirectResponse
     */
    public function methodDestroy($id): RedirectResponse
    {
        $this->offlinePayment->where('id', $id)->delete();
        Toastr::success(translate(DEFAULT_DELETE_200['message']));
        return back();
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function statusUpdate(Request $request, $id): JsonResponse
    {
        $offlinePayment = $this->offlinePayment->where('id', $id)->first();
        $this->offlinePayment->where('id', $id)->update(['is_active' => !$offlinePayment->is_active]);
        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }

}
