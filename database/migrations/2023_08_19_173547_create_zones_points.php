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
        Schema::create('zones_points', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('zone_id')->nullable();
            $table->string('latitude');
            $table->string('longitude');
            $table->index('zone_id'); 
            //$table->foreign('zone_id')->references('id')->on('zones')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zones_points');
    }
};
