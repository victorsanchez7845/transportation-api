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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();           
            $table->string('client_first_name');
            $table->string('client_last_name');
            $table->string('client_email');
            $table->string('client_phone');          
            $table->enum('currency', ['USD', 'MXN'])->default('USD');
            $table->enum('language', ['en', 'es'])->default('en');
            $table->string('rate_group');
            $table->tinyInteger('is_cancelled')->default(0);            
            $table->tinyInteger('is_commissionable')->default(1);            

            $table->unsignedBigInteger('site_id')->nullable();
            $table->unsignedBigInteger('destination_id')->nullable();
            
            $table->index('site_id');
            $table->index('destination_id');

            //$table->foreign('site_id')->references('id')->on('sites');
            //$table->foreign('destination_id')->references('id')->on('destinations')->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
