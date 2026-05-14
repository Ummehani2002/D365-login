<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Warranty;
use App\Support\DataAreaId;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WarrantyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Warranty::query()->orderBy('warranty');

        if ($request->filled('warranty')) {
            $query->where('warranty', trim((string) $request->input('warranty')));
        }

        $companyScope = $this->companyScopeFromRequest($request);
        if ($companyScope !== null && $companyScope !== '') {
            DataAreaId::whereUpperTrimEquals($query, 'company_id', $companyScope);
        }

        return response()->json([
            'status' => true,
            'message' => 'Warranty rows fetched successfully.',
            'data' => $query->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
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

        $companyId = DataAreaId::normalize($validated['company_id']);

        $row = Warranty::create([
            'company_id' => $companyId,
            'warranty' => trim($validated['warranty']),
            'description' => (isset($validated['description']) && trim((string) $validated['description']) !== '')
                ? trim((string) $validated['description'])
                : null,
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Warranty row created successfully.',
            'data' => $row,
        ], 201);
    }

    public function show(Request $request, Warranty $warranty): JsonResponse
    {
        if (! $this->rowMatchesCompanyScope($request, $warranty)) {
            return response()->json([
                'status' => false,
                'message' => 'Warranty not found.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Warranty fetched successfully.',
            'data' => $warranty,
        ]);
    }

    public function update(Request $request, Warranty $warranty): JsonResponse
    {
        if (! $this->rowMatchesCompanyScope($request, $warranty)) {
            return response()->json([
                'status' => false,
                'message' => 'Warranty not found.',
            ], 404);
        }

        $validated = $request->validate([
            'warranty' => [
                'required',
                'string',
                'max:100',
                Rule::unique('warranties', 'warranty')
                    ->where(function ($q) use ($warranty) {
                        DataAreaId::whereUpperTrimEquals($q, 'company_id', (string) $warranty->company_id);
                    })
                    ->ignore($warranty->id),
            ],
            'description' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        $data = ['warranty' => trim($validated['warranty'])];
        if (array_key_exists('description', $validated)) {
            $v = $validated['description'];
            $data['description'] = $v === null || $v === '' ? null : trim((string) $v);
        }
        $warranty->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Warranty updated successfully.',
            'data' => $warranty->fresh(),
        ]);
    }

    public function destroy(Request $request, Warranty $warranty): JsonResponse
    {
        if (! $this->rowMatchesCompanyScope($request, $warranty)) {
            return response()->json([
                'status' => false,
                'message' => 'Warranty not found.',
            ], 404);
        }

        $warranty->delete();

        return response()->json([
            'status' => true,
            'message' => 'Warranty deleted successfully.',
        ]);
    }

    private function rowMatchesCompanyScope(Request $request, Warranty $row): bool
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
                return DataAreaId::normalize($v);
            }
            $vq = trim((string) $request->query($key, ''));
            if ($vq !== '') {
                return DataAreaId::normalize($vq);
            }
        }

        return null;
    }
}
