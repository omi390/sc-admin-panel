<?php

namespace Modules\RewardModule\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\CategoryManagement\Entities\Category;
use Modules\ServiceManagement\Entities\Variation;

class RewardPointConfig extends Model
{
    use HasUuid;

    protected $table = 'reward_point_configs';

    protected $fillable = [
        'sub_category_id',
        'service_variant_id',
        'reward_points',
        'minimum_order_amount',
        'max_uses',
        'current_uses',
        'is_active',
    ];

    protected $casts = [
        'reward_points' => 'decimal:3',
        'minimum_order_amount' => 'decimal:3',
        'max_uses' => 'integer',
        'current_uses' => 'integer',
        'is_active' => 'boolean',
    ];

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'sub_category_id');
    }

    public function serviceVariant(): BelongsTo
    {
        return $this->belongsTo(Variation::class, 'service_variant_id');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(RewardPointUsage::class, 'reward_config_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getRemainingUsesAttribute(): ?int
    {
        if ($this->max_uses === 0) {
            return null; // unlimited
        }
        return max(0, $this->max_uses - $this->current_uses);
    }
}
