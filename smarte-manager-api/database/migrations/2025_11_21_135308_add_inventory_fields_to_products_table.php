<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Add only if they don't already exist

            if (! Schema::hasColumn('products', 'current_stock')) {
                $table->decimal('current_stock', 10, 2)->default(0);
            }

            if (! Schema::hasColumn('products', 'average_cost')) {
                $table->decimal('average_cost', 10, 2)->default(0);
            }

            if (! Schema::hasColumn('products', 'min_stock')) {
                $table->decimal('min_stock', 10, 2)->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'current_stock')) {
                $table->dropColumn('current_stock');
            }
            if (Schema::hasColumn('products', 'average_cost')) {
                $table->dropColumn('average_cost');
            }
            if (Schema::hasColumn('products', 'min_stock')) {
                $table->dropColumn('min_stock');
            }
        });
    }
};
