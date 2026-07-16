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
        if (!Schema::hasColumn('delivery_instruction_items', 'description')) {
            Schema::table('delivery_instruction_items', function (Blueprint $table) {
                $table->string('description')->nullable()->after('sku_code');
            });
        }

        if (!Schema::hasColumn('delivery_note_items', 'description')) {
            Schema::table('delivery_note_items', function (Blueprint $table) {
                $table->string('description')->nullable()->after('sku_code');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('delivery_note_items', 'description')) {
            Schema::table('delivery_note_items', function (Blueprint $table) {
                $table->dropColumn('description');
            });
        }

        if (Schema::hasColumn('delivery_instruction_items', 'description')) {
            Schema::table('delivery_instruction_items', function (Blueprint $table) {
                $table->dropColumn('description');
            });
        }
    }
};
