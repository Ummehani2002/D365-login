<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pools')) {
            return;
        }

        Schema::table('pools', function (Blueprint $table) {
            if (! Schema::hasColumn('pools', 'warehouse_company_id')) {
                $table->string('warehouse_company_id', 100)->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('pools')) {
            return;
        }

        Schema::table('pools', function (Blueprint $table) {
            if (Schema::hasColumn('pools', 'warehouse_company_id')) {
                $table->dropColumn('warehouse_company_id');
            }
        });
    }
};
