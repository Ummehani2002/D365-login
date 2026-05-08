<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Throwable;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            $companies = $this->scopedCompaniesQuery($user)
                ->orderBy('name')
                ->get();

            if ($companies->isEmpty()) {
                return view('dashboard', [
                    'companies' => collect(),
                    'currentCompanyCode' => null,
                ])->with('warning', 'No organization access found for your account. Please contact admin.');
            }

            $defaultCompany = $companies->first(function (Company $company) {
                return strtoupper((string) $company->d365_id) === 'PS';
            });

            $fallbackCompany = $defaultCompany
                ?? $companies->first(fn (Company $company) => strtoupper((string) $company->d365_id) === 'ML')
                ?? $companies->first();
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
        } catch (Throwable $e) {
            report($e);

            return view('dashboard', [
                'companies' => collect(),
                'currentCompanyCode' => null,
            ])->with('warning', 'Dashboard loaded in safe mode. Please contact admin to verify user-company setup.');
        }
    }
}
