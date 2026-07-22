<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asn_items', function (Blueprint $table) {
            if (! Schema::hasColumn('asn_items', 'serial_numbers')) {
                $table->text('serial_numbers')->nullable()->after('quantity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('asn_items', function (Blueprint $table) {
            if (Schema::hasColumn('asn_items', 'serial_numbers')) {
                $table->dropColumn('serial_numbers');
            }
        });
    }
};
