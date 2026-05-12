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
            $table->dropUnique(['pool_id']);
        });

        Schema::table('pools', function (Blueprint $table) {
            $table->unique(['company_id', 'pool_id']);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('pools')) {
            return;
        }

        Schema::table('pools', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'pool_id']);
        });

        Schema::table('pools', function (Blueprint $table) {
            $table->unique('pool_id');
        });
    }
};
