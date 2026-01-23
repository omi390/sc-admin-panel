<?php

namespace Modules\ProviderManagement\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FavoriteProvider extends Model
{
    use HasFactory;

    protected $fillable = [];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'provider_id', 'id');
    }

    protected static function newFactory()
    {
        return \Modules\ProviderManagement\Database\factories\FavoriteProviderFactory::new();
    }
}
