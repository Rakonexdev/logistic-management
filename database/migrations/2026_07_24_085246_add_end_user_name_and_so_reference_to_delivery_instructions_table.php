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
        Schema::table('delivery_instructions', function (Blueprint $table) {
            $table->string('end_user_name')->nullable()->after('customer_name');
            $table->string('so_reference')->nullable()->after('end_user_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_instructions', function (Blueprint $table) {
            $table->dropColumn(['end_user_name', 'so_reference']);
        });
    }
};
