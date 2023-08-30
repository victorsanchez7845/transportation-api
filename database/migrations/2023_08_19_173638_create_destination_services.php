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
        Schema::create('destination_services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->tinyInteger('passengers')->default(3);
            $table->tinyInteger('luggage')->default(1);
            $table->tinyInteger('order')->default(10);
            $table->unsignedBigInteger('destination_id')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->string('image_url');
            $table->enum('price_type', ['vehicle', 'passenger', 'shared'])->default('vehicle');
            $table->timestamps();
            $table->index('destination_id'); 
            //$table->foreign('destination_id')->references('id')->on('destinations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('destination_services');
    }
};
