<?php

namespace Modules\BusinessSettingsModule\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Storage extends Model
{
    use HasFactory, HasUuid;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'model',
        'model_id',
        'model_column',
        'storage_type',
        'created_at',
        'updated_at'
    ];

    public function model()
    {
        return $this->morphTo();
    }
}
