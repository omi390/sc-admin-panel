<?php

namespace Modules\ServiceManagement\Entities;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Config;
use Modules\BookingModule\Entities\BookingDetail;
use Modules\BusinessSettingsModule\Entities\Storage;
use Modules\BusinessSettingsModule\Entities\Translation;
use Modules\CategoryManagement\Entities\Category;
use Modules\PromotionManagement\Entities\DiscountType;
use Modules\ReviewModule\Entities\Review;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;

    protected $casts = [
        'tax' => 'float',
        'order_count' => 'float',
        'is_active' => 'integer',
        'rating_count' => 'integer',
        'avg_rating' => 'float',
    ];

    protected $fillable = [];

    protected $appends = ['thumbnail_full_path', 'cover_image_full_path'];

    public function variations(): HasMany
    {
        return $this->hasMany(Variation::class, 'service_id', 'id');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(FavoriteService::class, 'service_id', 'id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(BookingDetail::class, 'service_id', 'id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id')->withoutGlobalScopes();
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'sub_category_id', 'id')->withoutGlobalScopes();
    }

    public function service_discount(): HasMany
    {
        return $this->hasMany(DiscountType::class, 'type_wise_id')
            ->whereHas('discount', function ($query) {
                $query->whereIn('discount_type', ['service', 'mixed'])
                    ->where('promotion_type', 'discount')
                    ->whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now())
                    ->where('is_active', 1);
            })->whereHas('discount.discount_types', function ($query) {
                if (request()->is('api/*/provider?*') || request()->is('api/*/provider/*')) {
                    $query->where(['discount_type' => 'zone', 'type_wise_id' => request()->user()->provider->zone_id]);
                } elseif (request()->is('api/*/customer?*') || request()->is('api/*/customer/*')) {
                    $query->where(['discount_type' => 'zone', 'type_wise_id' => config('zone_id')]);
                }
            })->with(['discount'])->latest();
    }

    public function campaign_discount(): HasMany
    {
        return $this->hasMany(DiscountType::class, 'type_wise_id')
            ->whereHas('discount', function ($query) {
                $query->where('promotion_type', 'campaign')
                    ->whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now())
                    ->where('is_active', 1);
            })->whereHas('discount.discount_types', function ($query) {
                if (request()->is('api/*/provider?*') || request()->is('api/*/provider/*')) {
                    $query->where(['discount_type' => 'zone', 'type_wise_id' => request()->user()->provider->zone_id]);
                } elseif (request()->is('api/*/customer?*') || request()->is('api/*/customer/*')) {
                    $query->where(['discount_type' => 'zone', 'type_wise_id' => config('zone_id')]);
                }
            })->with(['discount'])->latest();
    }

    public function scopeActive($query)
    {
        $query->where(['is_active' => 1])
            ->whereHas('category', function ($query) {
                $query->where('is_active', 1);
            })
            ->whereHas('subCategory', function ($query) {
                $query->where('is_active', 1);
            });
    }

    public function scopeInActive($query)
    {
        $query->where(['is_active' => 0]);
    }

    public function scopeOfStatus($query, $status)
    {
        if($status == 1) {
            $query->where(['is_active' => 1])
                ->whereHas('category', function ($query) {
                    $query->where('is_active', 1);
                })
                ->whereHas('subCategory', function ($query) {
                    $query->where('is_active', 1);
                });

        } else if($status = 0) {
            $query->where(['is_active' => 0]);
        }
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    public function storage_cover_image()
    {
        return $this->hasOne(Storage::class, 'model_id')->where('model_column', 'cover_image');
    }

    public function storage_thumbnail()
    {
        return $this->hasOne(Storage::class, 'model_id')->where('model_column', 'thumbnail');
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

    public function getThumbnailFullPathAttribute()
    {
        $image = $this->thumbnail;
        $defaultPath = request()->is('*/edit/*') ? asset('public/assets/admin-module/img/media/upload-file.png') : asset('public/assets/admin-module/img/placeholder.png');

        if (!$image) {
            if (request()->is('api/*')) {
                $defaultPath = null;
            }
            return $defaultPath;
        }

        $s3Storage = $this->storage_thumbnail;
        $path = 'service/';
        $imagePath = $path . $image;

        return getSingleImageFullPath(imagePath: $imagePath, s3Storage: $s3Storage, defaultPath: $defaultPath);
    }

    public function getCoverImageFullPathAttribute()
    {
        $image = $this->cover_image;
        $defaultPath = asset('public/assets/admin-module/img/placeholder.png');

        if (!$image) {
            if (request()->is('api/*')) {
                $defaultPath = null;
            }
            return $defaultPath;
        }
        if (request()->is('*/detail/*')) {
            $defaultPath = asset('public/assets/admin-module/img/placeholder.png');
        }

        $s3Storage = $this->storage_cover_image;
        $path = 'service/';
        $imagePath = $path . $image;

        return getSingleImageFullPath(imagePath: $imagePath, s3Storage: $s3Storage, defaultPath: $defaultPath);
    }


    protected static function booted()
    {
        static::addGlobalScope('zone_wise_data', function (Builder $builder) {
            if (request()->is('api/*/customer?*') || request()->is('api/*/customer/*')) {
                $builder->whereHas('category.zones', function ($query) {
                    $query->where('zone_id', Config::get('zone_id'));
                })->with(['service_discount', 'campaign_discount']);
            } elseif (request()->is('api/*/provider?*') || request()->is('api/*/provider/*')) {
                if (auth()->check() && request()->user()->provider != null) {
                    $builder->whereHas('category.zones', function ($query) {
                        $query->where('zone_id', request()->user()->provider->zone_id);
                    })->with(['service_discount', 'campaign_discount']);
                }
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

        static::addGlobalScope('translate', function (Builder $builder) {
            $builder->with(['translations' => function ($query) {
                return $query->where('locale', app()->getLocale());
            }]);
        });
    }
}
