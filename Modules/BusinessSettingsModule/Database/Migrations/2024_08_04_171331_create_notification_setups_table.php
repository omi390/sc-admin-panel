<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationSetupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_setups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('user_type')->nullable();
            $table->string('title')->nullable();
            $table->string('sub_title')->nullable();
            $table->string('key')->nullable();
            $table->text('value')->nullable();
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
        Schema::dropIfExists('notification_setups');
    }
}
