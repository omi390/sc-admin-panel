<?php

namespace Modules\BusinessSettingsModule\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class DataSetting extends Model
{
    use HasFactory;
    use HasUuid;

    protected $fillable = ['id', 'key', 'value','type', 'is_active'];

    protected static function newFactory()
    {
        return \Modules\BusinessSettingsModule\Database\factories\BusinessSettingsFactory::new();
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    public function getValueAttribute($value){
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == $this->key && $translation['value'] != null) {
                    return $translation['value'];
                }
            }
        }
        return $value;
    }

    protected static function booted()
    {
        static::addGlobalScope('translate', function (Builder $builder) {
            $builder->with(['translations' => function ($query) {
                return $query->where('locale', app()->getLocale());
            }]);
        });
    }
}
