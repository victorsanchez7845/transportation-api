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
        Schema::create('reservations_follow_up', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reservation_id')->default(0);
            $table->string('name', 150);
            $table->text('text')->nullable();
            $table->enum('type', ['CLIENT', 'INTERN', 'ALL', 'HISTORY'])->default('INTERN');
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
        Schema::dropIfExists('reservations_follow_up');
    }
};
