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
        Schema::create('reservation_services', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('reservation_id');
            $table->unsignedBigInteger('destination_service_id');

            $table->string('from_name');
            $table->string('from_lat');
            $table->string('from_lng');
            $table->string('from_km');
            $table->string('from_minutes');
            $table->unsignedBigInteger('from_zone');

            $table->string('to_name');
            $table->string('to_lat');
            $table->string('to_lng');
            $table->string('to_km');
            $table->string('to_minutes');
            $table->unsignedBigInteger('to_zone');

            $table->enum('status', ['PENDING', 'COMPLETED', 'NOSHOW', 'CANCELLED'])->default('PENDING');
            $table->dateTime('pickup')->nullable();
            
            $table->string('flight_number')->nullable();
            $table->text('flight_data')->nullable();

            $table->foreign('destination_service_id')->references('id')->on('destination_services')->onDelete('cascade');
            $table->foreign('reservation_id')->references('id')->on('reservations')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation_services');
    }
};
