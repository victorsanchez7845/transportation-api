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
        Schema::create('reservations_services', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('reservation_item_id')->nullable();
            $table->unsignedBigInteger('destination_service_id')->nullable();

            $table->string('from_name');
            $table->string('from_lat');
            $table->string('from_lng');        
            $table->unsignedBigInteger('from_zone')->nullable();

            $table->string('to_name');
            $table->string('to_lat');
            $table->string('to_lng');
            $table->unsignedBigInteger('to_zone')->nullable();

            $table->integer('distance_time'); //Seconds
            $table->string('distance_km');

            $table->enum('status', ['PENDING', 'COMPLETED', 'NOSHOW', 'CANCELLED'])->default('PENDING');
            $table->dateTime('pickup')->nullable();
            
            $table->string('flight_number')->nullable();
            $table->text('flight_data')->nullable();
            $table->integer('passengers');
            
            $table->index('reservation_item_id');
            $table->index('destination_service_id');
            $table->index('from_zone');
            $table->index('to_zone');

            //$table->foreign('destination_service_id')->references('id')->on('destination_services')->onDelete('cascade');
            //$table->foreign('reservation_item_id')->references('id')->on('reservations_items')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations_services');
    }
};
