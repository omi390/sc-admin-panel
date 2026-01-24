<?php

namespace Modules\UserManagement\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeeRoleAccess extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $fillable = [];

    protected static function newFactory()
    {
        return \Modules\UserManagement\Database\factories\EmployeeRoleAccessFactory::new();
    }
}
