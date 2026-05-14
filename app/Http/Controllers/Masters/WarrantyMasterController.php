<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Warranty;
use App\Support\DataAreaId;
use App\Support\GlobalCompanySelection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WarrantyMasterController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        [, $companyCode] = GlobalCompanySelection::companiesAndSelectedDataAreaId($user, $request);

        $rows = Warranty::query()
            ->when($companyCode !== '', fn ($q) => DataAreaId::whereUpperTrimEquals($q, 'company_id', $companyCode))
            ->orderBy('warranty')
            ->get();

        return view('masters.warranty.index', [
            'rows' => $rows,
            'effectiveCompanyCode' => $companyCode,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        [, $companyCode] = GlobalCompanySelection::companiesAndSelectedDataAreaId($user, $request);

        if ($companyCode === '') {
            return redirect()
                ->route('masters.warranty.index')
                ->withErrors(['company_id' => 'Select a company before adding a warranty.']);
        }

        $request->merge([
            'company_id' => DataAreaId::normalize($companyCode),
        ]);

        $validated = $request->validate([
            'company_id' => ['required', 'string', 'max:100'],
            'warranty' => [
                'required',
                'string',
                'max:100',
                Rule::unique('warranties', 'warranty')->where(function ($q) use ($request) {
                    DataAreaId::whereUpperTrimEquals($q, 'company_id', (string) $request->input('company_id'));
                }),
            ],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        Warranty::create([
            'company_id' => $validated['company_id'],
            'warranty' => trim($validated['warranty']),
            'description' => isset($validated['description']) && trim((string) $validated['description']) !== '' ? trim((string) $validated['description']) : null,
            'created_by' => auth()->id(),
        ]);

        $params = ['company' => DataAreaId::normalize($validated['company_id'])];

        return redirect()->route('masters.warranty.index', $params)->with('status', 'Warranty saved.');
    }

    public function destroy(Request $request, Warranty $warranty): RedirectResponse
    {
        $user = $request->user();
        [, $companyCode] = GlobalCompanySelection::companiesAndSelectedDataAreaId($user, $request);

        if ($companyCode === '' || DataAreaId::normalize((string) $warranty->company_id) !== DataAreaId::normalize($companyCode)) {
            abort(403);
        }

        $warranty->delete();

        $params = ['company' => strtoupper((string) $companyCode)];

        return redirect()->route('masters.warranty.index', $params)->with('status', 'Warranty deleted.');
    }
}
