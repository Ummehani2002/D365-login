<?php

namespace App\Support;

use App\Models\Pool;

/**
 * Per-pool PR field requirements. Presets override pool master flags for known pools.
 */
final class PoolPurchReqRequirements
{
    /** @var array<string, array<string, bool>> */
    private const PRESETS = [
        'NP_APO' => [
            'has_item_category' => true,
            'has_attachment' => true,
            'has_fd_location' => true,
        ],
        'P_LPO' => [
            'uses_project' => true,
            'has_item_category' => false,
            'requires_budget_resource' => true,
        ],
        'P_OPO' => [
            'uses_project' => true,
            'has_item_category' => false,
            'requires_budget_resource' => true,
        ],
    ];

    /**
     * @return array{
     *     uses_project: bool,
     *     uses_warehouse: bool,
     *     has_attachment: bool,
     *     has_item_category: bool,
     *     has_item_id: bool,
     *     has_fd_location: bool,
     *     requires_budget_resource: bool,
     *     requires_typed_item_id: bool
     * }
     */
    public static function effectiveFlags(Pool $pool): array
    {
        $preset = self::PRESETS[strtoupper(trim((string) $pool->pool_id))] ?? [];

        $resolve = static function (string $key, bool $poolValue) use ($preset): bool {
            if (array_key_exists($key, $preset)) {
                return (bool) $preset[$key];
            }

            return $poolValue;
        };

        return [
            'uses_project' => $resolve('uses_project', (bool) $pool->uses_project),
            'uses_warehouse' => (bool) $pool->uses_warehouse,
            'has_attachment' => $resolve('has_attachment', (bool) $pool->has_attachment),
            'has_item_category' => $resolve('has_item_category', (bool) $pool->has_item_category),
            'has_item_id' => (bool) $pool->has_item_id,
            'has_fd_location' => $resolve('has_fd_location', (bool) ($pool->getAttributes()['has_fd_location'] ?? false)),
            'requires_budget_resource' => $resolve('requires_budget_resource', false),
            'requires_typed_item_id' => PoolPurchReqMode::requiresTypedItemId($pool),
        ];
    }
}
