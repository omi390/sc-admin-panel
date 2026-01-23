<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Modules\BusinessSettingsModule\Entities\BusinessSettings;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class Localization
{
    /**
     * @param $request
     * @param Closure $next
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function handle($request, Closure $next): mixed
    {
        if (env('DB_CONNECTION') && env('DB_HOST') && env('DB_DATABASE') && env('DB_USERNAME')) {
            $connected = false;
            try {
                if (DB::connection()->getPdo() && Schema::hasTable('business_settings')) {
                    $connected = true;
                }
            } catch (\Exception) {}
        }

        $defaultLocale = 'en';
        $defaultDirection = 'ltr';
        if (isset($connected) && $connected) {
            $systemLanguage = BusinessSettings::where('key_name', 'system_language')->first();
            if ($systemLanguage && isset($systemLanguage->live_values)) {
                foreach ($systemLanguage->live_values as $key => $data) {
                    if ($data['default']) {
                        $defaultLocale = $data['code'];
                        $defaultDirection = $data['direction'] ?? 'ltr';
                        break;
                    }
                }
            }
        }

        if ($request->is('provider*')) {
            $localeKey = 'provider_local';
            $directionKey = 'provider_site_direction';
        } elseif ($request->is('admin*')) {
            $localeKey = 'local';
            $directionKey = 'site_direction';
        } else {
            $localeKey = 'landing_local';
            $directionKey = 'landing_site_direction';
        }

        if (session()->has($localeKey)) {
            App::setLocale(session()->get($localeKey));
        } else {
            session()->put($localeKey, $defaultLocale);
            session()->put($directionKey, $defaultDirection);
            App::setLocale($defaultLocale);
        }

        return $next($request);
    }
}
