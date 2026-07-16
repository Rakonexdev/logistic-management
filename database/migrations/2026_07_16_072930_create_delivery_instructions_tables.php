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
        Schema::create('delivery_instructions', function (Blueprint $table) {
            $table->id();
            $table->string('di_number')->unique();
            $table->string('customer_name');
            $table->string('delivery_address');
            $table->string('status')->default('pending'); // pending, partial, completed
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('delivery_instruction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_instruction_id')->constrained('delivery_instructions')->onDelete('cascade');
            $table->string('sku_code');
            $table->integer('quantity');
            $table->text('serial_numbers')->nullable(); // comma separated
            $table->integer('delivered_quantity')->default(0);
            $table->string('status')->default('pending'); // pending, partial, completed
            $table->timestamps();
        });

        Schema::create('delivery_notes', function (Blueprint $table) {
            $table->id();
            $table->string('dn_number')->unique();
            $table->foreignId('delivery_instruction_id')->constrained('delivery_instructions')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('delivery_note_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_note_id')->constrained('delivery_notes')->onDelete('cascade');
            $table->string('sku_code');
            $table->integer('quantity');
            $table->text('serial_numbers')->nullable(); // comma separated
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_note_items');
        Schema::dropIfExists('delivery_notes');
        Schema::dropIfExists('delivery_instruction_items');
        Schema::dropIfExists('delivery_instructions');
    }
};
