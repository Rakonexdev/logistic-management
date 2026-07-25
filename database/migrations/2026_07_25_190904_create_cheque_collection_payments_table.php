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
        if (! Schema::hasColumn('cheque_collections', 'paid_amount')) {
            Schema::table('cheque_collections', function (Blueprint $table) {
                $table->decimal('paid_amount', 12, 2)->default(0.00)->after('amount');
            });
        }

        Schema::create('cheque_collection_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cheque_collection_id')->constrained('cheque_collections')->onDelete('cascade');
            $table->integer('payment_number')->default(1);
            $table->decimal('paid_amount', 12, 2)->default(0.00);
            $table->decimal('remaining_balance', 12, 2)->default(0.00);
            $table->string('cheque_number')->nullable();
            $table->date('cheque_date')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('driver')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cheque_collection_payments');
        if (Schema::hasColumn('cheque_collections', 'paid_amount')) {
            Schema::table('cheque_collections', function (Blueprint $table) {
                $table->dropColumn('paid_amount');
            });
        }
    }
};
