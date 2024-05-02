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
        Schema::create('types_cancellations', function (Blueprint $table) {
            $table->id();
            $table->string('name_es'); //nombre español 
            $table->string('name_en'); //nombre ingles 
            $table->integer('status')->default(1)->index(); //nos indicara si esta activo el metodo de pago 2: inactivo, 0: cancelado 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('types_cancellations');
    }
};
