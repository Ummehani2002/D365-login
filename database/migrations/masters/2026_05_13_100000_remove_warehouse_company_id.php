<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reverts company-scoped warehouses if the optional company migration was applied.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('warehouses')) {
            return;
        }

        if (! Schema::hasColumn('warehouses', 'company_id')) {
            return;
        }

        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'warehouse_id']);
        });

        Schema::table('warehouses', function (Blueprint $table) {
            $table->unique('warehouse_id');
        });

        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn('company_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('warehouses')) {
            return;
        }

        if (Schema::hasColumn('warehouses', 'company_id')) {
            return;
        }

        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropUnique(['warehouse_id']);
        });

        Schema::table('warehouses', function (Blueprint $table) {
            $table->string('company_id', 100)->default('LEGACY')->after('id');
        });

        Schema::table('warehouses', function (Blueprint $table) {
            $table->unique(['company_id', 'warehouse_id']);
        });
    }
};
