<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('item_categories', 'd365_id')) {
                $table->string('d365_id')->nullable()->after('company_id');
            }
        });

        Schema::table('items', function (Blueprint $table) {
            if (!Schema::hasColumn('items', 'd365_id')) {
                $table->string('d365_id')->nullable()->after('company_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (Schema::hasColumn('items', 'd365_id')) {
                $table->dropColumn('d365_id');
            }
        });

        Schema::table('item_categories', function (Blueprint $table) {
            if (Schema::hasColumn('item_categories', 'd365_id')) {
                $table->dropColumn('d365_id');
            }
        });
    }
};
