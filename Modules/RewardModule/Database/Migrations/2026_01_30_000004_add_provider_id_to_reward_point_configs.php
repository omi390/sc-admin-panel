<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProviderIdToRewardPointConfigs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reward_point_configs', function (Blueprint $table) {
            $table->uuid('provider_id')->nullable()->after('service_variant_id');
            $table->foreign('provider_id')
                ->references('id')
                ->on('providers')
                ->nullOnDelete();
            $table->index('provider_id');
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
            $table->dropForeign(['provider_id']);
            $table->dropIndex(['provider_id']);
            $table->dropColumn('provider_id');
        });
    }
}
