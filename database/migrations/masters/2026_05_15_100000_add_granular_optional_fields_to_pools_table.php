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
            if (! Schema::hasColumn('pools', 'project')) {
                $table->string('project', 500)->nullable();
            }
            if (! Schema::hasColumn('pools', 'warehouse')) {
                $table->string('warehouse', 500)->nullable();
            }
            if (! Schema::hasColumn('pools', 'item_category')) {
                $table->string('item_category', 500)->nullable();
            }
            if (! Schema::hasColumn('pools', 'item_id')) {
                $table->string('item_id', 200)->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('pools')) {
            return;
        }

        Schema::table('pools', function (Blueprint $table) {
            foreach (['project', 'warehouse', 'item_category', 'item_id'] as $col) {
                if (Schema::hasColumn('pools', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
