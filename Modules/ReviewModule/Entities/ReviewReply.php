<?php

namespace Modules\ReviewModule\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReviewReply extends Model
{
    use HasFactory;
    use HasUuid;

    protected $fillable = [];

    protected static function newFactory()
    {
        return \Modules\ReviewModule\Database\factories\ReviewReplyFactory::new();
    }
}
