<?php

namespace App\Support;

/**
 * Legal entity / DataAreaId codes (e.g. PS, USMF): uppercase in storage and UI,
 * while inbound sync/API may send any case.
 */
final class DataAreaId
{
    public static function normalize(?string $code): string
    {
        return strtoupper(trim((string) ($code ?? '')));
    }

    /**
     * Match VARCHAR columns that store a company legal-entity code (case-insensitive for legacy rows).
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     */
    public static function whereUpperTrimEquals($query, string $column, string $companyCodeFromRequest): void
    {
        if (! in_array($column, ['company_id', 'company'], true)) {
            throw new \InvalidArgumentException('Unsupported column for legal-entity match.');
        }

        $n = self::normalize($companyCodeFromRequest);
        if ($n === '') {
            return;
        }

        $query->whereRaw('UPPER(TRIM('.$column.')) = ?', [$n]);
    }
}
