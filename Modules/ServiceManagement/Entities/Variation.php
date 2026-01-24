<?php

namespace Modules\ServiceManagement\Entities;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Config;
use Modules\ZoneManagement\Entities\Zone;
use Modules\ProviderManagement\Entities\Provider;

class Variation extends Model
{
    use HasFactory;

    protected $casts = [
        'price' => 'float',
    ];

    protected $fillable = ['variant', 'variant_key', 'zone_id', 'price', 'service_id', 'provider_id', 'variation_image'];

    public function zone(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function provider(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    protected static function booted()
    {
        static::addGlobalScope('zone_wise_data', function (Builder $builder) {
            if (request()->is('api/*/customer?*') || request()->is('api/*/customer/*')) {
                $builder->where(['zone_id' => Config::get('zone_id')])->with(['zone:id,name']);
            } elseif (request()->is('api/*/provider?*') || request()->is('api/*/provider/*')) {
                if (auth()->check() && auth()->user()->provider != null) {
                    $builder->where(['zone_id' => auth()->user()->provider->zone_id])->with(['zone:id,name']);
                }
            }
        });
    }
}
