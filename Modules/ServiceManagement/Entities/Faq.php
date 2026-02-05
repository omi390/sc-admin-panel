<?php

namespace Modules\ServiceManagement\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Faq extends Model
{
    use HasFactory;
    use HasUuid;

    protected $casts = [
        'is_active' => 'integer'
    ];

    protected $fillable = ['service_id', 'question', 'answer', 'title', 'description', 'is_active'];

    protected function scopeOfStatus($query, $status)
    {
        $query->where('is_active', $status);
    }

    protected static function newFactory()
    {
        return \Modules\ServiceManagement\Database\factories\FaqFactory::new();
    }
}
