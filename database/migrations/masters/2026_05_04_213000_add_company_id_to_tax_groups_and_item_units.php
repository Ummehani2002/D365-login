<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function hasIndex(string $table, string $indexName): bool
    {
        $rows = DB::select('SHOW INDEX FROM `'.$table.'` WHERE Key_name = ?', [$indexName]);

        return ! empty($rows);
    }

    public function up(): void
    {
        Schema::table('sales_tax_groups', function (Blueprint $table) {
            if (! Schema::hasColumn('sales_tax_groups', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->nullOnDelete();
            }
            if ($this->hasIndex('sales_tax_groups', 'sales_tax_groups_tax_group_id_unique')) {
                $table->dropUnique('sales_tax_groups_tax_group_id_unique');
            }
            if (! $this->hasIndex('sales_tax_groups', 'sales_tax_groups_company_id_tax_group_id_unique')) {
                $table->unique(['company_id', 'tax_group_id']);
            }
        });

        Schema::table('item_sales_tax_groups', function (Blueprint $table) {
            if (! Schema::hasColumn('item_sales_tax_groups', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->nullOnDelete();
            }
            if ($this->hasIndex('item_sales_tax_groups', 'item_sales_tax_groups_tax_item_group_unique')) {
                $table->dropUnique('item_sales_tax_groups_tax_item_group_unique');
            }
            if (! $this->hasIndex('item_sales_tax_groups', 'item_sales_tax_groups_company_id_tax_item_group_unique')) {
                $table->unique(['company_id', 'tax_item_group']);
            }
        });

        Schema::table('item_units', function (Blueprint $table) {
            if (! Schema::hasColumn('item_units', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->nullOnDelete();
            }
            if (! $this->hasIndex('item_units', 'item_units_item_id_index')) {
                $table->index('item_id', 'item_units_item_id_index');
            }
            if ($this->hasIndex('item_units', 'item_units_item_id_unit_id_unique')) {
                $table->dropUnique('item_units_item_id_unit_id_unique');
            }
            if (! $this->hasIndex('item_units', 'item_units_company_id_item_id_unit_id_unique')) {
                $table->unique(['company_id', 'item_id', 'unit_id']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('item_units', function (Blueprint $table) {
            if ($this->hasIndex('item_units', 'item_units_company_id_item_id_unit_id_unique')) {
                $table->dropUnique('item_units_company_id_item_id_unit_id_unique');
            }
            if (! $this->hasIndex('item_units', 'item_units_item_id_unit_id_unique')) {
                $table->unique(['item_id', 'unit_id']);
            }
            if ($this->hasIndex('item_units', 'item_units_item_id_index')) {
                $table->dropIndex('item_units_item_id_index');
            }
            if (Schema::hasColumn('item_units', 'company_id')) {
                if ($this->hasIndex('item_units', 'item_units_company_id_foreign')) {
                    $table->dropForeign('item_units_company_id_foreign');
                }
                $table->dropColumn('company_id');
            }
        });

        Schema::table('item_sales_tax_groups', function (Blueprint $table) {
            if ($this->hasIndex('item_sales_tax_groups', 'item_sales_tax_groups_company_id_tax_item_group_unique')) {
                $table->dropUnique('item_sales_tax_groups_company_id_tax_item_group_unique');
            }
            if (! $this->hasIndex('item_sales_tax_groups', 'item_sales_tax_groups_tax_item_group_unique')) {
                $table->unique('tax_item_group');
            }
            if (Schema::hasColumn('item_sales_tax_groups', 'company_id')) {
                if ($this->hasIndex('item_sales_tax_groups', 'item_sales_tax_groups_company_id_foreign')) {
                    $table->dropForeign('item_sales_tax_groups_company_id_foreign');
                }
                $table->dropColumn('company_id');
            }
        });

        Schema::table('sales_tax_groups', function (Blueprint $table) {
            if ($this->hasIndex('sales_tax_groups', 'sales_tax_groups_company_id_tax_group_id_unique')) {
                $table->dropUnique('sales_tax_groups_company_id_tax_group_id_unique');
            }
            if (! $this->hasIndex('sales_tax_groups', 'sales_tax_groups_tax_group_id_unique')) {
                $table->unique('tax_group_id');
            }
            if (Schema::hasColumn('sales_tax_groups', 'company_id')) {
                if ($this->hasIndex('sales_tax_groups', 'sales_tax_groups_company_id_foreign')) {
                    $table->dropForeign('sales_tax_groups_company_id_foreign');
                }
                $table->dropColumn('company_id');
            }
        });
    }
};
