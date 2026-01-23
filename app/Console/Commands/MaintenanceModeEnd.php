<?php

namespace App\Console\Commands;

use App\Mail\MaintenanceModeStartEmail;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\BusinessSettingsModule\Entities\BusinessSettings;
use Modules\BusinessSettingsModule\Entities\Storage;
use Modules\ProviderManagement\Entities\Provider;

class MaintenanceModeEnd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:maintenance-mode-end';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a project up reminder email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $maintenanceSystemArray = ['mobile_app','web_app','provider_panel','provider_app','serviceman_app'];
        $selectedMaintenanceSystem = ((business_config('maintenance_system_setup', 'maintenance_mode'))?->live_values) ?? [];

        $maintenanceSystem = [];
        foreach ($maintenanceSystemArray as $system) {
            $maintenanceSystem[$system] = in_array($system, $selectedMaintenanceSystem) ? 1 : 0;
        }

        $selectedMaintenanceDuration = ((business_config('maintenance_duration_setup', 'maintenance_mode'))?->live_values) ?? [];
        $maintenanceStatus = (int)((business_config('maintenance_mode', 'maintenance_mode'))?->live_values) ?? 0;

        $status = 0;
        if ($maintenanceStatus == 1) {
            if (isset($selectedMaintenanceDuration['maintenance_duration']) && $selectedMaintenanceDuration['maintenance_duration'] == 'until_change') {
                $status = $maintenanceStatus;
            } else {
                if (isset($selectedMaintenanceDuration['start_date']) && isset($selectedMaintenanceDuration['end_date'])) {
                    $start = Carbon::parse($selectedMaintenanceDuration['start_date']);
                    $end = Carbon::parse($selectedMaintenanceDuration['end_date']);
                    $today = Carbon::now();
                    if ($today->between($start, $end)) {
                        BusinessSettings::where('key_name', 'maintenance_mode')->where('settings_type', 'maintenance_mode')->update(['live_values' => 0]);
                        Artisan::call('up');
                    }
                }
            }
        }
    }
}
