<?php

use App\Models\Product;
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
        // Clean up any empty strings and duplicate serial numbers in test database records to allow migration to pass
        Product::where('serial_number', '')->update(['serial_number' => null]);

        $duplicates = DB::table('products')
            ->select('serial_number')
            ->whereNotNull('serial_number')
            ->groupBy('serial_number')
            ->havingRaw('count(*) > 1')
            ->pluck('serial_number');

        if ($duplicates->isNotEmpty()) {
            Product::whereIn('serial_number', $duplicates)->update(['serial_number' => null]);
        }

        Schema::table('products', function (Blueprint $table) {
            $table->unique('serial_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['serial_number']);
        });
    }
};
