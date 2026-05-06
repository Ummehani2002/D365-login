<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'user_code')) {
                $table->string('user_code', 191)->nullable()->unique()->after('email');
            }

            if (! Schema::hasColumn('users', 'provider')) {
                $table->string('provider', 512)->nullable()->after('user_code');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'provider')) {
                $table->dropColumn('provider');
            }

            if (Schema::hasColumn('users', 'user_code')) {
                $table->dropUnique('users_user_code_unique');
                $table->dropColumn('user_code');
            }
        });
    }
};

