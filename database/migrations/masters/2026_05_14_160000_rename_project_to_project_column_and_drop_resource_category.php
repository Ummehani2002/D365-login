<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('budget_resource_codes')) {
            return;
        }

        Schema::table('budget_resource_codes', function (Blueprint $table) {
            if (Schema::hasColumn('budget_resource_codes', 'resource_category')) {
                $table->dropColumn('resource_category');
            }
        });

        Schema::table('budget_resource_codes', function (Blueprint $table) {
            if (Schema::hasColumn('budget_resource_codes', 'project_id') && ! Schema::hasColumn('budget_resource_codes', 'project')) {
                $table->renameColumn('project_id', 'project');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('budget_resource_codes')) {
            return;
        }

        Schema::table('budget_resource_codes', function (Blueprint $table) {
            if (Schema::hasColumn('budget_resource_codes', 'project') && ! Schema::hasColumn('budget_resource_codes', 'project_id')) {
                $table->renameColumn('project', 'project_id');
            }
        });

        Schema::table('budget_resource_codes', function (Blueprint $table) {
            if (! Schema::hasColumn('budget_resource_codes', 'resource_category')) {
                $table->string('resource_category', 50)->nullable()->after('unit');
            }
        });
    }
};
