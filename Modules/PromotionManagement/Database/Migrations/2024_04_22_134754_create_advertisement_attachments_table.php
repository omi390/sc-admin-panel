<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdvertisementAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advertisement_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('advertisement_id')->nullable();
            $table->string('file_extension_type')->nullable();
            $table->string('file_name')->nullable();
            $table->string('type')->nullable()->comment('promotional_video, provider_profile_image, provider_cover_image');
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
        Schema::dropIfExists('advertisement_attachments');
    }
}
