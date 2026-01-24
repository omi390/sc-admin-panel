<?php

namespace Modules\PromotionManagement\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\BusinessSettingsModule\Entities\Translation;
use Modules\ProviderManagement\Entities\Provider;
use Carbon\Carbon;

class Advertisement extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'readable_id',
        'title',
        'description',
        'provider_id',
        'priority',
        'type',
        'priority',
        'status',
    ];

    protected $appends = ['default_title', 'default_description'];

    public function attachments()
    {
        return $this->hasMany(AdvertisementAttachment::class, 'advertisement_id', 'id')->where('type', '!=', 'promotional_video');
    }

    public function attachment()
    {
        return $this->hasOne(AdvertisementAttachment::class, 'advertisement_id', 'id')->where('type','promotional_video');
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class, 'provider_id', 'id');
    }

    public function note()
    {
        return $this->hasOne(AdvertisementNote::class, 'advertisement_id', 'id');
    }

    public function rating()
    {
        return $this->hasOne(AdvertisementSettings::class, 'advertisement_id', 'id')->where('key', 'rating');
    }

    public function review()
    {
        return $this->hasOne(AdvertisementSettings::class, 'advertisement_id', 'id')->where('key', 'review');
    }

    public function getStartDateAttribute($value): Carbon
    {
        return Carbon::parse($value);
    }

    public function getEndDateAttribute($value): Carbon
    {
        return Carbon::parse($value);
    }
    public function scopeOfRunning($query): void
    {
        $query->whereIn('status', ['approved', 'resumed'])->where('start_date', '<=', Carbon::today())->where('end_date', '>=', Carbon::today());
    }

    public function scopeOfExpired($query): void
    {
        $query->where('end_date', '<', Carbon::today());
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    // Define accessors for the virtual attributes
    public function getDefaultTitleAttribute()
    {
        return $this->getRawOriginal('title');
    }

    public function getDefaultDescriptionAttribute()
    {
        return $this->getRawOriginal('description');
    }

    public function getTitleAttribute($value){
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'title') {
                    return $translation['value'];
                }
            }
        }

        return $value;
    }

    public function getDescriptionAttribute($value){
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'description') {
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
