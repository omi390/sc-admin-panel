<?php

namespace Modules\UserManagement\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RoleAccess extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $fillable = [];

    public function role(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    protected static function newFactory()
    {
        return \Modules\UserManagement\Database\factories\RoleAccessFactory::new();
    }
}
