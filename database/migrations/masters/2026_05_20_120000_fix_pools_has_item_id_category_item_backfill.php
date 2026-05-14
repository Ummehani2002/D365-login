<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Undo incorrect backfill: has_item_id was set true when only legacy `category_item` had text.
     * Those pools do not use a D365 item id on PR lines.
     */
    public function up(): void
    {
        if (! Schema::hasTable('pools')) {
            return;
        }

        DB::table('pools')
            ->where('has_item_id', true)
            ->whereRaw("TRIM(COALESCE(item_id, '')) = ''")
            ->whereRaw("TRIM(COALESCE(category_item, '')) != ''")
            ->update(['has_item_id' => false]);
    }

    public function down(): void
    {
        // Non-reversible without storing prior flags.
    }
};
