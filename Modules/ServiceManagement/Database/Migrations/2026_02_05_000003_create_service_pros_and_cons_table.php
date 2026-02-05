<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceProsAndConsTable extends Migration
{
    public function up(): void
    {
        Schema::create('service_pros_and_cons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('service_id')->constrained('services')->cascadeOnDelete();
            $table->string('title', 191)->nullable();
            $table->enum('prod_or_con', ['pros', 'con']);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_pros_and_cons');
    }
}
