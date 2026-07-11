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
            $table->string('customer_name')->after('so_number')->default('');
            $table->string('designation')->after('customer_name')->nullable();
            $table->dropColumn('customer_destination');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->string('customer_destination')->after('so_number')->default('');
            $table->dropColumn(['customer_name', 'designation']);
        });
    }
};
