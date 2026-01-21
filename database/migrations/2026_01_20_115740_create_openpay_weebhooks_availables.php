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
        Schema::create('openpay_weebhooks_available', function (Blueprint $table) {
            $table->id();
            $table->string('event_type',  100)->nullable(false);
            $table->string('verification_code', 100)->nullable(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('openpay_weebhooks_availables');
    }
};
