<?php

namespace Modules\PaymentModule\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\BusinessSettingsModule\Entities\Translation;

class Bonus extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [];

    protected function scopeOfStatus($query, $status)
    {
        $query->where('is_active', $status);
    }
    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    public function getBonusTitleAttribute($value){
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'bonus_title') {
                    return $translation['value'];
                }
            }
        }

        return $value;
    }


    public function getShortDescriptionAttribute($value){
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'short_description') {
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
