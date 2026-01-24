<?php

namespace Modules\PaymentModule\Lib;

use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Modules\BidModule\Entities\PostBid;
use Modules\BidModule\Http\Controllers\APi\V1\Customer\PostBidController;
use Modules\BookingModule\Http\Traits\BookingTrait;
use Modules\PaymentModule\Entities\PaymentRequest;
use Modules\PaymentModule\Traits\SubscriptionTrait;
use Modules\ProviderManagement\Entities\Provider;
use Modules\UserManagement\Entities\User;

class PaymentResponse
{
    use BookingTrait;
    use SubscriptionTrait;

    /**
     * @param $data
     * @return array
     */
    public static function success($data): array
    {
        $customer_user_id = $data['payer_id'];
        $tran_id = $data['transaction_id'];
        $payment_request_id = $data->id;

        $additional_data = json_decode($data['additional_data'], true);
        $request = collect([
            'access_token' => $additional_data['access_token'] ?? null,
            'zone_id' => $additional_data['zone_id'] ?? null,
            'service_schedule' => $additional_data['service_schedule'] ?? null,
            'service_address_id' => $additional_data['service_address_id'] ?? null,
            'service_address' => $additional_data['service_address'] ?? null,
            'payment_method' => $additional_data['payment_method'] ?? null,
            'callback' => $additional_data['callback'] ?? null,
            'is_partial' => $additional_data['is_partial'] ?? null,
            'post_id' => $additional_data['post_id'] ?? null,
            'provider_id' => $additional_data['provider_id'] ?? null,
        ]);

        if (!$request->has('post_id') || is_null($request['post_id'])) {
            $is_guest = !User::where('id', $customer_user_id)->exists();
            $response = (new PaymentResponse)->placeBookingRequest($customer_user_id, $request, $tran_id, $is_guest);

        } else {
            //for bidding
            $post_bid = PostBid::with(['post'])
                ->where('post_id', $request['post_id'])
                ->where('provider_id', $request['provider_id'])
                ->first();

            $data = [
                'payment_method' => $request['payment_method'],
                'zone_id' => $request['zone_id'],
                'service_tax' => $post_bid?->post?->service?->tax,
                'provider_id' => $post_bid?->provider_id,
                'price' => $post_bid?->offered_price,
                'service_schedule' => !is_null($request['service_schedule']) ? $request['service_schedule'] : $post_bid->post->booking_schedule,
                'service_id' => $post_bid->post->service_id,
                'category_id' => $post_bid->post->category_id,
                'sub_category_id' => $post_bid->post->category_id,
                'service_address_id' => !is_null($request['service_address_id']) ? $request['service_address_id'] : $post_bid->post->service_address_id,
                'is_partial' => $request['is_partial']
            ];

            $response = (new PaymentResponse)->placeBookingRequestForBidding(base64_decode($request['access_token']), $request, $tran_id, $data);
            if ($response['flag'] == 'success') {
                PostBidController::acceptPostBidOffer($post_bid->id, $response['booking_id']);
            }
        }

        //update payment request
        if ($response['flag'] == 'success' && $response['readable_id']) {
            $payment_request = PaymentRequest::find($payment_request_id);
            $payment_request->attribute = 'booking';
            $payment_request->attribute_id = $response['readable_id'];
            $payment_request->save();
        }

        $response['callback'] = $request['callback'];
        return $response;
    }

    /**
     * @param $data
     * @return array|RedirectResponse
     */
    public static function purchaseSubscriptionSuccess($data): array|RedirectResponse|string
    {
        DB::beginTransaction();

        try {
            $payment_request_id = $data->id;
            $payment_method = $data->payment_method;
            $additional_data = json_decode($data['additional_data'], true);
            $request = collect([
                'provider_id' => $additional_data['provider_id'] ?? null,
                'package_id' => $additional_data['package_id'] ?? null,
                'amount' => $additional_data['amount'] ?? null,
                'payment_id' => $payment_request_id ?? null,
                'payment_method' => $additional_data[$payment_method] ?? null,
                'free_trial_or_payment' => $additional_data['free_trial_or_payment'] ?? null,
                'payment_platform' => $additional_data['payment_platform'] ?? null,
                'name' => $additional_data['name'] ?? null,
                'package_status' => $additional_data['package_status'] ?? null,
                'callback' => $additional_data['callback'] ?? null,
            ]);

            $result = self::handlePurchasePackageSubscription(
                id: $request['package_id'],
                provider: $request['provider_id'],
                request: $request->toArray(),
                price: $request['amount'],
                name: $request['name']
            );

            if ($result) {
                $payment_request = PaymentRequest::find($payment_request_id);
                $payment_request->attribute = 'provider-reg';
                $payment_request->attribute_id = $request['provider_id'];
                $payment_request->save();

            }else{
                DB::rollBack();
                return ['error' => 'Subscription process failed. Please try again.'];
            }

            DB::commit();

            if ($request['payment_platform'] == 'web'){
                Toastr::success(translate(PROVIDER_REGISTERED_200['message']));
                return back();
            }

            $response['callback'] = $request['callback'];
            return $response;

        } catch (Exception $e) {
            DB::rollBack();
            return ['error' => 'Subscription process failed. Please try again.'];
        }
    }

