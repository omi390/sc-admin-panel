<?php

namespace Modules\BusinessSettingsModule\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SeoSetting extends Model
{
    use HasFactory;
    use HasUuid;

    protected $appends = ['image_full_path'];
    protected $fillable = [
        'page_title',
        'meta_title',
        'meta_description',
        'meta_image',
        'canonicals_url',
        'index',
        'no_follow',
        'no_image_index',
        'no_archive',
        'no_snippet',
        'max_snippet',
        'max_snippet_value',
        'max_video_preview',
        'max_video_preview_value',
        'max_image_preview',
        'max_image_preview_value',
    ];

    protected static function newFactory()
    {
        return \Modules\BusinessSettingsModule\Database\factories\SeoSettingFactory::new();
    }

    public function storage()
    {
        return $this->hasOne(Storage::class, 'model_id');
    }
    public function getImageFullPathAttribute()
    {
        $image = $this->meta_image;
        $defaultPath = asset('public/assets/placeholder.png');
        if (request()->is('admin/*')) {
            $defaultPath = asset('public/assets/admin-module/img/media/banner-upload-file.png');
        }

        if (!$image) {
            if (request()->is('api/*')) {
                $defaultPath = null;
            }
            return $defaultPath;
        }

        $s3Storage = $this->storage;

        $path = 'seo/';
        $imagePath = $path . $image;

        return getSingleImageFullPath(imagePath: $imagePath, s3Storage: $s3Storage, defaultPath: $defaultPath);
    }
}
