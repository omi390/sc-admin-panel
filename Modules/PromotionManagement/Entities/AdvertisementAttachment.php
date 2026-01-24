<?php

namespace Modules\PromotionManagement\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\BusinessSettingsModule\Entities\Storage;

class AdvertisementAttachment extends Model
{
    use HasFactory, HasUuid;

    protected $appends = ['provider_cover_image_full_path', 'provider_profile_image_full_path', 'promotional_video_full_path'];

    protected $fillable = [
        'advertisement_id',
        'file_extension_type',
        'file_name',
        'type'
    ];

    public function cover_image_storage()
    {
        return $this->hasOne(Storage::class, 'model_id')->where('model_column', '=', 'provider_cover_image');
    }

    public function profile_image_storage()
    {
        return $this->hasOne(Storage::class, 'model_id')->where('model_column', '=', 'provider_profile_image');
    }
    public function promotional_video_storage()
    {
        return $this->hasOne(Storage::class, 'model_id')->where('model_column', '=', 'promotional_video');
    }

    public function getProviderCoverImageFullPathAttribute()
    {
        $image = $this->file_name;
        $defaultPath = asset('public/assets/placeholder.png');

        if (!$image) {
            if (request()->is('api/*')) {
                $defaultPath = null;
            }
            return $defaultPath;
        }

        $s3Storage = $this->cover_image_storage;
        $path = 'advertisement/';
        $imagePath = $path . $image;

        return getSingleImageFullPath(imagePath: $imagePath, s3Storage: $s3Storage, defaultPath: $defaultPath);
    }
    public function getProviderProfileImageFullPathAttribute()
    {
        $image = $this->file_name;
        $defaultPath = asset('public/assets/placeholder.png');

        if (!$image) {
            if (request()->is('api/*')) {
                $defaultPath = null;
            }
            return $defaultPath;
        }

        $s3Storage = $this->profile_image_storage;
        $path = 'advertisement/';
        $imagePath = $path . $image;

        return getSingleImageFullPath(imagePath: $imagePath, s3Storage: $s3Storage, defaultPath: $defaultPath);
    }
    public function getPromotionalVideoFullPathAttribute()
    {
        $image = $this->file_name;
        $defaultPath = asset('public/assets/placeholder.png');

        if (!$image) {
            if (request()->is('api/*')) {
                $defaultPath = null;
            }
            return $defaultPath;
        }

        $s3Storage = $this->promotional_video_storage;
        $path = 'advertisement/';
        $imagePath = $path . $image;

        return getSingleImageFullPath(imagePath: $imagePath, s3Storage: $s3Storage, defaultPath: $defaultPath);
    }


    protected static function booted()
    {
        static::saved(function ($model) {
            $storageType = getDisk();
            if($model->isDirty('file_name') && $storageType != 'public'){
                saveSingleImageDataToStorage(model: $model, modelColumn : $model->type, storageType : $storageType);
            }
        });
    }

}
