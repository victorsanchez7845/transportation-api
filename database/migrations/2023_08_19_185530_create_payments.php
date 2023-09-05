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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('description');            
            $table->decimal('total', 10, 2);
            $table->decimal('exchange_rate', 10, 2);
            $table->tinyInteger('request_payment')->default(0);
            $table->enum('payment_method', ['CASH', 'PAYPAL', 'CARD'])->default('CASH');
            $table->text('object')->nullable();
            $table->unsignedBigInteger('reservation_id')->nullable();
            $table->timestamps();
            
            $table->index('reservation_id');
            //$table->foreign('reservation_id')->references('id')->on('reservations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
