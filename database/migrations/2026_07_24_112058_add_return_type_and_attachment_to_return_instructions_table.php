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
        Schema::table('return_instructions', function (Blueprint $table) {
            $table->string('return_type')->default('Return to Warehouse')->after('customer_name');
            $table->string('attachment')->nullable()->after('remarks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('return_instructions', function (Blueprint $table) {
            $table->dropColumn(['return_type', 'attachment']);
        });
    }
};
