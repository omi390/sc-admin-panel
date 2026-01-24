<?php

namespace Modules\BusinessSettingsModule\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ErrorLog extends Model
{
    use HasFactory;
    use HasUuid;

    protected $fillable = ['status_code', 'url', 'hit_counts', 'redirect_url', 'redirect_status'];

    protected static function newFactory()
    {
        return \Modules\BusinessSettingsModule\Database\factories\ErrorLogFactory::new();
    }
}
