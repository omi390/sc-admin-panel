<?php

use Modules\UserManagement\Entities\User;

if (!function_exists('add_fund_success')) {
    /**
     * @param $data
     * @return void
     */
    function add_fund_success($data): void
    {
        $customer_user_id = $data['payer_id'];
        $amount = $data['payment_amount'];
        addFundTransactions($customer_user_id, $amount);

        //send notification
        $user = User::find($customer_user_id);
        $title =  with_currency_symbol($amount) . ' ' . get_push_notification_message('add_fund_wallet', 'customer_notification', $user?->current_language_key);
        $permission = isNotificationActive($user?->provider?->id, 'wallet', 'notification', 'user');
        $data_info = [
            'user_name' => $user?->first_name . ' '. $user->last_name
        ];
        if ($user->fcm_token && $title && $permission) {
            device_notification($user->fcm_token, $title, null, null, null, NOTIFICATION_TYPE['wallet'], null, $customer_user_id, $data_info);
        }
    }
}

if (!function_exists('add_fund_fail')) {
    /**
     * @param $data
     * @return void
     */
    function add_fund_fail($data): void
    {
        //
    }
}
