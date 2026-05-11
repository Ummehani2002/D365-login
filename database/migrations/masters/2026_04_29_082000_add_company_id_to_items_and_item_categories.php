<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('item_categories', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->nullOnDelete();
            }
        });

        Schema::table('items', function (Blueprint $table) {
            if (!Schema::hasColumn('items', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (Schema::hasColumn('items', 'company_id')) {
                $table->dropConstrainedForeignId('company_id');
            }
        });

        Schema::table('item_categories', function (Blueprint $table) {
            if (Schema::hasColumn('item_categories', 'company_id')) {
                $table->dropConstrainedForeignId('company_id');
            }
        });
    }
};
