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
        Schema::create('cheque_collections', function (Blueprint $table) {
            $table->id();
            $table->string('collection_ref')->unique();
            $table->string('customer_name');
            $table->string('collection_location');
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('Pending Collection'); // Pending Collection, Collected, Submitted, Issue Reported
            $table->string('photo_path')->nullable();
            $table->timestamp('submission_time')->nullable();
            $table->text('remarks')->nullable();
            $table->string('driver')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cheque_collections');
    }
};
