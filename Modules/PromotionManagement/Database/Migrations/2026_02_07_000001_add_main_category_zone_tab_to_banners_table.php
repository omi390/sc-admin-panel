<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->unsignedBigInteger('main_category_id')->nullable()->after('id');
            $table->foreignUuid('zone_id')->nullable()->after('main_category_id');
            $table->unsignedBigInteger('tab_id')->nullable()->after('zone_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn(['main_category_id', 'zone_id', 'tab_id']);
        });
    }
};
