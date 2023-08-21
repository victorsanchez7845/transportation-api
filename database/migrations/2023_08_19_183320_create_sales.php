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
            $table->string('description');
            $table->decimal('exchange_rate', 10, 2);
            $table->decimal('total', 10, 2);
            $table->unsignedBigInteger('user_id');
            $table->tinyInteger('system_only')->default(0); // Por defecto es 0 e indica que si se debe mostrar en panel de operación, si es 1 indica que no.
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