    /**
     * @param $data
     * @return array|RedirectResponse|string
     */
    public static function purchaseSubscriptionFailed($data): array|RedirectResponse|string
    {
        DB::beginTransaction();

        try {
            $payment_request_id = $data->id;
            $payment_method = $data->payment_method;
            $additional_data = json_decode($data['additional_data'], true);
            $freeTrialStatus = (int)((business_config('free_trial_period', 'subscription_Setting'))->is_active);

            $request = collect([
                'provider_id' => $additional_data['provider_id'] ?? null,
                'package_id' => $additional_data['package_id'] ?? null,
                'amount' => $additional_data['amount'] ?? null,
                'payment_id' => $payment_request_id ?? null,
                'payment_method' => $additional_data[$payment_method] ?? null,
                'free_trial_or_payment' => $additional_data['free_trial_or_payment'] ?? null,
                'payment_platform' => $additional_data['payment_platform'] ?? null,
                'name' => $additional_data['name'] ?? null,
                'package_status' => $additional_data['package_status'] ?? null,
                'callback' => $additional_data['callback'] ?? null,
            ]);

            $result = self::handlePurchaseSubscriptionFailed(
                id: $request['package_id'],
                provider: $request['provider_id'],
                request: $request->toArray(),
                price: $request['amount'],
                name: $request['name']
            );

            if ($result) {
                $payment_request = PaymentRequest::find($payment_request_id);
                $payment_request->attribute = 'provider-reg';
                $payment_request->attribute_id = $request['provider_id'];
                $payment_request->save();

            }else{
                DB::rollBack();
                return ['error' => 'Subscription process failed. Please try again.'];
            }

            DB::commit();

            if (!$freeTrialStatus){
                Toastr::success(translate(PAYMENT_FAILED['message']));
                return back();
            }

            if ($request['payment_platform'] == 'web'){
                Toastr::success(translate(PAYMENT_FAILED_SHIFT_FREE_TRIAL['message']));
                return back();
            }

            $response['callback'] = $request['callback'];
            return $response;

        } catch (Exception $e) {
            DB::rollBack();
            return ['error' => 'Subscription process failed. Please try again.'];
        }
    }

    /**
     * @param $data
     * @return array|RedirectResponse
     */
    public static function renewSubscriptionSuccess($data): array|RedirectResponse|string
    {
        DB::beginTransaction();

        try {
            $payment_request_id = $data->id;
            $payment_method = $data->payment_method;
            $additional_data = json_decode($data['additional_data'], true);
            $request = collect([
                'provider_id' => $additional_data['provider_id'] ?? null,
                'package_id' => $additional_data['package_id'] ?? null,
                'amount' => $additional_data['amount'] ?? null,
                'payment_id' => $payment_request_id ?? null,
                'payment_method' => $additional_data[$payment_method] ?? null,
                'free_trial_or_payment' => $additional_data['free_trial_or_payment'] ?? null,
                'payment_platform' => $additional_data['payment_platform'] ?? null,
                'name' => $additional_data['name'] ?? null,
                'package_status' => $additional_data['package_status'] ?? null,
                'callback' => $additional_data['callback'] ?? null,
            ]);

            $result = self::handleRenewPackageSubscription(
                id: $request['package_id'],
                provider: $request['provider_id'],
                request: $request->toArray(),
                price: $request['amount'],
                name: $request['name']
            );

            if ($result) {
                $payment_request = PaymentRequest::find($payment_request_id);
                $payment_request->attribute = 'provider-reg';
                $payment_request->attribute_id = $request['provider_id'];
                $payment_request->save();

            }else{
                DB::rollBack();
                return ['error' => 'Subscription process failed. Please try again.'];
            }

            DB::commit();

            if ($request['payment_platform'] == 'web'){
                Toastr::success(translate(RENEW_SUBSCRIPTION_PACKAGE['message']));
                return back();
            }

            $response['callback'] = $request['callback'];
            return $response;

        } catch (Exception $e) {
            DB::rollBack();
            return ['error' => 'Subscription process failed. Please try again.'];
        }
    }

