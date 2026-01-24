<?php

namespace Modules\BookingModule\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\BookingModule\Entities\TendorApplicant;

class Tendor extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'type', 'location', 'duration',
        'material_type', 'category_type', 'emd',
        'fee', 'desc','tendor_price','tendor_location','closing_date','tendor_closing_date','tendor_organization','tendor_code'
    ];
  
    public function applicants()
    {
        return $this->hasMany(TendorApplicant::class, 'tendor_id');
    }
   
}
