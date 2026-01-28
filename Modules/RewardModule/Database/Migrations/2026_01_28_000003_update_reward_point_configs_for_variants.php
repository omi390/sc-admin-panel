<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateRewardPointConfigsForVariants extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reward_point_configs', function (Blueprint $table) {
            // Make sub_category_id nullable (we'll keep it for backward compatibility)
            $table->uuid('sub_category_id')->nullable()->change();
            
            // Add service_variant_id
            $table->uuid('service_variant_id')->nullable()->after('sub_category_id');
            $table->foreign('service_variant_id')
                ->references('id')
                ->on('variations')
                ->cascadeOnDelete();
            
            // Add minimum_order_amount
            $table->decimal('minimum_order_amount', 24, 3)->default(0.000)->after('reward_points');
            
            // Add index for service_variant_id
            $table->index('service_variant_id');
            $table->index(['service_variant_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reward_point_configs', function (Blueprint $table) {
            $table->dropForeign(['service_variant_id']);
            $table->dropIndex(['service_variant_id', 'is_active']);
            $table->dropIndex(['service_variant_id']);
            $table->dropColumn(['service_variant_id', 'minimum_order_amount']);
            $table->uuid('sub_category_id')->nullable(false)->change();
        });
    }
}
