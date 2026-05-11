<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Earlier installs may have created sales_tax_groups with column sales_tax_group.
     */
    public function up(): void
    {
        if (! Schema::hasTable('sales_tax_groups')) {
            return;
        }
        if (Schema::hasColumn('sales_tax_groups', 'sales_tax_group') && ! Schema::hasColumn('sales_tax_groups', 'tax_group_id')) {
            Schema::table('sales_tax_groups', function (Blueprint $table) {
                $table->renameColumn('sales_tax_group', 'tax_group_id');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('sales_tax_groups')) {
            return;
        }
        if (Schema::hasColumn('sales_tax_groups', 'tax_group_id') && ! Schema::hasColumn('sales_tax_groups', 'sales_tax_group')) {
            Schema::table('sales_tax_groups', function (Blueprint $table) {
                $table->renameColumn('tax_group_id', 'sales_tax_group');
            });
        }
    }
};
