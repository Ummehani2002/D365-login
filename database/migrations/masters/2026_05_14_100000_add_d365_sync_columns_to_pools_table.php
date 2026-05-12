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
            if (! Schema::hasColumn('pools', 'project_warehouse')) {
                $table->string('project_warehouse', 500)->nullable()->after('name');
            }
            if (! Schema::hasColumn('pools', 'attachment')) {
                $table->text('attachment')->nullable()->after('project_warehouse');
            }
            if (! Schema::hasColumn('pools', 'category_item')) {
                $table->string('category_item', 500)->nullable()->after('attachment');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('pools')) {
            return;
        }

        Schema::table('pools', function (Blueprint $table) {
            foreach (['project_warehouse', 'attachment', 'category_item'] as $col) {
                if (Schema::hasColumn('pools', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
