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
        Schema::create('delivery_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('delivery_instruction_id')->constrained('delivery_instructions')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('customer_name');
            $table->string('end_user_name')->nullable();
            $table->string('so_reference')->nullable();
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->string('status')->default('Unpaid');
            $table->timestamps();
        });

        Schema::create('delivery_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_invoice_id')->constrained('delivery_invoices')->onDelete('cascade');
            $table->string('sku_code');
            $table->string('serial_number')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('charge_amount', 12, 2)->default(0.00);
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_invoice_items');
        Schema::dropIfExists('delivery_invoices');
    }
};
