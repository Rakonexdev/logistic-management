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
            if (! Schema::hasColumn('delivery_notes', 'driver')) {
                $table->string('driver')->nullable();
            }
            if (! Schema::hasColumn('delivery_notes', 'vehicle')) {
                $table->string('vehicle')->nullable();
            }
            if (! Schema::hasColumn('delivery_notes', 'delivery_status')) {
                $table->string('delivery_status')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('delivery_notes', function (Blueprint $table) {
            if (Schema::hasColumn('delivery_notes', 'driver')) {
                $table->dropColumn('driver');
            }
            if (Schema::hasColumn('delivery_notes', 'vehicle')) {
                $table->dropColumn('vehicle');
            }
            if (Schema::hasColumn('delivery_notes', 'delivery_status')) {
                $table->dropColumn('delivery_status');
            }
        });
    }
};
