<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BudgetResourceCode;
use App\Support\DataAreaId;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BudgetResourceCodeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = BudgetResourceCode::query()->orderBy('resource_code');

        if ($request->filled('resource_code')) {
            $query->where('resource_code', trim((string) $request->input('resource_code')));
        }

        $companyScope = $this->companyScopeFromRequest($request);
        if ($companyScope !== null && $companyScope !== '') {
            DataAreaId::whereUpperTrimEquals($query, 'company_id', $companyScope);
        }

        return response()->json([
            'status' => true,
            'message' => 'Budget resource codes fetched successfully.',
            'data' => $query->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $companyNorm = DataAreaId::normalize((string) $request->input('company_id'));

        $validated = $request->validate([
            'company_id' => ['required', 'string', 'max:100'],
            'resource_code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('budget_resource_codes', 'resource_code')->where(
                    fn ($q) => $q->where('company_id', $companyNorm)
                ),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:30'],
            'project' => ['nullable', 'string', 'max:100'],
            'resource_type' => ['nullable', 'string', 'max:100'],
            'quantity' => ['nullable', 'numeric', 'min:0'],
            'rate' => ['nullable', 'numeric', 'min:0'],
            'amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $companyId = DataAreaId::normalize($validated['company_id']);

        $row = BudgetResourceCode::create([
            'company_id' => $companyId,
            'resource_code' => trim($validated['resource_code']),
            'description' => (isset($validated['description']) && trim((string) $validated['description']) !== '') ? trim((string) $validated['description']) : null,
            'unit' => (isset($validated['unit']) && trim((string) $validated['unit']) !== '') ? trim((string) $validated['unit']) : null,
            'project' => (isset($validated['project']) && trim((string) $validated['project']) !== '') ? trim((string) $validated['project']) : null,
            'resource_type' => (isset($validated['resource_type']) && trim((string) $validated['resource_type']) !== '') ? trim((string) $validated['resource_type']) : null,
            'quantity' => array_key_exists('quantity', $validated) ? $validated['quantity'] : null,
            'rate' => array_key_exists('rate', $validated) ? $validated['rate'] : null,
            'amount' => array_key_exists('amount', $validated) ? $validated['amount'] : null,
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Budget resource code created successfully.',
            'data' => $row,
        ], 201);
    }

    public function show(Request $request, BudgetResourceCode $budget_resource_code): JsonResponse
    {
        if (! $this->rowMatchesCompanyScope($request, $budget_resource_code)) {
            return response()->json([
                'status' => false,
                'message' => 'Budget resource code not found.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Budget resource code fetched successfully.',
            'data' => $budget_resource_code,
        ]);
    }

    public function update(Request $request, BudgetResourceCode $budget_resource_code): JsonResponse
    {
        if (! $this->rowMatchesCompanyScope($request, $budget_resource_code)) {
            return response()->json([
                'status' => false,
                'message' => 'Budget resource code not found.',
            ], 404);
        }

        $validated = $request->validate([
            'resource_code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('budget_resource_codes', 'resource_code')
                    ->where(fn ($q) => $q->where('company_id', $budget_resource_code->company_id))
                    ->ignore($budget_resource_code->id),
            ],
            'description' => ['sometimes', 'nullable', 'string', 'max:255'],
            'unit' => ['sometimes', 'nullable', 'string', 'max:30'],
            'project' => ['sometimes', 'nullable', 'string', 'max:100'],
            'resource_type' => ['sometimes', 'nullable', 'string', 'max:100'],
            'quantity' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'rate' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
        ]);

        $data = ['resource_code' => trim($validated['resource_code'])];
        foreach (['description', 'unit', 'project', 'resource_type'] as $field) {
            if (array_key_exists($field, $validated)) {
                $v = $validated[$field];
                $data[$field] = $v === null || $v === '' ? null : trim((string) $v);
            }
        }
        foreach (['quantity', 'rate', 'amount'] as $field) {
            if (array_key_exists($field, $validated)) {
                $v = $validated[$field];
                $data[$field] = $v === null || $v === '' ? null : $v;
            }
        }
        $budget_resource_code->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Budget resource code updated successfully.',
            'data' => $budget_resource_code->fresh(),
        ]);
    }

    public function destroy(Request $request, BudgetResourceCode $budget_resource_code): JsonResponse
    {
        if (! $this->rowMatchesCompanyScope($request, $budget_resource_code)) {
            return response()->json([
                'status' => false,
                'message' => 'Budget resource code not found.',
            ], 404);
        }

        $budget_resource_code->delete();

        return response()->json([
            'status' => true,
            'message' => 'Budget resource code deleted successfully.',
        ]);
    }

    private function rowMatchesCompanyScope(Request $request, BudgetResourceCode $row): bool
    {
        $companyScope = $this->companyScopeFromRequest($request);
        if ($companyScope === null || $companyScope === '') {
            return false;
        }

        return DataAreaId::normalize((string) $row->company_id) === $companyScope;
    }

    /**
     * Accepts `company_id` or `company` from query or body.
     */
    private function companyScopeFromRequest(Request $request): ?string
    {
        foreach (['company_id', 'company'] as $key) {
            $v = trim((string) $request->input($key, ''));
            if ($v !== '') {
                return strtoupper($v);
            }
            $vq = trim((string) $request->query($key, ''));
            if ($vq !== '') {
                return strtoupper($vq);
            }
        }

        return null;
    }
}
