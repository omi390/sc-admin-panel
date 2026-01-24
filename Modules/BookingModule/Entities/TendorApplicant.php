<?php

namespace Modules\BookingModule\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\BookingModule\Entities\Tendor;

class TendorApplicant extends Model
{
    protected $table = 'tendor_applicants';

    public function tendor()
    {
        return $this->belongsTo(Tendor::class, 'tendor_id');
    }
}