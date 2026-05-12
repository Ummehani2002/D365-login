<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purch_req_journals', function (Blueprint $table) {
            $table->string('project_id', 100)->nullable()->after('warehouse');
        });
    }

    public function down(): void
    {
        Schema::table('purch_req_journals', function (Blueprint $table) {
            $table->dropColumn('project_id');
        });
    }
};
