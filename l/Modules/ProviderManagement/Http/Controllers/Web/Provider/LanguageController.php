<?php

namespace Modules\ProviderManagement\Http\Controllers\Web\Provider;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\BusinessSettingsModule\Entities\BusinessSettings;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ProviderManagement\Entities\WithdrawRequest;
use Modules\TransactionModule\Entities\Account;
use Modules\TransactionModule\Entities\Transaction;
use Modules\TransactionModule\Entities\WithdrawalMethod;
use Modules\UserManagement\Entities\User;
use Rap2hpoutre\FastExcel\FastExcel;

class LanguageController extends Controller
{
    protected User $user;
    protected Provider $provider;
    protected WithdrawRequest $withdraw_request;
    protected Transaction $transaction;
    protected Account $account;
    protected WithdrawalMethod $withdrawal_method;

    public function __construct(User $user, Provider $provider, WithdrawRequest $withdraw_request, Transaction $transaction, Account $account, withdrawalMethod $withdrawal_method)
    {
        $this->user = $user;
        $this->provider = $provider;
        $this->withdraw_request = $withdraw_request;
        $this->transaction = $transaction;
        $this->account = $account;
        $this->withdrawal_method = $withdrawal_method;
    }

    /**
     *
     * @param $local
     * @return RedirectResponse
     */
    public function lang($local): RedirectResponse
    {
        $direction = BusinessSettings::where('key_name', 'site_direction')->first();
        $direction = $direction->live_values ?? 'ltr';
        $language = BusinessSettings::where('key_name', 'system_language')->first();

        foreach ($language?->live_values as $key => $data) {
            if ($data['code'] == $local) {
                $direction = isset($data['direction']) ? $data['direction'] : 'ltr';
            }
        }

        session()->forget('provider_language_settings');
        provider_language_load();
        session()->put('provider_site_direction', $direction);
        session()->put('provider_local', $local);

        return redirect()->back();
    }

}
