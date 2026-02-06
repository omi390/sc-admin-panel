<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBadgeImageToServicesTable extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (Schema::hasColumn('services', 'code_img')) {
                $table->string('badge_image', 500)->nullable()->after('code_img');
            } else {
                $table->string('badge_image', 500)->nullable()->after('thumbnail');
            }
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('badge_image');
        });
    }
}
