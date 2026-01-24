<?php

namespace Modules\BusinessSettingsModule\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubscriptionPackageLimit extends Model
{
    use HasFactory;
    use HasUuid;

    protected $fillable = [];

    protected static function newFactory()
    {
        return \Modules\BusinessSettingsModule\Database\factories\SubscriptionPackageLimitFactory::new();
    }
}
