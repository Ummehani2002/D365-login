<?php

namespace App\Support;

use App\Models\Pool;

/**
 * Optional item-category restriction text stored on pools (D365 sync / master).
 */
final class PoolCategoryAllowlist
{
    /**
     * @return list<string> unique non-empty lowercase tokens
     */
    public static function tokensFromPool(?Pool $pool): array
    {
        if ($pool === null) {
            return [];
        }

        $raw = trim((string) ($pool->item_category ?? ''));
        if ($raw !== '') {
            $raw .= "\n";
        }
        $raw .= trim((string) ($pool->category_item ?? ''));

        if ($raw === '') {
            return [];
        }

        $parts = preg_split('/[\s,;|]+/u', $raw, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $out = [];
        foreach ($parts as $p) {
            $t = strtolower(trim((string) $p));
            if ($t !== '') {
                $out[$t] = true;
            }
        }

        return array_keys($out);
    }

    /**
     * @param  list<string>  $tokens  lowercase tokens from {@see tokensFromPool()}
     */
    public static function categoryMatchesAllowlist(string $categoryId, string $categoryName, array $tokens): bool
    {
        if ($tokens === []) {
            return true;
        }

        $idL = strtolower(trim($categoryId));
        $nameL = strtolower(trim($categoryName));

        foreach ($tokens as $t) {
            if ($t === '') {
                continue;
            }
            if ($idL !== '' && ($idL === $t || str_starts_with($idL, $t))) {
                return true;
            }
            if ($nameL !== '' && $nameL === $t) {
                return true;
            }
            if (strlen($t) >= 3 && $nameL !== '' && str_contains($nameL, $t)) {
                return true;
            }
        }

        return false;
    }
}
