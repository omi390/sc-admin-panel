<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameColPackageSubscriberFeaturesTabel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('package_subscriber_features', function(Blueprint $table) {
            $table->renameColumn('subscription_package_id', 'package_subscriber_log_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('package_subscriber_features', function(Blueprint $table) {
            $table->renameColumn('package_subscriber_log_id', 'subscription_package_id');
        });
    }
}
