<?php

namespace Modules\RewardModule\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\BookingModule\Entities\Booking;
use Modules\CategoryManagement\Entities\Category;
use Modules\ServiceManagement\Entities\Variation;
use Modules\UserManagement\Entities\User;

class RewardPointUsage extends Model
{
    use HasUuid;

    protected $table = 'reward_point_usages';

    protected $fillable = [
        'user_id',
        'booking_id',
        'sub_category_id',
        'service_variant_id',
        'reward_points',
        'reward_config_id',
    ];

    protected $casts = [
        'reward_points' => 'decimal:3',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'sub_category_id');
    }

    public function serviceVariant(): BelongsTo
    {
        return $this->belongsTo(Variation::class, 'service_variant_id');
    }

    public function rewardConfig(): BelongsTo
    {
        return $this->belongsTo(RewardPointConfig::class, 'reward_config_id');
    }
}
