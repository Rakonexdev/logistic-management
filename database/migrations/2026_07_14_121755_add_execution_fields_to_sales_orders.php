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
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->timestamp('arrived_at')->nullable();
            $table->string('recipient_name')->nullable();
            $table->string('signed_proof_path')->nullable();
            $table->string('delivery_photo_path')->nullable();
            $table->timestamp('delivery_completed_at')->nullable();
            $table->text('delivery_remarks')->nullable();
            $table->text('delivery_issue')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn([
                'arrived_at',
                'recipient_name',
                'signed_proof_path',
                'delivery_photo_path',
                'delivery_completed_at',
                'delivery_remarks',
                'delivery_issue',
            ]);
        });
    }
};
