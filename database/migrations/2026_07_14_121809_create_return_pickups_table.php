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
        Schema::create('return_pickups', function (Blueprint $table) {
            $table->id();
            $table->string('return_ref')->unique();
            $table->string('driver')->nullable();
            $table->string('pickup_location');
            $table->string('product_sku');
            $table->integer('quantity');
            $table->integer('quantity_picked_up')->nullable();
            $table->string('status')->default('Pending Pickup'); // Pending Pickup, Pickup Started, Completed, Returned to Warehouse
            $table->string('classification')->nullable(); // Defective, Re-stockable
            $table->text('condition_data')->nullable();
            $table->string('photo_path')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_pickups');
    }
};
