<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\BudgetResourceCode;
use App\Support\DataAreaId;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BudgetResourceCodeMasterController extends Controller
{
    public function index(Request $request): View
    {
        $companyCode = strtoupper(trim((string) $request->query('company', '')));

        $rows = BudgetResourceCode::query()
            ->when($companyCode !== '', fn ($q) => DataAreaId::whereUpperTrimEquals($q, 'company_id', $companyCode))
            ->orderBy('resource_code')
            ->get();

        return view('masters.budget-resource-codes.index', ['budgetResourceCodes' => $rows]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'company_id' => DataAreaId::normalize((string) $request->input('company_id')),
        ]);

        $validated = $request->validate([
            'company_id' => ['required', 'string', 'max:100'],
            'resource_code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('budget_resource_codes', 'resource_code')->where(function ($q) use ($request) {
                    DataAreaId::whereUpperTrimEquals($q, 'company_id', (string) $request->input('company_id'));
                }),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:30'],
            'resource_category' => ['nullable', 'string', 'max:50'],
        ]);

        BudgetResourceCode::create([
            'company_id' => $validated['company_id'],
            'resource_code' => trim($validated['resource_code']),
            'description' => isset($validated['description']) ? trim((string) $validated['description']) : null,
            'unit' => isset($validated['unit']) ? trim((string) $validated['unit']) : null,
            'resource_category' => isset($validated['resource_category']) ? trim((string) $validated['resource_category']) : null,
            'created_by' => auth()->id(),
        ]);

        $params = ['company' => DataAreaId::normalize($validated['company_id'])];

        return redirect()->route('masters.budget-resource-codes.index', $params)->with('status', 'Budget resource code saved.');
    }

    public function destroy(Request $request, BudgetResourceCode $budgetResourceCode): RedirectResponse
    {
        $budgetResourceCode->delete();

        $params = ['company' => strtoupper((string) $budgetResourceCode->company_id)];

        return redirect()->route('masters.budget-resource-codes.index', $params)->with('status', 'Budget resource code deleted.');
    }
}
