<?php

namespace Modules\ProviderManagement\Http\Controllers\Web\Admin;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\ProviderManagement\Entities\Provider;
use Modules\TransactionModule\Entities\Transaction;
use Modules\UserManagement\Entities\User;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CollectCashController extends Controller
{
    protected User $user;
    protected Provider $provider;
    protected Transaction $transaction;
    use AuthorizesRequests;

    public function __construct(User $user, Provider $provider, Transaction $transaction)
    {
        $this->user = $user;
        $this->provider = $provider;
        $this->transaction = $transaction;
    }

    /**
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function collectCash(Request $request): RedirectResponse
    {
        $this->authorize('provider_update');

        Validator::make($request->all(), [
            'provider_id' => 'required|uuid',
            'amount' => 'required|numeric|min:1',
        ]);


        $providerUserId = get_user_id($request['provider_id'], PROVIDER_USER_TYPES[0]);
        $providerUser = $this->user->with(['account'])->find($providerUserId);
        if(is_null($providerUser)) {
            Toastr::success(translate(DEFAULT_404['message']));
            return back();
        }

        if($request['amount'] > $providerUser->account->account_payable) {
            Toastr::error(translate(COLLECT_CASH_FAIL_200['message']));
            return back();
        }

        collectCashTransaction($request->provider_id, $request['amount']);

        Toastr::success(translate(COLLECT_CASH_SUCCESS_200['message']));
        return back();
    }

    /**
     *
     * @param $provider_id
     * @param Request $request
     * @return Renderable
     * @throws AuthorizationException
     */
    public function index($provider_id, Request $request)
    {
        $this->authorize('provider_view');

        Validator::make($request->all(), [
            'search' => 'search',
        ]);


        $search = $request->has('search') ? $request['search'] : '';
        $webPage = 'overview';
        $queryParam = ['search' => $search, 'web_page' => $webPage];

        $transactions = $this->transaction
            ->with(['from_user.account'])
            ->where('from_user_id', get_user_id($provider_id, PROVIDER_USER_TYPES[0]))
            ->where('trx_type', 'paid_commission')
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhere('id', 'LIKE', '%' . $key . '%')
                            ->orWhere('ref_trx_id', 'LIKE', '%' . $key . '%')
                            ->orWhere('trx_type', 'LIKE', '%' . $key . '%')
                            ->orWhere('from_user_id', 'LIKE', '%' . $key . '%')
                            ->orWhere('to_user_id', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->latest()
            ->paginate(pagination_limit())->appends($queryParam);

        $total_collected_cash = $this->transaction
            ->where('from_user_id', get_user_id($provider_id, PROVIDER_USER_TYPES[0]))
            ->where('trx_type', TRANSACTION_TYPE[3]['key'])
            ->sum('debit');

        return view('providermanagement::admin.account.collect-cash', compact('transactions', 'total_collected_cash', 'webPage', 'search', 'provider_id'));
    }
}
