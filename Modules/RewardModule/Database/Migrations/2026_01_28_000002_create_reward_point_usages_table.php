<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRewardPointUsagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reward_point_usages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->foreignUuid('sub_category_id')->constrained('categories')->cascadeOnDelete();
            $table->decimal('reward_points', 24, 3)->default(0.000);
            $table->foreignUuid('reward_config_id')->constrained('reward_point_configs')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::table('reward_point_usages', function (Blueprint $table) {
            $table->index(['user_id', 'booking_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reward_point_usages');
    }
}
