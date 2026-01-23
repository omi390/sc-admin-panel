<?php

namespace Modules\TransactionModule\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\BusinessSettingsModule\Entities\Translation;

class WithdrawalMethod extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'method_name',
        'method_fields',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'method_fields' => 'array',
        'is_default' => 'integer',
        'is_active' => 'integer',
    ];

    protected function scopeOfStatus($query, $status)
    {
        $query->where('is_active', $status);
    }

    protected static function newFactory()
    {
        return \Modules\TransactionModule\Database\factories\WithdrawalMethodFactory::new();
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    public function getMethodNameAttribute($value){
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'method_name') {
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
