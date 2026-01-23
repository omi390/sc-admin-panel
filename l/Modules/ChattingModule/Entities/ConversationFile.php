<?php

namespace Modules\ChattingModule\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ConversationFile extends Model
{
    use HasFactory, SoftDeletes, HasUuid;

    protected $fillable = [
        'conversation_id',
        'original_file_name',
        'stored_file_name',
        'file_type',
    ];

    protected static function newFactory()
    {
        return \Modules\ChattingModule\Database\factories\ConversationFileFactory::new();
    }

    protected $appends = ['file_size', 'stored_file_name_full_path'];

    public function getFileSizeAttribute(): ?string
    {
        if ($this->attributes['stored_file_name']) {
            $path = 'public/conversation/' . $this->attributes['stored_file_name'];

            if (Storage::disk('local')->exists($path)) {
                $fileSizeBytes = Storage::disk('local')->size($path);
                return $this->formatSizeUnits($fileSizeBytes);
            } else {
                return null;
            }
        }

        return null;
    }

    private function formatSizeUnits($bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function storage()
    {
        return $this->hasOne(\Modules\BusinessSettingsModule\Entities\Storage::class, 'model_id');
    }

    public function getStoredFileNameFullPathAttribute()
    {
        $image = $this->stored_file_name;
        $defaultPath = asset('public/assets/placeholder.png');

        if (!$image) {
            if (request()->is('api/*')) {
                $defaultPath = null;
            }
            return $defaultPath;
        }

        $s3Storage = $this->storage;
        $path = 'conversation/';
        $imagePath = $path . $image;

        return getSingleImageFullPath(imagePath: $imagePath, s3Storage: $s3Storage, defaultPath: $defaultPath);
    }

    protected static function booted()
    {
        static::saved(function ($model) {
            $storageType = getDisk();
            if($model->isDirty('stored_file_name') && $storageType != 'public'){
                saveSingleImageDataToStorage(model: $model, modelColumn : 'image', storageType : $storageType);
            }
        });
    }


}

