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
        Schema::table('delivery_invoices', function (Blueprint $table) {
            $table->decimal('lump_sum_amount', 12, 2)->default(0.00)->after('so_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_invoices', function (Blueprint $table) {
            $table->dropColumn('lump_sum_amount');
        });
    }
};
