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
        Schema::create('advance_shipping_notes', function (Blueprint $table) {
            $table->id();
            $table->string('asn_reference')->unique();
            $table->string('airway_bill');
            $table->string('vendor_id');
            $table->text('remarks')->nullable();
            $table->string('airway_bill_path')->nullable();
            $table->string('additional_attachments_path')->nullable();
            $table->string('status')->default('draft'); // draft, submitted, processing, completed
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advance_shipping_notes');
    }
};