    /**
     * @param $data
     * @return array|RedirectResponse
     */
    public static function shiftSubscriptionSuccess($data): array|RedirectResponse|string
    {
        DB::beginTransaction();

        try {
            $payment_request_id = $data->id;
            $payment_method = $data->payment_method;
            $additional_data = json_decode($data['additional_data'], true);
            $request = collect([
                'provider_id' => $additional_data['provider_id'] ?? null,
                'package_id' => $additional_data['package_id'] ?? null,
                'amount' => $additional_data['amount'] ?? null,
                'payment_id' => $payment_request_id ?? null,
                'payment_method' => $additional_data[$payment_method] ?? null,
                'free_trial_or_payment' => $additional_data['free_trial_or_payment'] ?? null,
                'payment_platform' => $additional_data['payment_platform'] ?? null,
                'name' => $additional_data['name'] ?? null,
                'package_status' => $additional_data['package_status'] ?? null,
                'callback' => $additional_data['callback'] ?? null,
            ]);

            $result = self::handleShiftPackageSubscription(
                id: $request['package_id'],
                provider: $request['provider_id'],
                request: $request->toArray(),
                price: $request['amount'],
                name: $request['name']
            );

            if ($result) {
                $payment_request = PaymentRequest::find($payment_request_id);
                $payment_request->attribute = 'provider-reg';
                $payment_request->attribute_id = $request['provider_id'];
                $payment_request->save();

            }else{
                DB::rollBack();
                return ['error' => 'Subscription process failed. Please try again.'];
            }

            DB::commit();

            if ($request['payment_platform'] == 'web'){
                Toastr::success(translate(SHIFT_SUBSCRIPTION_PACKAGE['message']));
                return back();
            }

            $response['callback'] = $request['callback'];
            return $response;

        } catch (Exception $e) {
            DB::rollBack();
            return ['error' => 'Subscription process failed. Please try again.'];
        }
    }

    /**
     * @param $data
     * @return array|RedirectResponse
     */
    public static function businessPlanChangeSuccess($data): array|RedirectResponse|string
    {
        DB::beginTransaction();

        try {
            $payment_request_id = $data->id;
            $payment_method = $data->payment_method;
            $additional_data = json_decode($data['additional_data'], true);
            $request = collect([
                'provider_id' => $additional_data['provider_id'] ?? null,
                'package_id' => $additional_data['package_id'] ?? null,
                'amount' => $additional_data['amount'] ?? null,
                'payment_id' => $payment_request_id ?? null,
                'payment_method' => $additional_data[$payment_method] ?? null,
                'free_trial_or_payment' => $additional_data['free_trial_or_payment'] ?? null,
                'payment_platform' => $additional_data['payment_platform'] ?? null,
                'name' => $additional_data['name'] ?? null,
                'package_status' => $additional_data['package_status'] ?? null,
                'callback' => $additional_data['callback'] ?? null,
            ]);

            $result = self::handlePurchasePackageSubscription(
                id: $request['package_id'],
                provider: $request['provider_id'],
                request: $request->toArray(),
                price: $request['amount'],
                name: $request['name']
            );

            if ($result) {
                $payment_request = PaymentRequest::find($payment_request_id);
                $payment_request->attribute = 'provider-reg';
                $payment_request->attribute_id = $request['provider_id'];
                $payment_request->save();

            }else{
                DB::rollBack();
                return ['error' => 'Subscription process failed. Please try again.'];
            }

            DB::commit();

            if ($request['payment_platform'] == 'web'){
                Toastr::success(translate(PURCHASE_SUBSCRIPTION_PACKAGE['message']));
                return back();
            }

            $response['callback'] = $request['callback'];
            return $response;

        } catch (Exception $e) {
            DB::rollBack();
            return ['error' => 'Subscription process failed. Please try again.'];
        }
    }

}
