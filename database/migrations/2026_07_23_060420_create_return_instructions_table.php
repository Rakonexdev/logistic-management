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
        Schema::create('return_instructions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('return_ref')->unique();
            $table->string('customer_name');
            $table->string('pickup_address');
            $table->string('contact_person')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('status')->default('Created'); // Created, Driver Assigned, Picked Up, Stored, Shipped to END, Completed
            $table->string('driver_name')->nullable();
            $table->string('driver_vehicle')->nullable();
            $table->string('storing_location')->nullable();
            $table->timestamp('instruction_received_date')->nullable();
            $table->timestamp('picking_date')->nullable();
            $table->timestamp('storing_date')->nullable();
            $table->timestamp('shipped_back_date')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('courier_name')->nullable();
            $table->string('proof_document')->nullable();
            $table->decimal('shipping_charges', 10, 2)->nullable();
            $table->string('classification')->nullable(); // Re-stockable, Defective
            $table->string('inspection_status')->default('Pending Inspection'); // Pending Inspection, Passed, Failed
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_instructions');
    }
};
