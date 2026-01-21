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
        Schema::create('openpay_clients', function (Blueprint $table) {
            $table->id();
            $table->string('client_name', 255)->nullable(false);
            $table->string('client_email')->nullable(false);
            $table->string('client_openpay_id')->nullable(false);
            $table->json('client_data')->nullable(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('openpay_clients');
    }
};
