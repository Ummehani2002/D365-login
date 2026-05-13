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
            'quantity' => $request->input('quantity') === '' || $request->input('quantity') === null ? null : $request->input('quantity'),
            'rate' => $request->input('rate') === '' || $request->input('rate') === null ? null : $request->input('rate'),
            'amount' => $request->input('amount') === '' || $request->input('amount') === null ? null : $request->input('amount'),
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
            'project' => ['nullable', 'string', 'max:100'],
            'resource_type' => ['nullable', 'string', 'max:100'],
            'quantity' => ['nullable', 'numeric', 'min:0'],
            'rate' => ['nullable', 'numeric', 'min:0'],
            'amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        BudgetResourceCode::create([
            'company_id' => $validated['company_id'],
            'resource_code' => trim($validated['resource_code']),
            'description' => isset($validated['description']) && trim((string) $validated['description']) !== '' ? trim((string) $validated['description']) : null,
            'unit' => isset($validated['unit']) && trim((string) $validated['unit']) !== '' ? trim((string) $validated['unit']) : null,
            'project' => isset($validated['project']) && trim((string) $validated['project']) !== '' ? trim((string) $validated['project']) : null,
            'resource_type' => isset($validated['resource_type']) && trim((string) $validated['resource_type']) !== '' ? trim((string) $validated['resource_type']) : null,
            'quantity' => $validated['quantity'] ?? null,
            'rate' => $validated['rate'] ?? null,
            'amount' => $validated['amount'] ?? null,
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
