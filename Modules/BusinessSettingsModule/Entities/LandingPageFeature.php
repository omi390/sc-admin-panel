<?php

namespace Modules\BusinessSettingsModule\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class LandingPageFeature extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [];

    protected $appends = ['image_1_full_path', 'image_2_full_path'];

    protected static function newFactory()
    {
        return \Modules\BusinessSettingsModule\Database\factories\LandingPageFeatureFactory::new();
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translationable');
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

    public function getSubTitleAttribute($value){
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'sub_title') {
                    return $translation['value'];
                }
            }
        }

        return $value;
    }

    public function imageOneStorage()
    {
        return $this->hasOne(Storage::class, 'model_id')->where('model_column', 'image_1');
    }

    public function imageTwoStorage()
    {
        return $this->hasOne(Storage::class, 'model_id')->where('model_column', 'image_2');
    }

    public function getImage1FullPathAttribute()
    {
        $image = $this->image_1;
        $defaultPath = asset('public/assets/placeholder.png');

        if (!$image) {
            if (request()->is('api/*')) {
                $defaultPath = null;
            }
            return $defaultPath;
        }

        $s3Storage = $this->imageOneStorage;
        $path = 'landing-page/';
        $imagePath = $path . $image;

        return getSingleImageFullPath(imagePath: $imagePath, s3Storage: $s3Storage, defaultPath: $defaultPath);
    }

    public function getImage2FullPathAttribute()
    {
        $image = $this->image_2;
        $defaultPath = asset('public/assets/placeholder.png');

        if (!$image) {
            if (request()->is('api/*')) {
                $defaultPath = null;
            }
            return $defaultPath;
        }

        $s3Storage = $this->imageTwoStorage;
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
            if($model->isDirty('image_1') && $storageType != 'public'){
                saveSingleImageDataToStorage(model: $model, modelColumn : 'image_1', storageType : $storageType);
            }
            if($model->isDirty('image_2') && $storageType != 'public'){
                saveSingleImageDataToStorage(model: $model, modelColumn : 'image_2', storageType : $storageType);
            }
        });
    }
}
