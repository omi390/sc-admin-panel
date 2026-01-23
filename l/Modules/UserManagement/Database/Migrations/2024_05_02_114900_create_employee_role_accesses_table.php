<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeRoleAccessesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_role_accesses', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('employee_id');
            $table->foreignUuid('role_id');
            $table->string('section_name');
            $table->tinyInteger('can_view')->default(1);
            $table->tinyInteger('can_add')->default(0);
            $table->tinyInteger('can_update')->default(0);
            $table->tinyInteger('can_delete')->default(0);
            $table->tinyInteger('can_export')->default(0);
            $table->tinyInteger('can_manage_status')->default(0);
            $table->tinyInteger('can_approve_or_deny')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_role_accesses');
    }
}
