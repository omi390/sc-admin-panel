<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCodeTextAndCodeImgToServicesTable extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->text('code_text')->nullable()->after('images');
            $table->string('code_img', 500)->nullable()->after('code_text');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['code_text', 'code_img']);
        });
    }
}
