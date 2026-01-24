<?php

namespace Modules\ServiceManagement\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FavoriteService extends Model
{
    use HasFactory;

    protected $fillable = [];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id','id');
    }
    protected static function newFactory()
    {
        return \Modules\ServiceManagement\Database\factories\FavoriteServiceFactory::new();
    }
}
