<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSeoSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seo_settings', function (Blueprint $table) {
            $table->uuid('id')->primary()->index();
            $table->string('page_title')->nullable();
            $table->string('page_name')->nullable();
            $table->string('page_url')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_image')->nullable();
            $table->string('canonicals_url')->nullable();
            $table->string('index')->nullable();
            $table->string('no_follow')->nullable();
            $table->string('no_image_index')->nullable();
            $table->string('no_archive')->nullable();
            $table->string('no_snippet')->nullable();
            $table->string('max_snippet')->nullable();
            $table->string('max_snippet_value')->nullable();
            $table->string('max_video_preview')->nullable();
            $table->string('max_video_preview_value')->nullable();
            $table->string('max_image_preview')->nullable();
            $table->string('max_image_preview_value')->nullable();
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
        Schema::dropIfExists('seo_settings');
    }
}
