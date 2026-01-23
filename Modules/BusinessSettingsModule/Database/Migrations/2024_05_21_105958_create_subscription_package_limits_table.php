<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubscriptionPackageLimitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscription_package_limits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('subscription_package_id')->nullable();
            $table->string('key')->nullable();
            $table->boolean('is_limited')->default(1);
            $table->unsignedInteger('limit_count')->default(0);
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
        Schema::dropIfExists('subscription_package_limits');
    }
}
