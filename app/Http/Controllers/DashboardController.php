<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $companies = $this->scopedCompaniesQuery($user)
            ->orderBy('name')
            ->get();

        if ($companies->isEmpty()) {
            abort(403, 'You do not have access to any organization.');
        }

        $defaultCompany = $companies->first(function (Company $company) {
            return strtoupper((string) $company->company_id) === 'PS'
                || strtoupper((string) $company->name) === 'PS';
        });

        $fallbackCompany = $defaultCompany ?? $companies->first();
        $requestedCompanyCode = strtoupper(trim((string) $request->query('company', '')));
        $selectedCompany = $companies->first(function (Company $company) use ($requestedCompanyCode) {
            return strtoupper((string) $company->d365_id) === $requestedCompanyCode;
        }) ?? $fallbackCompany;

        if ($selectedCompany && strtoupper((string) $selectedCompany->d365_id) !== $requestedCompanyCode) {
            return redirect()->route('dashboard', [
                'company' => strtoupper((string) $selectedCompany->d365_id),
            ]);
        }

        return view('dashboard', [
            'companies' => $companies,
            'currentCompanyCode' => $selectedCompany?->d365_id,
        ]);
    }
}
