<?php

namespace Modules\BusinessSettingsModule\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProviderNotificationSetup extends Model
{
    use HasFactory;

    protected $fillable = ['notification_setup_id', 'provider_id', 'value'];

    protected static function newFactory()
    {
        return \Modules\BusinessSettingsModule\Database\factories\ProviderNotificationSetupFactory::new();
    }

    public function notification(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(NotificationSetup::class, 'notification_setup_id');
    }
}
