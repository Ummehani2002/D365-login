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
            if (! Schema::hasColumn('budget_resource_codes', 'resource_type')) {
                $table->string('resource_type', 100)->nullable()->after('project');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('budget_resource_codes')) {
            return;
        }

        Schema::table('budget_resource_codes', function (Blueprint $table) {
            if (Schema::hasColumn('budget_resource_codes', 'resource_type')) {
                $table->dropColumn('resource_type');
            }
        });
    }
};
