<?php

namespace Modules\BusinessSettingsModule\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\PaymentModule\Entities\PaymentRequest;
use Modules\ProviderManagement\Entities\Provider;

class PackageSubscriberLog extends Model
{
    use HasFactory;
    use HasUuid;

    protected $fillable = [];


    public function payment(): BelongsTo
    {
        return $this->belongsTo(PaymentRequest::class,'payment_id', );
    }
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class,'provider_id', );
    }

    protected static function newFactory()
    {
        return \Modules\BusinessSettingsModule\Database\factories\PackageSubscriberLogFactory::new();
    }
}
