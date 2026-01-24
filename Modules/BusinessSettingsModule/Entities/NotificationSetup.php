<?php

namespace Modules\BusinessSettingsModule\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NotificationSetup extends Model
{
    use HasFactory;
    use HasUuid;

    protected $fillable = ['title', 'sub_title', 'key', 'value', 'user_type'];

    protected static function newFactory()
    {
        return \Modules\BusinessSettingsModule\Database\factories\NotificationSetupFactory::new();
    }

    public function providerNotifications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProviderNotificationSetup::class, 'notification_setup_id');
    }
}
