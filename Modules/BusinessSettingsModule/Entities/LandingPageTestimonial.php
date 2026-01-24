<?php

namespace Modules\BusinessSettingsModule\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class LandingPageTestimonial extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [];

    protected $appends = ['image_full_path'];

    protected static function newFactory()
    {
        return \Modules\BusinessSettingsModule\Database\factories\LandingPageTestimonialFactory::new();
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    public function getNameAttribute($value){
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'name') {
                    return $translation['value'];
                }
            }
        }

        return $value;
    }

    public function getDesignationAttribute($value){
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'designation') {
                    return $translation['value'];
                }
            }
        }

        return $value;
    }

    public function getReviewAttribute($value){
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'review') {
                    return $translation['value'];
                }
            }
        }

        return $value;
    }

    public function storage()
    {
        return $this->hasOne(Storage::class, 'model_id');
    }

    public function getImageFullPathAttribute()
    {
        $image = $this->image;
        $defaultPath = asset('public/assets/placeholder.png');

        if (!$image) {
            if (request()->is('api/*')) {
                $defaultPath = null;
            }
            return $defaultPath;
        }

        $s3Storage = $this->storage;
        $path = 'landing-page/';
        $imagePath = $path . $image;

        return getSingleImageFullPath(imagePath: $imagePath, s3Storage: $s3Storage, defaultPath: $defaultPath);
    }

    protected static function booted()
    {
        static::addGlobalScope('translate', function (Builder $builder) {
            $builder->with(['translations' => function ($query) {
                return $query->where('locale', app()->getLocale());
            }]);
        });

        static::saved(function ($model) {
            $storageType = getDisk();
            if($model->isDirty('image') && $storageType != 'public'){
                saveSingleImageDataToStorage(model: $model, modelColumn : 'image', storageType : $storageType);
            }
        });
    }
}
