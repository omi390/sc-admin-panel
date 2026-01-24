<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameFileNameColToConversationFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('conversation_files', function(Blueprint $table) {
            $table->renameColumn('file_name', 'stored_file_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('conversation_files', function(Blueprint $table) {
            $table->renameColumn('stored_file_name', 'file_name');
        });
    }
}
