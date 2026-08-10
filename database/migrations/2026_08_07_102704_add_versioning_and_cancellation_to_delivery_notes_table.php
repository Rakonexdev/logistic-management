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
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->unsignedInteger('version')->default(1)->after('status');
            $table->string('version_label')->nullable()->after('version');
            $table->foreignId('parent_dn_id')->nullable()->after('version_label')->constrained('delivery_notes')->nullOnDelete();
            $table->boolean('is_latest')->default(true)->after('parent_dn_id');
            $table->text('amendment_reason')->nullable()->after('is_latest');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->dropForeign(['parent_dn_id']);
            $table->dropColumn(['version', 'version_label', 'parent_dn_id', 'is_latest', 'amendment_reason']);
        });
    }
};
