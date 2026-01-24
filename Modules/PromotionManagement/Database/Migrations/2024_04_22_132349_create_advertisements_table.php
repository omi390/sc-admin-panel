<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdvertisementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advertisements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('readable_id')->nullable();
            $table->string('title', 255)->nullable();
            $table->string('description', 255)->nullable();
            $table->foreignUuid('provider_id')->nullable();
            $table->integer('priority')->nullable()->default(null);
            $table->string('type')->nullable()->comment('video_promotion, profile_promotion');
            $table->tinyInteger('is_paid')->default(0);
            $table->dateTime('start_date')->default(now());
            $table->dateTime('end_date')->default(now());
            $table->string('status')->default('pending')->comment('pending, approved, running, expired, denied, paused, canceled');
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
        Schema::dropIfExists('advertisements');
    }
}
