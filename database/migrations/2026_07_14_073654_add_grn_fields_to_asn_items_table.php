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
        Schema::table('asn_items', function (Blueprint $table) {
            $table->integer('received_qty')->nullable()->after('quantity');
            $table->integer('discrepancy_qty')->nullable()->after('received_qty');
            $table->string('discrepancy_reason')->nullable()->after('discrepancy_qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asn_items', function (Blueprint $table) {
            $table->dropColumn(['received_qty', 'discrepancy_qty', 'discrepancy_reason']);
        });
    }
};
