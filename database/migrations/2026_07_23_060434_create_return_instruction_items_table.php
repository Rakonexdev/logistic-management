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
        Schema::create('return_instruction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_instruction_id')->constrained('return_instructions')->onDelete('cascade');
            $table->string('sku_code');
            $table->string('description')->nullable();
            $table->integer('quantity');
            $table->text('serial_numbers')->nullable();
            $table->string('condition')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_instruction_items');
    }
};
