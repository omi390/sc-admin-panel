<?php

namespace Modules\BusinessSettingsModule\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubscriptionPackage extends Model
{
    use HasFactory;
    use HasUuid;

    protected $fillable = [];

    protected static function newFactory()
    {
        return \Modules\BusinessSettingsModule\Database\factories\SubscriptionPackageFactory::new();
    }

    public function scopeOfStatus($query, $status)
    {
        $query->where('is_active', '=', $status);
    }

    public function subscriber(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PackageSubscriber::class, 'subscription_package_id', 'id');
    }

    public function subscriptionPackageFeature(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SubscriptionPackageFeature::class, 'subscription_package_id', 'id');
    }

    public function subscriptionPackageLimit(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SubscriptionPackageLimit::class, 'subscription_package_id', 'id');
    }

    public function subscriberPackageLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PackageSubscriberLog::class, 'subscription_package_id');
    }


}
