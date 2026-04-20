<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Services\D365CompanyService;
use Illuminate\Http\Request;
use Throwable;

class CompanyMasterController extends Controller
{
    public function index()
    {
        return view('masters.company.index', [
            'apiBearerToken' => (string) config('services.webapp.api_bearer_token'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'd365_id' => ['required', 'string', 'max:100', 'unique:companies,d365_id'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        Company::create([
            'd365_id' => $validated['d365_id'],
            'name' => $validated['name'],
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('masters.company.index')
            ->with('status', 'Company created successfully.');
    }

    public function syncFromD365(D365CompanyService $d365CompanyService)
    {
        try {
            $companies = $d365CompanyService->fetchCompanies();

            $inserted = 0;
            $updated = 0;

            foreach ($companies as $company) {
                $existing = Company::where('d365_id', $company['d365_id'])->first();

                if ($existing) {
                    $existing->update([
                        'name' => $company['name'],
                    ]);
                    $updated++;
                } else {
                    Company::create([
                        'd365_id' => $company['d365_id'],
                        'name' => $company['name'],
                        'created_by' => auth()->id(),
                    ]);
                    $inserted++;
                }
            }

            return redirect()
                ->route('masters.company.index')
                ->with('status', "D365 sync complete. Inserted: {$inserted}, Updated: {$updated}");
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route('masters.company.index')
                ->withErrors(['sync' => 'Failed to sync companies from D365. Check API settings and logs.']);
        }
    }

}
