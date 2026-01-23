<?php

namespace Modules\PaymentModule\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Validator;
use Modules\PaymentModule\Library\Payer;
use Modules\PaymentModule\Library\Payment as Payment;
use Modules\PaymentModule\Library\Receiver;
use Modules\PaymentModule\Traits\Payment as PaymentTrait;
use Modules\UserManagement\Entities\User;

class SubscriptionPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse|Redirector|RedirectResponse|Application
     */
    public function index(Request $request): JsonResponse|Redirector|RedirectResponse|Application
    {
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|in:' . implode(',', array_column(GATEWAYS_PAYMENT_METHODS, 'key')),
            'package_id' => 'required|uuid',
            'provider_id' => 'required|uuid',
            'amount' => 'required',
        ]);

        if ($validator->fails()) {
            if ($request->has('callback')) return redirect($request['callback'] . '?flag=fail');
            else return response()->json(response_formatter(DEFAULT_400), 400);
        }

        $customer_user_id = base64_decode($request['access_token']) ?? '';

        $customer = User::find($customer_user_id);
        $payer = new Payer($customer['first_name'] . ' ' . $customer['last_name'], $customer['email'], $customer['phone'], '');
        $payment_info = new Payment(
            success_hook: 'subscription_success',
            failure_hook: 'subscription_fail',
            currency_code: currency_code(),
            payment_method: $request['payment_method'],
            payment_platform: $request['payment_platform'],
            payer_id: $customer_user_id,
            receiver_id: null,
            additional_data: $request->all(),
            payment_amount: $request['amount'],
            external_redirect_link: $request['callback'] ?? null,
            attribute: 'provider_id',
            attribute_id: time()
        );

        $receiver_info = new Receiver('receiver_name', 'example.png');
        $redirect_link = PaymentTrait::generate_link($payer, $payment_info, $receiver_info);
        return redirect($redirect_link);
    }
}
