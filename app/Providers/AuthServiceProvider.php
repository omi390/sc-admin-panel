<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('dashboard', fn () => $this->checkAccess('dashboard', 'can_view'));

        Gate::define('booking_view', fn () => $this->checkAccess('booking', 'can_view'));
        Gate::define('booking_edit', fn () => $this->checkAccess('booking', 'can_update'));
        Gate::define('booking_delete', fn () => $this->checkAccess('booking', 'can_delete'));
        Gate::define('booking_export', fn () => $this->checkAccess('booking', 'can_export'));
        Gate::define('booking_can_manage_status', fn () => $this->checkAccess('booking', 'can_manage_status'));
        Gate::define('booking_can_approve_or_deny', fn () => $this->checkAccess('booking', 'can_approve_or_deny'));

        Gate::define('discount_view', fn () => $this->checkAccess('discount', 'can_view'));
        Gate::define('discount_add', fn () => $this->checkAccess('discount', 'can_add'));
        Gate::define('discount_update', fn () => $this->checkAccess('discount', 'can_update'));
        Gate::define('discount_delete', fn () => $this->checkAccess('discount', 'can_delete'));
        Gate::define('discount_export', fn () => $this->checkAccess('discount', 'can_export'));
        Gate::define('discount_manage_status', fn () => $this->checkAccess('discount', 'can_manage_status'));
        Gate::define('discount_approve_or_deny', fn () => $this->checkAccess('discount', 'can_approve_or_deny'));

        Gate::define('coupon_view', fn () => $this->checkAccess('coupon', 'can_view'));
        Gate::define('coupon_add', fn () => $this->checkAccess('coupon', 'can_add'));
        Gate::define('coupon_update', fn () => $this->checkAccess('coupon', 'can_update'));
        Gate::define('coupon_delete', fn () => $this->checkAccess('coupon', 'can_delete'));
        Gate::define('coupon_export', fn () => $this->checkAccess('coupon', 'can_export'));
        Gate::define('coupon_manage_status', fn () => $this->checkAccess('coupon', 'can_manage_status'));
        Gate::define('coupon_approve_or_deny', fn () => $this->checkAccess('coupon', 'can_approve_or_deny'));

        Gate::define('bonus_view', fn () => $this->checkAccess('bonus', 'can_view'));
        Gate::define('bonus_add', fn () => $this->checkAccess('bonus', 'can_add'));
        Gate::define('bonus_update', fn () => $this->checkAccess('bonus', 'can_update'));
        Gate::define('bonus_delete', fn () => $this->checkAccess('bonus', 'can_delete'));
        Gate::define('bonus_export', fn () => $this->checkAccess('bonus', 'can_export'));
        Gate::define('bonus_manage_status', fn () => $this->checkAccess('bonus', 'can_manage_status'));
        Gate::define('bonus_approve_or_deny', fn () => $this->checkAccess('bonus', 'can_approve_or_deny'));

        Gate::define('campaign_view', fn () => $this->checkAccess('campaign', 'can_view'));
        Gate::define('campaign_add', fn () => $this->checkAccess('campaign', 'can_add'));
        Gate::define('campaign_update', fn () => $this->checkAccess('campaign', 'can_update'));
        Gate::define('campaign_delete', fn () => $this->checkAccess('campaign', 'can_delete'));
        Gate::define('campaign_export', fn () => $this->checkAccess('campaign', 'can_export'));
        Gate::define('campaign_manage_status', fn () => $this->checkAccess('campaign', 'can_manage_status'));
        Gate::define('campaign_approve_or_deny', fn () => $this->checkAccess('campaign', 'can_approve_or_deny'));


        Gate::define('advertisement_view', fn () => $this->checkAccess('advertisement', 'can_view'));
        Gate::define('advertisement_add', fn () => $this->checkAccess('advertisement', 'can_add'));
        Gate::define('advertisement_update', fn () => $this->checkAccess('advertisement', 'can_update'));
        Gate::define('advertisement_delete', fn () => $this->checkAccess('advertisement', 'can_delete'));
        Gate::define('advertisement_export', fn () => $this->checkAccess('advertisement', 'can_export'));
        Gate::define('advertisement_manage_status', fn () => $this->checkAccess('advertisement', 'can_manage_status'));
        Gate::define('advertisement_approve_or_deny', fn () => $this->checkAccess('advertisement', 'can_approve_or_deny'));

        Gate::define('banner_view', fn () => $this->checkAccess('banner', 'can_view'));
        Gate::define('banner_add', fn () => $this->checkAccess('banner', 'can_add'));
        Gate::define('banner_update', fn () => $this->checkAccess('banner', 'can_update'));
        Gate::define('banner_delete', fn () => $this->checkAccess('banner', 'can_delete'));
        Gate::define('banner_export', fn () => $this->checkAccess('banner', 'can_export'));
        Gate::define('banner_manage_status', fn () => $this->checkAccess('banner', 'can_manage_status'));
        Gate::define('banner_approve_or_deny', fn () => $this->checkAccess('banner', 'can_approve_or_deny'));


        Gate::define('push_notification_view', fn () => $this->checkAccess('push_notification', 'can_view'));
        Gate::define('push_notification_add', fn () => $this->checkAccess('push_notification', 'can_add'));
        Gate::define('push_notification_update', fn () => $this->checkAccess('push_notification', 'can_update'));
        Gate::define('push_notification_delete', fn () => $this->checkAccess('push_notification', 'can_delete'));
        Gate::define('push_notification_export', fn () => $this->checkAccess('push_notification', 'can_export'));
        Gate::define('push_notification_manage_status', fn () => $this->checkAccess('push_notification', 'can_manage_status'));
        Gate::define('push_notification_approve_or_deny', fn () => $this->checkAccess('push_notification', 'can_approve_or_deny'));

        Gate::define('onboarding_request_view', fn () => $this->checkAccess('onboarding_request', 'can_view'));
        Gate::define('onboarding_request_add', fn () => $this->checkAccess('onboarding_request', 'can_add'));
        Gate::define('onboarding_request_update', fn () => $this->checkAccess('onboarding_request', 'can_update'));
        Gate::define('onboarding_request_delete', fn () => $this->checkAccess('onboarding_request', 'can_delete'));
        Gate::define('onboarding_request_export', fn () => $this->checkAccess('onboarding_request', 'can_export'));
        Gate::define('onboarding_request_manage_status', fn () => $this->checkAccess('onboarding_request', 'can_manage_status'));
        Gate::define('onboarding_request_approve_or_deny', fn () => $this->checkAccess('onboarding_request', 'can_approve_or_deny'));


        Gate::define('provider_view', fn () => $this->checkAccess('onboarding_request', 'can_view'));
        Gate::define('provider_add', fn () => $this->checkAccess('provider', 'can_add'));
        Gate::define('provider_update', fn () => $this->checkAccess('provider', 'can_update'));
        Gate::define('provider_delete', fn () => $this->checkAccess('provider', 'can_delete'));
        Gate::define('provider_export', fn () => $this->checkAccess('provider', 'can_export'));
        Gate::define('provider_manage_status', fn () => $this->checkAccess('provider', 'can_manage_status'));
        Gate::define('provider_approve_or_deny', fn () => $this->checkAccess('provider', 'can_approve_or_deny'));

        Gate::define('withdraw_view', fn () => $this->checkAccess('withdraw', 'can_view'));
        Gate::define('withdraw_add', fn () => $this->checkAccess('withdraw', 'can_add'));
        Gate::define('withdraw_update', fn () => $this->checkAccess('withdraw', 'can_update'));
        Gate::define('withdraw_delete', fn () => $this->checkAccess('withdraw', 'can_delete'));
        Gate::define('withdraw_export', fn () => $this->checkAccess('withdraw', 'can_export'));
        Gate::define('withdraw_manage_status', fn () => $this->checkAccess('withdraw', 'can_manage_status'));
        Gate::define('withdraw_approve_or_deny', fn () => $this->checkAccess('withdraw', 'can_approve_or_deny'));

        Gate::define('zone_view', fn () => $this->checkAccess('zone', 'can_view'));
        Gate::define('zone_add', fn () => $this->checkAccess('zone', 'can_add'));
        Gate::define('zone_update', fn () => $this->checkAccess('zone', 'can_update'));
        Gate::define('zone_delete', fn () => $this->checkAccess('zone', 'can_delete'));
        Gate::define('zone_export', fn () => $this->checkAccess('zone', 'can_export'));
        Gate::define('zone_manage_status', fn () => $this->checkAccess('zone', 'can_manage_status'));
        Gate::define('zone_approve_or_deny', fn () => $this->checkAccess('zone', 'can_approve_or_deny'));

        Gate::define('category_view', fn () => $this->checkAccess('category', 'can_view'));
        Gate::define('category_add', fn () => $this->checkAccess('category', 'can_add'));
        Gate::define('category_update', fn () => $this->checkAccess('category', 'can_update'));
        Gate::define('category_delete', fn () => $this->checkAccess('category', 'can_delete'));
        Gate::define('category_export', fn () => $this->checkAccess('category', 'can_export'));
        Gate::define('category_manage_status', fn () => $this->checkAccess('category', 'can_manage_status'));
        Gate::define('category_approve_or_deny', fn () => $this->checkAccess('category', 'can_approve_or_deny'));

        Gate::define('service_view', fn () => $this->checkAccess('service', 'can_view'));
        Gate::define('service_add', fn () => $this->checkAccess('service', 'can_add'));
        Gate::define('service_update', fn () => $this->checkAccess('service', 'can_update'));
        Gate::define('service_delete', fn () => $this->checkAccess('service', 'can_delete'));
        Gate::define('service_export', fn () => $this->checkAccess('service', 'can_export'));
        Gate::define('service_manage_status', fn () => $this->checkAccess('service', 'can_manage_status'));
        Gate::define('service_approve_or_deny', fn () => $this->checkAccess('service', 'can_approve_or_deny'));

        Gate::define('customer_view', fn () => $this->checkAccess('customer', 'can_view'));
        Gate::define('customer_add', fn () => $this->checkAccess('customer', 'can_add'));
        Gate::define('customer_update', fn () => $this->checkAccess('customer', 'can_update'));
        Gate::define('customer_delete', fn () => $this->checkAccess('customer', 'can_delete'));
        Gate::define('customer_export', fn () => $this->checkAccess('customer', 'can_export'));
        Gate::define('customer_manage_status', fn () => $this->checkAccess('customer', 'can_manage_status'));
        Gate::define('customer_approve_or_deny', fn () => $this->checkAccess('customer', 'can_approve_or_deny'));

        Gate::define('wallet_view', fn () => $this->checkAccess('wallet', 'can_view'));
        Gate::define('wallet_add', fn () => $this->checkAccess('wallet', 'can_add'));
        Gate::define('wallet_update', fn () => $this->checkAccess('wallet', 'can_update'));
        Gate::define('wallet_delete', fn () => $this->checkAccess('wallet', 'can_delete'));
        Gate::define('wallet_export', fn () => $this->checkAccess('wallet', 'can_export'));
        Gate::define('wallet_manage_status', fn () => $this->checkAccess('wallet', 'can_manage_status'));
        Gate::define('wallet_approve_or_deny', fn () => $this->checkAccess('wallet', 'can_approve_or_deny'));

        Gate::define('point_view', fn () => $this->checkAccess('point', 'can_view'));
        Gate::define('point_add', fn () => $this->checkAccess('point', 'can_add'));
        Gate::define('point_update', fn () => $this->checkAccess('point', 'can_update'));
        Gate::define('point_delete', fn () => $this->checkAccess('point', 'can_delete'));
        Gate::define('point_export', fn () => $this->checkAccess('point', 'can_export'));
        Gate::define('point_manage_status', fn () => $this->checkAccess('point', 'can_manage_status'));
        Gate::define('point_approve_or_deny', fn () => $this->checkAccess('point', 'can_approve_or_deny'));

        Gate::define('role_view', fn () => $this->checkAccess('role', 'can_view'));
        Gate::define('role_add', fn () => $this->checkAccess('role', 'can_add'));
        Gate::define('role_update', fn () => $this->checkAccess('role', 'can_update'));
        Gate::define('role_delete', fn () => $this->checkAccess('role', 'can_delete'));
        Gate::define('role_export', fn () => $this->checkAccess('role', 'can_export'));
        Gate::define('role_manage_status', fn () => $this->checkAccess('role', 'can_manage_status'));
        Gate::define('role_approve_or_deny', fn () => $this->checkAccess('role', 'can_approve_or_deny'));

        Gate::define('employee_view', fn () => $this->checkAccess('employee', 'can_view'));
        Gate::define('employee_add', fn () => $this->checkAccess('employee', 'can_add'));
        Gate::define('employee_update', fn () => $this->checkAccess('employee', 'can_update'));
        Gate::define('employee_delete', fn () => $this->checkAccess('employee', 'can_delete'));
        Gate::define('employee_export', fn () => $this->checkAccess('employee', 'can_export'));
        Gate::define('employee_manage_status', fn () => $this->checkAccess('employee', 'can_manage_status'));
        Gate::define('employee_approve_or_deny', fn () => $this->checkAccess('employee', 'can_approve_or_deny'));

        Gate::define('transaction_view', fn () => $this->checkAccess('transaction', 'can_view'));
        Gate::define('transaction_add', fn () => $this->checkAccess('transaction', 'can_add'));
        Gate::define('transaction_update', fn () => $this->checkAccess('transaction', 'can_update'));
        Gate::define('transaction_delete', fn () => $this->checkAccess('transaction', 'can_delete'));
        Gate::define('transaction_export', fn () => $this->checkAccess('transaction', 'can_export'));
        Gate::define('transaction_manage_status', fn () => $this->checkAccess('transaction', 'can_manage_status'));
        Gate::define('transaction_approve_or_deny', fn () => $this->checkAccess('transaction', 'can_approve_or_deny'));

        Gate::define('report_view', fn () => $this->checkAccess('report', 'can_view'));
        Gate::define('report_add', fn () => $this->checkAccess('report', 'can_add'));
        Gate::define('report_update', fn () => $this->checkAccess('report', 'can_update'));
        Gate::define('report_delete', fn () => $this->checkAccess('report', 'can_delete'));
        Gate::define('report_export', fn () => $this->checkAccess('report', 'can_export'));
        Gate::define('report_manage_status', fn () => $this->checkAccess('report', 'can_manage_status'));
        Gate::define('report_approve_or_deny', fn () => $this->checkAccess('report', 'can_approve_or_deny'));

        Gate::define('analytics_view', fn () => $this->checkAccess('analytics', 'can_view'));
        Gate::define('analytics_add', fn () => $this->checkAccess('analytics', 'can_add'));
        Gate::define('analytics_update', fn () => $this->checkAccess('analytics', 'can_update'));
        Gate::define('analytics_delete', fn () => $this->checkAccess('analytics', 'can_delete'));
        Gate::define('analytics_export', fn () => $this->checkAccess('analytics', 'can_export'));
        Gate::define('analytics_manage_status', fn () => $this->checkAccess('analytics', 'can_manage_status'));
        Gate::define('analytics_approve_or_deny', fn () => $this->checkAccess('analytics', 'can_approve_or_deny'));

        Gate::define('addon_view', fn () => $this->checkAccess('addon', 'can_view'));
        Gate::define('addon_add', fn () => $this->checkAccess('addon', 'can_add'));
        Gate::define('addon_update', fn () => $this->checkAccess('addon', 'can_update'));
        Gate::define('addon_delete', fn () => $this->checkAccess('addon', 'can_delete'));
        Gate::define('addon_export', fn () => $this->checkAccess('addon', 'can_export'));
        Gate::define('addon_manage_status', fn () => $this->checkAccess('addon', 'can_manage_status'));
        Gate::define('addon_approve_or_deny', fn () => $this->checkAccess('addon', 'can_approve_or_deny'));

        Gate::define('business_add', fn () => $this->checkAccess('business', 'can_add'));
        Gate::define('business_view', fn () => $this->checkAccess('business', 'can_view'));
        Gate::define('business_update', fn () => $this->checkAccess('business', 'can_update'));
        Gate::define('business_delete', fn () => $this->checkAccess('business', 'can_delete'));
        Gate::define('business_export', fn () => $this->checkAccess('business', 'can_export'));
        Gate::define('business_manage_status', fn () => $this->checkAccess('business', 'can_manage_status'));
        Gate::define('business_approve_or_deny', fn () => $this->checkAccess('business', 'can_approve_or_deny'));

        Gate::define('landing_add', fn () => $this->checkAccess('landing', 'can_add'));
        Gate::define('landing_view', fn () => $this->checkAccess('landing', 'can_view'));
        Gate::define('landing_update', fn () => $this->checkAccess('landing', 'can_update'));
        Gate::define('landing_delete', fn () => $this->checkAccess('landing', 'can_delete'));
        Gate::define('landing_export', fn () => $this->checkAccess('landing', 'can_export'));
        Gate::define('landing_manage_status', fn () => $this->checkAccess('landing', 'can_manage_status'));
        Gate::define('landing_approve_or_deny', fn () => $this->checkAccess('landing', 'can_approve_or_deny'));

        Gate::define('configuration_view', fn () => $this->checkAccess('configuration', 'can_view'));
        Gate::define('configuration_add', fn () => $this->checkAccess('configuration', 'can_add'));
        Gate::define('configuration_update', fn () => $this->checkAccess('configuration', 'can_update'));
        Gate::define('configuration_delete', fn () => $this->checkAccess('configuration', 'can_delete'));
        Gate::define('configuration_export', fn () => $this->checkAccess('configuration', 'can_export'));
        Gate::define('configuration_manage_status', fn () => $this->checkAccess('configuration', 'can_manage_status'));
        Gate::define('configuration_approve_or_deny', fn () => $this->checkAccess('configuration', 'can_approve_or_deny'));

        Gate::define('page_view', fn () => $this->checkAccess('page', 'can_view'));
        Gate::define('page_add', fn () => $this->checkAccess('page', 'can_add'));
        Gate::define('page_update', fn () => $this->checkAccess('page', 'can_update'));
        Gate::define('page_delete', fn () => $this->checkAccess('page', 'can_delete'));
        Gate::define('page_export', fn () => $this->checkAccess('page', 'can_export'));
        Gate::define('page_manage_status', fn () => $this->checkAccess('page', 'can_manage_status'));
        Gate::define('page_approve_or_deny', fn () => $this->checkAccess('page', 'can_approve_or_deny'));

        Gate::define('gallery_view', fn () => $this->checkAccess('gallery', 'can_view'));
        Gate::define('gallery_add', fn () => $this->checkAccess('gallery', 'can_add'));
        Gate::define('gallery_update', fn () => $this->checkAccess('gallery', 'can_update'));
        Gate::define('gallery_delete', fn () => $this->checkAccess('gallery', 'can_delete'));
        Gate::define('gallery_export', fn () => $this->checkAccess('gallery', 'can_export'));
        Gate::define('gallery_manage_status', fn () => $this->checkAccess('gallery', 'can_manage_status'));
        Gate::define('gallery_approve_or_deny', fn () => $this->checkAccess('gallery', 'can_approve_or_deny'));

        Gate::define('backup_view', fn () => $this->checkAccess('backup', 'can_view'));
        Gate::define('backup_add', fn () => $this->checkAccess('backup', 'can_add'));
        Gate::define('backup_update', fn () => $this->checkAccess('backup', 'can_update'));
        Gate::define('backup_delete', fn () => $this->checkAccess('backup', 'can_delete'));
        Gate::define('backup_export', fn () => $this->checkAccess('backup', 'can_export'));
        Gate::define('backup_manage_status', fn () => $this->checkAccess('backup', 'can_manage_status'));
        Gate::define('backup_approve_or_deny', fn () => $this->checkAccess('backup', 'can_approve_or_deny'));

        Gate::define('subscriber_view', fn () => $this->checkAccess('subscriber', 'can_view'));
        Gate::define('subscriber_add', fn () => $this->checkAccess('subscriber', 'can_add'));
        Gate::define('subscriber_update', fn () => $this->checkAccess('subscriber', 'can_update'));
        Gate::define('subscriber_delete', fn () => $this->checkAccess('subscriber', 'can_delete'));
        Gate::define('subscriber_export', fn () => $this->checkAccess('subscriber', 'can_export'));
        Gate::define('subscriber_manage_status', fn () => $this->checkAccess('subscriber', 'can_manage_status'));
        Gate::define('subscriber_approve_or_deny', fn () => $this->checkAccess('subscriber', 'can_approve_or_deny'));

        Gate::define('subscription_settings_view', fn () => $this->checkAccess('subscription_settings', 'can_view'));
        Gate::define('subscription_settings_add', fn () => $this->checkAccess('subscription_settings', 'can_add'));
        Gate::define('subscription_settings_update', fn () => $this->checkAccess('subscription_settings', 'can_update'));
        Gate::define('subscription_settings_delete', fn () => $this->checkAccess('subscription_settings', 'can_delete'));
        Gate::define('subscription_settings_export', fn () => $this->checkAccess('subscription_settings', 'can_export'));
        Gate::define('subscription_settings_manage_status', fn () => $this->checkAccess('subscription_settings', 'can_manage_status'));
        Gate::define('subscription_settings_approve_or_deny', fn () => $this->checkAccess('subscription_settings', 'can_approve_or_deny'));

        Gate::define('subscription_package_view', fn () => $this->checkAccess('subscription_package', 'can_view'));
        Gate::define('subscription_package_add', fn () => $this->checkAccess('subscription_package', 'can_add'));
        Gate::define('subscription_package_update', fn () => $this->checkAccess('subscription_package', 'can_update'));
        Gate::define('subscription_package_delete', fn () => $this->checkAccess('subscription_package', 'can_delete'));
        Gate::define('subscription_package_export', fn () => $this->checkAccess('subscription_package', 'can_export'));
        Gate::define('subscription_package_manage_status', fn () => $this->checkAccess('subscription_package', 'can_manage_status'));
        Gate::define('subscription_package_approve_or_deny', fn () => $this->checkAccess('subscription_package', 'can_approve_or_deny'));

    }

    private function checkAccess($sectionName, $action): bool
    {
        return auth()->user()->user_type == 'super-admin' || auth()->user()->module_access->where('role_id', auth()->user()->roles->first()->pivot->role_id)->where('section_name', $sectionName)->first()?->$action;
    }
}
