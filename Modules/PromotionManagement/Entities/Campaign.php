<?php

namespace Modules\PromotionManagement\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Config;
use Modules\BusinessSettingsModule\Entities\Storage;

class Campaign extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [];

    protected $casts = [
        'is_active' => 'integer'
    ];

    protected $appends = ['thumbnail_full_path', 'cover_image_full_path'];

    public function discount(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Discount::class, 'discount_id');
    }

    public function scopeOfStatus($query, $status)
    {
        $query->where('is_active', '=', $status);
    }

    public function storage_cover_image()
    {
        return $this->hasOne(Storage::class, 'model_id')->where('model_column', 'cover_image');
    }

    public function storage_thumbnail()
    {
        return $this->hasOne(Storage::class, 'model_id')->where('model_column', 'thumbnail');
    }

    public function getThumbnailFullPathAttribute()
    {
        $image = $this->thumbnail;
        $defaultPath = asset('public/assets/admin-module/img/media/upload-file.png');

        if (!$image) {
            if (request()->is('api/*')) {
                $defaultPath = null;
            }
            return $defaultPath;
        }

        $s3Storage = $this->storage_thumbnail;
        $path = 'campaign/';
        $imagePath = $path . $image;

        return getSingleImageFullPath(imagePath: $imagePath, s3Storage: $s3Storage, defaultPath: $defaultPath);
    }

    public function getCoverImageFullPathAttribute()
    {
        $image = $this->cover_image;
        $defaultPath = asset('public/assets/admin-module/img/media/upload-file.png');

        if (!$image) {
            if (request()->is('api/*')) {
                $defaultPath = null;
            }
            return $defaultPath;
        }

        $s3Storage = $this->storage_cover_image;
        $path = 'campaign/';
        $imagePath = $path . $image;

        return getSingleImageFullPath( imagePath: $imagePath, s3Storage: $s3Storage, defaultPath: $defaultPath);
    }

    protected static function booted()
    {
        static::addGlobalScope('zone_wise_data', function (Builder $builder) {
            if (request()->is('api/*/customer?*') || request()->is('api/*/customer/*')) {
                $builder->whereHas('discount', function ($query) {
                    $query->where('promotion_type', 'campaign')
                        ->whereDate('start_date', '<=', now())
                        ->whereDate('end_date', '>=', now())
                        ->where('is_active', 1);
                })->whereHas('discount.discount_types', function ($query) {
                    $query->where(['discount_type' => 'zone', 'type_wise_id' => config('zone_id')]);
                })->latest()->with(['discount'])->where(['is_active' => 1]);
            }
        });

        static::saved(function ($model) {
            $storageType = getDisk();
            if($model->isDirty('thumbnail') && $storageType != 'public'){
                saveSingleImageDataToStorage(model: $model, modelColumn : 'thumbnail', storageType : $storageType);
            }
            if($model->isDirty('cover_image') && $storageType != 'public'){
                saveSingleImageDataToStorage(model: $model, modelColumn : 'cover_image', storageType : $storageType);
            }
        });
    }
}
