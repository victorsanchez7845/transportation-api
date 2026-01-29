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
        Schema::create('openpay_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('openpay_transaction_id',255)->nullable(false);
            $table->string('reservation_uuid', 150)->nullable(false);
            $table->string('status',255)->nullable(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('openpay_transactions');
    }
};
