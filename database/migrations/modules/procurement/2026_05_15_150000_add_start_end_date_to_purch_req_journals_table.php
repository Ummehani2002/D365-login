<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('purch_req_journals')) {
            return;
        }

        Schema::table('purch_req_journals', function (Blueprint $table) {
            if (! Schema::hasColumn('purch_req_journals', 'start_date')) {
                $table->date('start_date')->nullable()->after('pr_date');
            }
            if (! Schema::hasColumn('purch_req_journals', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('purch_req_journals')) {
            return;
        }

        Schema::table('purch_req_journals', function (Blueprint $table) {
            if (Schema::hasColumn('purch_req_journals', 'end_date')) {
                $table->dropColumn('end_date');
            }
            if (Schema::hasColumn('purch_req_journals', 'start_date')) {
                $table->dropColumn('start_date');
            }
        });
    }
};
