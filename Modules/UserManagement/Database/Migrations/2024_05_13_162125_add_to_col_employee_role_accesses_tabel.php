<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddToColEmployeeRoleAccessesTabel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_role_accesses', function (Blueprint $table) {
            $table->tinyInteger('can_assign_serviceman')->default(0);
            $table->tinyInteger('can_give_feedback')->default(0);
            $table->tinyInteger('can_take_backup')->default(0);
            $table->tinyInteger('can_change_status')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_role_accesses', function (Blueprint $table) {
            $table->dropColumn('can_assign_serviceman');
            $table->dropColumn('can_give_feedback');
            $table->dropColumn('can_take_backup');
            $table->dropColumn('can_change_status');
        });
    }
}
