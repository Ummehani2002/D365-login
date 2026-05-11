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
        if (! Schema::hasTable('pools')) {
            return;
        }

        if (Schema::hasColumn('pools', 'd365_pool_id') && ! Schema::hasColumn('pools', 'pool_id')) {
            Schema::table('pools', function (Blueprint $table) {
                $table->renameColumn('d365_pool_id', 'pool_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('pools')) {
            return;
        }

        if (Schema::hasColumn('pools', 'pool_id') && ! Schema::hasColumn('pools', 'd365_pool_id')) {
            Schema::table('pools', function (Blueprint $table) {
                $table->renameColumn('pool_id', 'd365_pool_id');
            });
        }
    }
};
