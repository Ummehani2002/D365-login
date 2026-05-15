<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pools')) {
            return;
        }

        foreach (['P_LPO', 'P_OPO'] as $poolId) {
            DB::table('pools')
                ->whereRaw('UPPER(TRIM(pool_id)) = ?', [$poolId])
                ->update([
                    'uses_project' => true,
                    'has_item_category' => false,
                ]);
        }
    }

    public function down(): void
    {
        // Preset logic in PoolPurchReqRequirements remains the source of truth for these pools.
    }
};
