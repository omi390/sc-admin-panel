<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class RenameServicesSectionsToServiceDetailSections extends Migration
{
    /**
     * Run the migrations.
     * Rename existing table from services_sections to service_detail_sections.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('services_sections') && !Schema::hasTable('service_detail_sections')) {
            Schema::rename('services_sections', 'service_detail_sections');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('service_detail_sections') && !Schema::hasTable('services_sections')) {
            Schema::rename('service_detail_sections', 'services_sections');
        }
    }
}
