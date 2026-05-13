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
            if (! Schema::hasColumn('budget_resource_codes', 'project')) {
                $table->string('project', 100)->nullable()->after('company_id');
            }
            if (! Schema::hasColumn('budget_resource_codes', 'quantity')) {
                $table->decimal('quantity', 18, 2)->nullable()->after('unit');
            }
            if (! Schema::hasColumn('budget_resource_codes', 'rate')) {
                $table->decimal('rate', 18, 2)->nullable()->after('quantity');
            }
            if (! Schema::hasColumn('budget_resource_codes', 'amount')) {
                $table->decimal('amount', 18, 2)->nullable()->after('rate');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('budget_resource_codes')) {
            return;
        }

        Schema::table('budget_resource_codes', function (Blueprint $table) {
            $drop = [];
            foreach (['project', 'quantity', 'rate', 'amount'] as $col) {
                if (Schema::hasColumn('budget_resource_codes', $col)) {
                    $drop[] = $col;
                }
            }
            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });
    }
};
