<?php

namespace App\Support;

use App\Models\Pool;

/**
 * PR line behaviour vs pool flags. Legacy backfill set `has_item_id` true when only `category_item`
 * had text; those pools are still category-driven and must not send ItemId to D365.
 */
final class PoolPurchReqMode
{
    /**
     * When true, PR lines must carry a real D365 item id and it is sent on the PurchReq line payload.
     */
    public static function requiresTypedItemId(Pool $pool): bool
    {
        if (! $pool->has_item_id) {
            return false;
        }

        $legacyItemId = trim((string) ($pool->item_id ?? ''));
        if ($legacyItemId === '' && trim((string) ($pool->category_item ?? '')) !== '') {
            return false;
        }

        return true;
    }
}
