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
            $table->string('delivery_note_attachment')->nullable()->after('so_reference');
        });

        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->string('delivery_note_attachment')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_instructions', function (Blueprint $table) {
            $table->dropColumn('delivery_note_attachment');
        });

        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->dropColumn('delivery_note_attachment');
        });
    }
};
