<?php
namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;

trait MaintenanceModeTrait
{
    public function checkMaintenanceMode(): array
    {
        $maintenanceSystemArray = ['mobile_app','web_app','provider_panel','provider_app','serviceman_app'];
        $selectedMaintenanceSystem = ((business_config('maintenance_system_setup', 'maintenance_mode'))?->live_values) ?? [];

        $maintenanceSystem = [];
        foreach ($maintenanceSystemArray as $system) {
            $maintenanceSystem[$system] = in_array($system, $selectedMaintenanceSystem) ? 1 : 0;
        }

        $selectedMaintenanceDuration = ((business_config('maintenance_duration_setup', 'maintenance_mode'))?->live_values);
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
                        $status = 1;
                    }
                }
            }
        }

        $maintenanceMessages = ((business_config('maintenance_message_setup', 'maintenance_mode'))?->live_values);
        $maintenanceTypeAndDuration = $selectedMaintenanceDuration ?? [];

        if (empty($maintenanceMessages)) {
            $maintenanceMessages = (object) [];
        }

        if (empty($maintenanceTypeAndDuration)) {
            $maintenanceTypeAndDuration = (object) [];
        }

        return [
            'maintenance_status' => $status,
            'selected_maintenance_system' => $maintenanceSystem,
            'maintenance_messages' => $maintenanceMessages,
            'maintenance_type_and_duration' => $maintenanceTypeAndDuration,
        ];
    }
}
