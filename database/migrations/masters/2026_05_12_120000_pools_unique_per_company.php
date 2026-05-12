<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function hasIndex(string $table, string $indexName): bool
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            $rows = DB::select('SHOW INDEX FROM `'.$table.'` WHERE Key_name = ?', [$indexName]);

            return ! empty($rows);
        }

        return Schema::hasIndex($table, $indexName);
    }

    public function up(): void
    {
        if (! Schema::hasTable('pools')) {
            return;
        }

        if ($this->hasIndex('pools', 'pools_pool_id_unique')) {
            Schema::table('pools', function (Blueprint $table) {
                $table->dropUnique(['pool_id']);
            });
        }

        if (! $this->hasIndex('pools', 'pools_company_id_pool_id_unique')) {
            Schema::table('pools', function (Blueprint $table) {
                $table->unique(['company_id', 'pool_id']);
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('pools')) {
            return;
        }

        if ($this->hasIndex('pools', 'pools_company_id_pool_id_unique')) {
            Schema::table('pools', function (Blueprint $table) {
                $table->dropUnique(['company_id', 'pool_id']);
            });
        }

        if (! $this->hasIndex('pools', 'pools_pool_id_unique')) {
            Schema::table('pools', function (Blueprint $table) {
                $table->unique('pool_id');
            });
        }
    }
};
