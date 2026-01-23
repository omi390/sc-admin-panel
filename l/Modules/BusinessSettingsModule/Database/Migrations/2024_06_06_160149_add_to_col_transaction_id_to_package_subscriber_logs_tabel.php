<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddToColTransactionIdToPackageSubscriberLogsTabel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('package_subscriber_logs', function (Blueprint $table) {
            $table->foreignUuid('primary_transaction_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('package_subscriber_logs', function (Blueprint $table) {
            $table->dropColumn('primary_transaction_id');
        });
    }
}
