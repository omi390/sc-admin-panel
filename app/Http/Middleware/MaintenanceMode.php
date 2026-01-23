<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Modules\BusinessSettingsModule\Entities\BusinessSettings;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('provider/*')) {
            $maintenance = Cache::get('maintenance');
            if ($maintenance) {
                $maintenanceStatus = $maintenance['status'];

                if ($maintenanceStatus && isset($maintenance['provider'])) {
                    if ($maintenance['provider']) {
                        if (isset($maintenance['maintenance_duration']) && $maintenance['maintenance_duration'] == 'until_change') {
                            return redirect()->route('maintenance-mode');
                        } else {
                            if (isset($maintenance['start_date']) && isset($maintenance['end_date'])) {
                                $start = Carbon::parse($maintenance['start_date']);
                                $end = Carbon::parse($maintenance['end_date']);
                                $today = Carbon::now();
                                if ($today->between($start, $end)) {
                                    return redirect()->route('maintenance-mode');
                                }
                            }
                        }
                    }
                }
            }
        }

        return $next($request);
    }

}
