<?php

namespace App\Support;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Resolves the company list and selected D365 data area id for the current user + request,
 * matching the global company selector behaviour in {@see \App\Providers\AppServiceProvider}.
 */
final class GlobalCompanySelection
{
    /**
     * @return array{0: Collection<int, Company>, 1: string} [companies, selectedDataAreaId]
     */
    public static function companiesAndSelectedDataAreaId(?User $user, Request $request): array
    {
        if (! $user) {
            return [collect(), ''];
        }

        $companies = collect();
        $isSuperAdmin = $user->isSuperAdmin();
        $isAdmin = $user->isCompanyAdmin();
        $hasFullAccess = $isSuperAdmin || $isAdmin;

        $accessibleCodes = $hasFullAccess
            ? collect()
            : $user->accessibleCompanyD365Codes()
                ->map(fn (mixed $code) => strtoupper(trim((string) $code)))
                ->filter(fn (string $code) => $code !== '')
                ->unique()
                ->values();

        try {
            if (Schema::hasTable('companies')) {
                $query = Company::query()
                    ->select(['id', 'd365_id', 'name'])
                    ->whereNotNull('d365_id')
                    ->orderBy('name');

                if (! $hasFullAccess) {
                    if ($accessibleCodes->isEmpty()) {
                        $query->whereRaw('1 = 0');
                    } else {
                        $query->whereIn(DB::raw('UPPER(d365_id)'), $accessibleCodes->all());
                    }
                }

                $companies = $query->get();
            }
        } catch (Throwable) {
            $companies = collect();
        }

        $selectedCompany = strtoupper(trim((string) $request->query('company', '')));
        if (($selectedCompany === '' || ! $companies->contains(fn ($company) => strtoupper((string) $company->d365_id) === $selectedCompany)) && $companies->isNotEmpty()) {
            $preferred = $companies->first(fn ($company) => strtoupper((string) ($company->d365_id ?? '')) === 'ML')
                ?? $companies->first(fn ($company) => strtoupper((string) ($company->d365_id ?? '')) === 'PS')
                ?? $companies->first();
            $selectedCompany = strtoupper((string) ($preferred->d365_id ?? ''));
        }

        return [$companies, $selectedCompany];
    }
}
