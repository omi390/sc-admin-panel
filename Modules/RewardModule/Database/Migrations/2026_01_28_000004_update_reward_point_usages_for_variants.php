<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateRewardPointUsagesForVariants extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reward_point_usages', function (Blueprint $table) {
            // Make sub_category_id nullable
            $table->uuid('sub_category_id')->nullable()->change();
            
            // Add service_variant_id
            $table->uuid('service_variant_id')->nullable()->after('sub_category_id');
            $table->foreign('service_variant_id')
                ->references('id')
                ->on('variations')
                ->cascadeOnDelete();
            
            // Add index
            $table->index('service_variant_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reward_point_usages', function (Blueprint $table) {
            $table->dropForeign(['service_variant_id']);
            $table->dropIndex(['service_variant_id']);
            $table->dropColumn('service_variant_id');
            $table->uuid('sub_category_id')->nullable(false)->change();
        });
    }
}
