<?php

namespace Modules\BusinessSettingsModule\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class BusinessSettings extends Model
{
    use HasFactory;
    use HasUuid;

    protected $casts = [
        'live_values'=>'array',
        'test_values'=>'array',
        'is_active'=>'integer',
    ];

    protected $fillable = ['key_name', 'live_values', 'test_values', 'settings_type', 'mode', 'is_active'];

    protected static function newFactory()
    {
        return \Modules\BusinessSettingsModule\Database\factories\BusinessSettingsFactory::new();
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    public function storage()
    {
        return $this->hasOne(Storage::class, 'model_id');
    }
}
