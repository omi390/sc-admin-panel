<?php

namespace Modules\BusinessSettingsModule\Http\Controllers\Web\Provider;

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
    public function notificationSettingsGet(Request $request): \Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\Foundation\Application
    {
        $searchTerm = $request->input('search');
        $providerId = auth()->user()->provider->id;
        $notificationType = $request->get('notification_type', 'provider');
        $notificationSetup = $this->notificationSetup->where('user_type', $notificationType)
            ->with(['providerNotifications' => function ($query) use ($providerId) {
                $query->where('provider_id', $providerId);
            }])
            ->where(function($query) use ($searchTerm) {
                $query->where('title', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('sub_title', 'LIKE', "%{$searchTerm}%");
            })->get();

        return view('businesssettingsmodule::provider.notification', compact('notificationSetup','searchTerm'));
    }

    public function updateStatus(Request $request): \Illuminate\Http\JsonResponse
    {
        $providerId = auth()->user()->provider->id;

        $existingNotification = $this->providerNotificationSetup->where([
            'notification_setup_id' => $request->notification_id,
            'provider_id' => $providerId
        ])->first();

        if ($existingNotification) {
            $existingSettings = json_decode($existingNotification->value, true);
        } else {
            $existingSettings = [];
        }

        $existingSettings[$request->type] = $request->status;

        $this->providerNotificationSetup->updateOrCreate(
            [
                'notification_setup_id' => $request->notification_id,
                'provider_id' => $providerId
            ],
            [
                'value' => json_encode($existingSettings)
            ]
        );

        return response()->json(['success' => true]);
    }

}
