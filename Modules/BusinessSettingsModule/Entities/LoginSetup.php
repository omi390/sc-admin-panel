<?php

namespace Modules\BusinessSettingsModule\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoginSetup extends Model
{
    use HasFactory;
    use HasUuid;

    protected $fillable = ['key','value'];

    protected static function newFactory()
    {
        return \Modules\BusinessSettingsModule\Database\factories\LoginSetupFactory::new();
    }
}
