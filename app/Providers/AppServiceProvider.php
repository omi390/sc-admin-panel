<?php

namespace App\Providers;

use App\Traits\ActivationClass;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Laravel\Passport\Passport;
use Modules\AddonModule\Traits\AddonHelper;
use Modules\BusinessSettingsModule\Entities\BusinessSettings;

ini_set('memory_limit', '512M');

class AppServiceProvider extends ServiceProvider
{
    use  AddonHelper;

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap any application services.
     *
     */
    public function boot(Request $request)
    {
        if(env('FORCE_HTTPS', false)) {
            \URL::forceScheme('https');
        }

       
        Config::set('addon_admin_routes',$this->get_addon_admin_routes());
        Config::set('get_payment_publish_status',$this->get_payment_publish_status());

        try {
            Config::set('default_pagination', 25);
            Paginator::useBootstrap();
        } catch (\Exception $ex) {
            info($ex);
        }
    }
}
