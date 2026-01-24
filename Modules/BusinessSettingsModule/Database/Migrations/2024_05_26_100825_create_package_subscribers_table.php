<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackageSubscribersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_subscribers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('provider_id')->nullable();
            $table->foreignUuid('subscription_package_id')->nullable();
            $table->foreignUuid('package_subscriber_log_id')->nullable();
            $table->string('package_name',255)->nullable();
            $table->decimal('package_price',24,2)->default(0.00);
            $table->timestamp('package_start_date')->nullable();
            $table->timestamp('package_end_date')->nullable();
            $table->integer('trial_duration')->default(0);
            $table->float('vat_percentage')->default(0);
            $table->float('vat_amount')->default(0);
            $table->string('payment_method', 50)->nullable();
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
        Schema::dropIfExists('package_subscribers');
    }
}
