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
        Schema::create('rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rate_group_id')->nullable();
            $table->unsignedBigInteger('destination_service_id')->nullable();
            $table->unsignedBigInteger('destination_id')->nullable();
            $table->unsignedBigInteger('zone_id')->nullable();       
            $table->decimal('one_way', 10, 2);
            $table->decimal('round_trip', 10, 2);
            $table->decimal('ow_12', 10, 2);
            $table->decimal('rt_12', 10, 2);
            $table->decimal('ow_37', 10, 2);
            $table->decimal('rt_37', 10, 2);
            $table->decimal('up_8_ow', 10, 2);
            $table->decimal('up_8_rt', 10, 2);
            $table->timestamps();
            $table->index('rate_group_id');
            $table->index('destination_service_id');
            $table->index('destination_id');
            $table->index('zone_id');
            //$table->foreign('destination_service_id')->references('id')->on('destination_services')->onDelete('cascade');
            //$table->foreign('rate_group_id')->references('id')->on('rates_groups');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rates');
    }
};
