<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOriginalFileNameColToConversationFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('conversation_files', function (Blueprint $table) {
            $table->string('original_file_name')->nullable()->after('stored_file_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('conversation_files', function (Blueprint $table) {
            $table->dropColumn('original_file_name');
        });
    }
}
