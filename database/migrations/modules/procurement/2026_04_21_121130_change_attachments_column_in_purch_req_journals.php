<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purch_req_journals', function (Blueprint $table) {
            $table->dropColumn('attachments');
        });

        Schema::table('purch_req_journals', function (Blueprint $table) {
            $table->longText('attachments')->nullable()->after('lines');
        });
    }

    public function down(): void
    {
        Schema::table('purch_req_journals', function (Blueprint $table) {
            $table->dropColumn('attachments');
        });

        Schema::table('purch_req_journals', function (Blueprint $table) {
            $table->json('attachments')->nullable()->after('lines');
        });
    }
};
