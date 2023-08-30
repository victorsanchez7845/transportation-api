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
        Schema::create('destination_mail', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('text')->nullable();
            $table->unsignedBigInteger('destination_id')->nullable();
            $table->timestamps();
            $table->index('destination_id');
            //$table->foreign('destination_id')->references('id')->on('destinations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('destination_mail');
    }
};
