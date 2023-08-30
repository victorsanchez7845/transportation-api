<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('destination_services_translate', function (Blueprint $table) {
            $table->id();
            $table->string('lang');
            $table->string('translation');
            $table->unsignedBigInteger('destination_services_id')->nullable();
            $table->timestamps();
            $table->index('destination_services_id');
            //$table->foreign('destination_services_id')->references('id')->on('destination_services')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('destination_services_translate');
    }
};
