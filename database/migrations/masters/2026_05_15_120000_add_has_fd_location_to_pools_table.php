<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pools')) {
            return;
        }

        Schema::table('pools', function (Blueprint $table) {
            if (! Schema::hasColumn('pools', 'has_fd_location')) {
                $table->boolean('has_fd_location')->default(false)->after('has_item_id');
            }
        });

        DB::table('pools')
            ->whereRaw('UPPER(TRIM(pool_id)) = ?', ['NP_APO'])
            ->update([
                'has_item_category' => true,
                'has_attachment' => true,
                'has_fd_location' => true,
            ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('pools')) {
            return;
        }

        Schema::table('pools', function (Blueprint $table) {
            if (Schema::hasColumn('pools', 'has_fd_location')) {
                $table->dropColumn('has_fd_location');
            }
        });
    }
};
