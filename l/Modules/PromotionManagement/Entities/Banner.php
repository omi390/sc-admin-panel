<?php

namespace Modules\PromotionManagement\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Config;
use Modules\BusinessSettingsModule\Entities\Storage;
use Modules\CategoryManagement\Entities\Category;
use Modules\ServiceManagement\Entities\Service;

class Banner extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [];

    protected $casts = [
        'is_active' => 'integer'
    ];

    protected $appends = ['banner_image_full_path'];

    public function scopeOfStatus($query, $status)
    {
        $query->where('is_active', '=', $status);
    }

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class, 'resource_id');
    }

    public function service(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Service::class, 'resource_id');
    }

    public function storage()
    {
        return $this->hasOne(Storage::class, 'model_id');
    }

    public function getBannerImageFullPathAttribute()
    {
        $image = $this->banner_image;
        $defaultPath = asset('public/assets/admin-module/img/media/banner-upload-file.png');

        if (!$image) {
            if (request()->is('api/*')) {
                $defaultPath = null;
            }
            return $defaultPath;
        }

        $s3Storage = $this->storage;
        $path = 'banner/';
        $imagePath = $path . $image;

        return getSingleImageFullPath(imagePath: $imagePath, s3Storage: $s3Storage, defaultPath: $defaultPath);
    }

    protected static function booted()
    {
        static::addGlobalScope('zone_wise_data', function (Builder $builder) {
            if (request()->is('api/*/customer?*') || request()->is('api/*/customer/*')) {
                $builder->whereHas('category.zones', function ($query) {
                    $query->where('zone_id', Config::get('zone_id'));
                })->orWhereHas('service.category.zones', function ($query) {
                    $query->where('zone_id', Config::get('zone_id'));
                })->orWhere('resource_type', 'link');
            }
        });

        static::saved(function ($model) {
            $storageType = getDisk();
            if($model->isDirty('banner_image') && $storageType != 'public'){
                saveSingleImageDataToStorage(model: $model, modelColumn : 'banner_image', storageType : $storageType);
            }
        });
    }

}
