<?php

namespace Modules\PromotionManagement\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdvertisementSettings extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'advertisement_id',
        'key',
        'value',
    ];

}
