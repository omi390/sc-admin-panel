<?php

namespace Modules\BusinessSettingsModule\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\PaymentModule\Entities\PaymentRequest;
use Modules\ProviderManagement\Entities\Provider;

class PackageSubscriber extends Model
{
    use HasFactory;
    use HasUuid;

    protected $fillable = ['is_notified'];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(PaymentRequest::class, 'payment_id');
    }
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }
    public function package(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPackage::class, 'subscription_package_id');
    }

    public function feature(): HasMany
    {
        return $this->hasMany(PackageSubscriberFeature::class, 'package_subscriber_log_id', 'package_subscriber_log_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(PackageSubscriberLog::class, 'provider_id', 'provider_id');
    }

    public function limits(): HasMany
    {
        return $this->hasMany(PackageSubscriberLimit::class, 'subscription_package_id', 'subscription_package_id');
    }

    protected static function newFactory()
    {
        return \Modules\BusinessSettingsModule\Database\factories\PackageSubscriberFactory::new();
    }
}
