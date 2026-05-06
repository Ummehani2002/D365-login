<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('user_code', 191)->nullable()->after('email');
            $table->uuid('telemetry_id')->nullable()->after('user_code');
            $table->string('provider', 512)->nullable()->after('telemetry_id');
            $table->boolean('enabled')->default(true)->after('provider');

            $table->unique('user_code');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['user_code']);
            $table->dropColumn(['user_code', 'telemetry_id', 'provider', 'enabled']);
        });
    }
};
