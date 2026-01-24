<?php

namespace Modules\ProviderManagement\Traits;

use Modules\CartModule\Entities\Cart;
use Modules\CartModule\Traits\CartTrait;
use Modules\UserManagement\Entities\UserAddress;
use Illuminate\Support\Facades\DB;

trait WithdrawTrait
{
    public function adjustment($provider_user): void
    {
        $payable = $provider_user->account->account_payable;
        $receivable = $provider_user->account->account_receivable;

        if($payable > $receivable){
            $provider_user->account->decrement('account_payable', $receivable);
            $provider_user->account->decrement('account_receivable', $receivable);

            //withdraw tran

        }elseif($payable < $receivable){
            $provider_user->account->decrement('account_payable', $payable);
            $provider_user->account->decrement('account_receivable', $payable);
        }
    }

}
