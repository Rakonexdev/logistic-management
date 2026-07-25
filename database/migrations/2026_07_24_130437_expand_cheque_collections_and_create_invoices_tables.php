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
        Schema::table('cheque_collections', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            $table->decimal('amount_usd', 10, 2)->nullable()->after('amount');
            $table->string('cheque_number')->nullable()->after('amount_usd');
            $table->date('cheque_date')->nullable()->after('cheque_number');
            $table->string('po_reference')->nullable()->after('cheque_date');
            $table->string('so_reference')->nullable()->after('po_reference');
            $table->string('invoice_reference')->nullable()->after('so_reference');
            $table->decimal('collection_fee', 10, 2)->default(35.00)->after('invoice_reference');
        });

        Schema::create('cheque_collection_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('customer_name');
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->string('status')->default('Unpaid');
            $table->timestamps();
        });

        Schema::create('cheque_collection_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cheque_collection_invoice_id')->constrained('cheque_collection_invoices', 'id', 'cc_inv_items_inv_id_fk')->onDelete('cascade');
            $table->foreignId('cheque_collection_id')->nullable()->constrained('cheque_collections')->onDelete('set null');
            $table->string('collection_ref');
            $table->string('cheque_number')->nullable();
            $table->decimal('cheque_amount', 12, 2)->default(0.00);
            $table->decimal('collection_fee', 12, 2)->default(35.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cheque_collection_invoice_items');
        Schema::dropIfExists('cheque_collection_invoices');
        Schema::table('cheque_collections', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'user_id',
                'amount_usd',
                'cheque_number',
                'cheque_date',
                'po_reference',
                'so_reference',
                'invoice_reference',
                'collection_fee',
            ]);
        });
    }
};
