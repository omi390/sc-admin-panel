<?php

namespace Modules\ProviderManagement\Http\Controllers\Web\Admin;

use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Mail;
use Modules\BusinessSettingsModule\Emails\CancelSubscriptionMail;
use Modules\BusinessSettingsModule\Entities\PackageSubscriber;
use Modules\BusinessSettingsModule\Entities\SubscriptionPackage;
use Modules\PaymentModule\Entities\PaymentRequest;
use Modules\PaymentModule\Traits\SubscriptionTrait;
use Modules\ProviderManagement\Entities\Provider;
use Modules\TransactionModule\Entities\Transaction;

class SubscriptionController extends Controller
{
    private SubscriptionPackage $subscriptionPackage;
    private PackageSubscriber $packageSubscriber;
    private Provider $provider;
    private Transaction $transactions;
    private PaymentRequest $paymentRequest;
    use SubscriptionTrait;

    public function __construct(PackageSubscriber $packageSubscriber, Provider $provider, SubscriptionPackage $subscriptionPackage, Transaction $transactions, PaymentRequest $paymentRequest)
    {
        $this->packageSubscriber = $packageSubscriber;
        $this->provider = $provider;
        $this->subscriptionPackage = $subscriptionPackage;
        $this->transactions = $transactions;
        $this->paymentRequest = $paymentRequest;
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function cancel( Request $request): RedirectResponse
    {
        $packageId = $request->package_id;
        $providerId = $request->provider_id;

        $packageSubscriber = $this->packageSubscriber->where('subscription_package_id', $packageId)->where('provider_id', $providerId)->first();
        if ($packageSubscriber){
            try {
                $packageSubscriber->is_canceled = 1;
                $packageSubscriber->save();

                Mail::to($packageSubscriber?->provider?->owner?->email)->send(new CancelSubscriptionMail($packageSubscriber->provider));

            } catch (\Exception $exception) {
                info($exception);
            }

            Toastr::success(translate('Subscription canceled successfully'));
            return back();
        }

        Toastr::error(translate('Please Select valid plan'));
        return back();

    }

    public function ajaxRenewPackage(Request $request)
    {
        $packageId = $request->id;
        $providerId = $request->providerId;
        $subscriptionPackage = $this->subscriptionPackage->where('id', $packageId)->first();
        if ($subscriptionPackage){

            $html = view('providermanagement::admin.partials.renew-content', compact('subscriptionPackage', 'providerId'))->render();

            return response()->json($html);

        }
    }
    public function ajaxShiftPackage(Request $request)
    {
        $id = $request->id;
        $providerId = $request->providerId;
        $subscriptionPackage = $this->subscriptionPackage->where('id', $id)->first();
        $packageSubscriber = $this->packageSubscriber->where('provider_id',$providerId)->first();
        if ($subscriptionPackage){

            $html = view('providermanagement::admin.partials.shift-content', compact('subscriptionPackage', 'providerId', 'packageSubscriber'))->render();

            return response()->json($html);

        }
    }
    public function ajaxPurchasePackage(Request $request)
    {
        $packageId = $request->id;
        $providerId = $request->providerId;
        $subscriptionPackage = $this->subscriptionPackage->where('id', $packageId)->first();
        if ($subscriptionPackage){

            $html = view('providermanagement::admin.partials.purchase-content', compact('subscriptionPackage', 'providerId'))->render();

            return response()->json($html);

        }
    }

    public function renewPayment( Request $request): RedirectResponse
    {
        if ($request->payment_method == 'received_manually' || $request->payment_method == 'free_trial'){

            $package = $this->subscriptionPackage->where('id',$request->package_id)->ofStatus(1)->first();
            $vatPercentage      = (int)((business_config('subscription_vat', 'subscription_Setting'))->live_values ?? 0);
            $vatAmount          = $package->price * ($vatPercentage / 100);
            $vatWithPrice       = $package->price + $vatAmount;
            if (!$package){
                Toastr::error(translate('Please Select valid plan'));
                return back();
            }

            $packageId          = $package?->id;
            $price              = $vatWithPrice;
            $name               = $package?->name;
            $providerId         = $request->provider_id;
        }


        $packageSubscription = $this->packageSubscriber->where('provider_id', $providerId)->first();

        if ($packageSubscription->subscription_package_id == $packageId) {

            if ($request->payment_method == 'received_manually') {
                $payment = $this->paymentRequest;
                $payment->payment_amount = $price;
                $payment->success_hook = 'subscription_success';
                $payment->failure_hook = 'subscription_fail';
                $payment->payment_method = 'manually';
                $payment->additional_data = json_encode($request->all());
                $payment->attribute = 'provider-reg';
                $payment->attribute_id = $providerId;
                $payment->payment_platform = 'web';
                $payment->is_paid = 1;
                $payment->save();
                $request['payment_id'] = $payment->id;

                $result = $this->handleRenewPackageSubscription($packageId, $providerId, $request->all(), $price, $name);
            }

            if ($request->payment_method == 'free_trial') {
                $result = $this->handleFreeTrialPackageSubscription($packageId, $providerId, $price, $name);
            }
        }

        if (!$result) {
            Toastr::error(translate('Something went wrong'));
            return back();
        }

        Toastr::success(translate('Subscription renew successfully'));
        return back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function shiftPayment( Request $request): RedirectResponse
    {
        $result = true;
        if ($request->payment_method == 'received_manually' || $request->payment_method == 'free_trial'){

            $package = $this->subscriptionPackage->where('id',$request->package_id)->ofStatus(1)->first();
            $vatPercentage      = (int)((business_config('subscription_vat', 'subscription_Setting'))->live_values ?? 0);
            $vatAmount          = $package->price * ($vatPercentage / 100);
            $vatWithPrice       = $package->price + $vatAmount;
            if (!$package){
                Toastr::error(translate('Please Select valid plan'));
                return back();
            }

            $packageId          = $package?->id;
            $price              = $vatWithPrice;
            $name               = $package?->name;
            $providerId         = $request->provider_id;
        }


        $packageSubscription = $this->packageSubscriber->where('provider_id', $providerId)->first();

        if ($packageSubscription->subscription_package_id != $packageId) {

            if ($request->payment_method == 'received_manually') {
                $payment = $this->paymentRequest;
                $payment->payment_amount = $price;
                $payment->success_hook = 'subscription_success';
                $payment->failure_hook = 'subscription_fail';
                $payment->payment_method = 'manually';
                $payment->additional_data = json_encode($request->all());
                $payment->attribute = 'provider-reg';
                $payment->attribute_id = $providerId;
                $payment->payment_platform = 'web';
                $payment->is_paid = 1;
                $payment->save();
                $request['payment_id'] = $payment->id;

                $result = $this->handleShiftPackageSubscription($packageId, $providerId, $request->all(), $price, $name);
            }

            if ($request->payment_method == 'free_trial') {
                $result = $this->handleFreeTrialPackageSubscription($packageId, $providerId, $price, $name);
            }
        }

        if (!$result) {
            Toastr::error(translate('Something went wrong'));
            return back();
        }

        Toastr::success(translate('Subscription shift successfully'));
        return back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function purchasePayment( Request $request): RedirectResponse
    {
        if ($request->payment_method == 'received_manually' || $request->payment_method == 'free_trial'){

            $package = $this->subscriptionPackage->where('id',$request->package_id)->ofStatus(1)->first();
            $vatPercentage      = (int)((business_config('subscription_vat', 'subscription_Setting'))->live_values ?? 0);
            $vatAmount          = $package->price * ($vatPercentage / 100);
            $vatWithPrice       = $package->price + $vatAmount;
            if (!$package){
                Toastr::error(translate('Please Select valid plan'));
                return back();
            }

            $packageId          = $package?->id;
            $price              = $vatWithPrice;
            $name               = $package?->name;
            $providerId         = $request->provider_id;
        }


        $packageSubscription = $this->packageSubscriber->where('provider_id', $providerId)->first();

        if ($packageSubscription == null) {

            if ($request->payment_method == 'received_manually') {
                $payment = $this->paymentRequest;
                $payment->payment_amount = $price;
                $payment->success_hook = 'subscription_success';
                $payment->failure_hook = 'subscription_fail';
                $payment->payment_method = 'manually';
                $payment->additional_data = json_encode($request->all());
                $payment->attribute = 'provider-reg';
                $payment->attribute_id = $providerId;
                $payment->payment_platform = 'web';
                $payment->is_paid = 1;
                $payment->save();
                $request['payment_id'] = $payment->id;

                $result = $this->handlePurchasePackageSubscription($packageId, $providerId, $request->all(), $price, $name);
            }

            if ($request->payment_method == 'free_trial') {
                $result = $this->handleFreeTrialPackageSubscription($packageId, $providerId, $price, $name);
            }
        }

        if (!$result) {
            Toastr::error(translate('Something went wrong'));
            return back();
        }

        Toastr::success(translate('Subscription renew successfully'));
        return back();
    }

    public function toCommission( Request $request): RedirectResponse
    {
        $providerId = $request->provider_id;
        $subscriber = $this->packageSubscriber->where('provider_id',$providerId)->with('logs')->first();
        $usedTime   = (int)((business_config('usage_time', 'subscription_Setting'))->live_values ?? 0);

        if (!$subscriber){
            Toastr::error(translate('Something wrong'));
            return back();
        }

        $packageStartDate = Carbon::parse($subscriber->package_start_date);
        $packageEndDate = Carbon::parse($subscriber->package_end_date);
        $now = Carbon::now();

        if ($now->lessThanOrEqualTo($packageEndDate)) {
            $totalDuration = $packageStartDate->diffInDays($packageEndDate);
            $daysPassed = $packageStartDate->diffInDays($now);
            $percentageUsed = ($daysPassed / $totalDuration) * 100;
            $roundedPercentageUsed = ceil($percentageUsed);


            if ($usedTime > $roundedPercentageUsed) {
                shiftRefundSubscriptionTransaction(
                    provider_id: $providerId
                );
            }
        }
        $subscriber->delete();

        Toastr::success(translate('Subscription change successfully'));
        return back();

    }
}
