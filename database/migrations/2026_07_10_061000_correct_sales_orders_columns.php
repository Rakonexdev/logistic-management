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
            // Rename 'destination' to 'designation' if it was created with the wrong name
            if (Schema::hasColumn('sales_orders', 'destination') && ! Schema::hasColumn('sales_orders', 'designation')) {
                $table->renameColumn('destination', 'designation');
            }

            // Drop the old combined column if it still exists
            if (Schema::hasColumn('sales_orders', 'customer_destination')) {
                $table->dropColumn('customer_destination');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            if (Schema::hasColumn('sales_orders', 'designation') && ! Schema::hasColumn('sales_orders', 'destination')) {
                $table->renameColumn('designation', 'destination');
            }

            if (! Schema::hasColumn('sales_orders', 'customer_destination')) {
                $table->string('customer_destination')->after('so_number')->default('');
            }
        });
    }
};
