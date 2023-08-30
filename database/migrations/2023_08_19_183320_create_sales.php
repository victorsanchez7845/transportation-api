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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reservation_id')->nullable();
            $table->unsignedBigInteger('sale_type_id')->default(1);
            $table->string('description');
            $table->integer('quantity');
            $table->decimal('total', 10, 2);
            $table->unsignedBigInteger('call_center_agent_id')->default(0);

            $table->index('reservation_id');
            $table->index('sale_type_id');
            $table->index('call_center_agent_id');
            //$table->foreign('sale_type_id')->references('id')->on('sales');
            //$table->foreign('reservation_id')->references('id')->on('reservations');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
