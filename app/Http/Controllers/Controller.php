<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Throwable;

abstract class Controller
{
    protected function allowedCompanyCodes(?User $user): ?Collection
    {
        if (! $user) {
            return collect();
        }

        // Temporary bypass: allow all authenticated users to access all organizations.
        return null;
    }

    protected function scopedCompaniesQuery(?User $user): Builder
    {
        $allowedCompanyCodes = $this->allowedCompanyCodes($user);

        return Company::query()
            ->select(['id', 'company_id', 'd365_id', 'name'])
            ->whereNotNull('d365_id')
            ->when($allowedCompanyCodes !== null, function (Builder $query) use ($allowedCompanyCodes) {
                $query->whereIn(\DB::raw('UPPER(d365_id)'), $allowedCompanyCodes->all());
            });
    }

    protected function resolveScopedCompany(?User $user, string $companyCode): ?Company
    {
        $candidate = strtoupper(trim($companyCode));
        if ($candidate === '') {
            return null;
        }

        $allowedCompanyCodes = $this->allowedCompanyCodes($user);
        if ($allowedCompanyCodes !== null && ! $allowedCompanyCodes->contains($candidate)) {
            return null;
        }

        return Company::resolveFromMixed($candidate);
    }

    protected function assertCompanyAccess(string $companyCode): void
    {
        $candidate = strtoupper(trim($companyCode));
        if ($candidate === '') {
            abort(403, 'Invalid organization.');
        }

        $allowedCompanyCodes = $this->allowedCompanyCodes(auth()->user());
        if ($allowedCompanyCodes === null) {
            return;
        }

        if (! $allowedCompanyCodes->contains($candidate)) {
            abort(403, 'You do not have access to this organization.');
        }
    }
}
