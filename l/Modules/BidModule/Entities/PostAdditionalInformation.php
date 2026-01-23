<?php

namespace Modules\BidModule\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PostAdditionalInformation extends Model
{
    use HasFactory;

    protected $fillable = [];
    
    protected static function newFactory()
    {
        return \Modules\BidModule\Database\factories\PostAdditionalInformationFactory::new();
    }
}
