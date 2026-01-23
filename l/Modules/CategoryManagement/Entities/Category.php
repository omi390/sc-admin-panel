<?php

namespace Modules\CategoryManagement\Entities;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Modules\BusinessSettingsModule\Entities\Storage;
use Modules\BusinessSettingsModule\Entities\Translation;
use Modules\PromotionManagement\Entities\DiscountType;
use Modules\ServiceManagement\Entities\Service;
use Modules\ZoneManagement\Entities\Zone;
use App\Traits\HasUuid;

class Category extends Model
{
    use HasFactory;
    use HasUuid;

    protected $casts = [
        'position' => 'integer',
        'is_active' => 'integer',
    ];

    protected $appends = ['image_full_path'];

    protected $fillable = [];

    public function scopeOfStatus($query, $status)
    {
        $query->where('is_active', '=', $status);
    }

    public function scopeOfFeatured($query, $status)
    {
        $query->where('is_featured', '=', $status);
    }

    public function scopeOfType($query, $type)
    {
        $value = ($type == 'main') ? 1 : 2;
        $query->where(['position' => $value]);
    }

    public function zones(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Zone::class, 'category_zone');
    }

    public function zonesBasicInfo(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Zone::class, 'category_zone');
    }

    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function category_discount(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DiscountType::class, 'type_wise_id')
            ->whereHas('discount', function ($query) {
                $query->whereIn('discount_type', ['category', 'mixed'])
                    ->where('promotion_type', 'discount')
                    ->whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now())
                    ->where('is_active', 1);
            })->whereHas('discount.discount_types', function ($query) {
                $query->where(['discount_type' => 'zone', 'type_wise_id' => config('zone_id')]);
            })->with(['discount'])->latest();
    }

    public function campaign_discount(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DiscountType::class, 'type_wise_id')
            ->whereHas('discount', function ($query) {
                $query->where('promotion_type', 'campaign')
                    ->whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now())
                    ->where('is_active', 1);
            })->whereHas('discount.discount_types', function ($query) {
                $query->where(['discount_type' => 'zone', 'type_wise_id' => config('zone_id')]);
            })->with(['discount'])->latest();
    }

    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function services(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Service::class, 'sub_category_id');
    }

    public function services_by_category(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Service::class, 'category_id');
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

    public function storage()
    {
        return $this->hasOne(Storage::class, 'model_id');
    }

    public function getImageFullPathAttribute()
    {
        $image = $this->image;
        $defaultPath = asset('public/assets/placeholder.png');
        if (request()->is('admin/*')) {
            $defaultPath = asset('public/assets/admin-module/img/media/upload-file.png');
        }

        if (!$image) {
            if (request()->is('api/*')) {
                $defaultPath = null;
            }
            return $defaultPath;
        }

        $s3Storage = $this->storage;

        $path = 'category/';
        $imagePath = $path . $image;

        return getSingleImageFullPath(imagePath: $imagePath, s3Storage: $s3Storage, defaultPath: $defaultPath);
    }

    protected static function booted()
    {
        static::addGlobalScope('zone_wise_data', function (Builder $builder) {
            if (request()->is('api/*/customer?*') || request()->is('api/*/customer/*')) {
                $builder->whereHas('zones', function ($query) {
                    $query->where('zone_id', Config::get('zone_id'));
                })->with(['category_discount', 'campaign_discount']);
            }
        });

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
