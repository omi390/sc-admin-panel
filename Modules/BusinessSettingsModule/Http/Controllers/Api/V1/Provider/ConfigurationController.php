<?php

namespace Modules\BusinessSettingsModule\Http\Controllers\Api\V1\Provider;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\BusinessSettingsModule\Entities\NotificationSetup;
use Modules\BusinessSettingsModule\Entities\ProviderNotificationSetup;

class ConfigurationController extends Controller
{
    private NotificationSetup $notificationSetup;
    private ProviderNotificationSetup $providerNotificationSetup;

    public function __construct(NotificationSetup $notificationSetup, ProviderNotificationSetup $providerNotificationSetup)
    {
        $this->notificationSetup = $notificationSetup;
        $this->providerNotificationSetup = $providerNotificationSetup;
    }

    /**
     * Display a listing of the resource.
     */
    public function notificationSettingsGet(Request $request): \Illuminate\Http\JsonResponse
    {
        $providerId = auth('api')->user()->provider->id;
        $notificationType = $request->get('notification_type', 'provider');
        $notificationSetup = $this->notificationSetup->where('user_type', $notificationType)
            ->with(['providerNotifications' => function ($query) use ($providerId) {
                $query->where('provider_id', $providerId);
            }])->get();

        $notificationSetup->each(function ($setup) {
            $setup->value = json_decode($setup->value, true);
            $setup->title = translate($setup->title);
            $setup->sub_title = translate($setup->sub_title);

            if ($setup->providerNotifications->isNotEmpty()) {
                $providerNotification = $setup->providerNotifications->first();
                $providerNotification->value = json_decode($providerNotification->value, true);
                $setup->provider_notifications = $providerNotification;
            } else {
                $setup->provider_notifications = null;
            }

            unset($setup->providerNotifications);
        });

        return response()->json(response_formatter(DEFAULT_200, $notificationSetup), 200);
    }

    public function updateStatus(Request $request): \Illuminate\Http\JsonResponse
    {
        $providerId = auth()->user()->provider->id;
        $notificationsData = $request->input('notifications', []);

        foreach ($notificationsData as $notificationId => $settings) {
            $existingNotification = $this->providerNotificationSetup->where([
                'notification_setup_id' => $notificationId,
                'provider_id' => $providerId
            ])->first();

            if ($existingNotification) {
                $existingSettings = json_decode($existingNotification->value, true);
            } else {
                $existingSettings = [];
            }

            foreach ($settings as $type => $status) {
                $existingSettings[$type] = $status;
            }

            $this->providerNotificationSetup->updateOrCreate(
                [
                    'notification_setup_id' => $notificationId,
                    'provider_id' => $providerId
                ],
                [
                    'value' => json_encode($existingSettings)
                ]
            );
        }

        return response()->json(response_formatter(DEFAULT_200), 200);
    }
}
