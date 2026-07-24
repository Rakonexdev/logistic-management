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
        Schema::create('rent_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('warehouse_name');
            $table->string('rent_month');
            $table->decimal('monthly_rent_amount', 12, 2)->default(1200.00);
            $table->decimal('utility_charges', 12, 2)->default(0.00);
            $table->decimal('total_amount', 12, 2)->default(1200.00);
            $table->date('due_date')->nullable();
            $table->string('status')->default('Unpaid');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rent_invoices');
    }
};
