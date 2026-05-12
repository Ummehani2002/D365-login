<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WarehouseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Warehouse::query();

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', trim((string) $request->input('warehouse_id')));
        }

        $companyScope = $this->companyScopeFromRequest($request);
        if ($companyScope !== null && $companyScope !== '') {
            $query->where('company_id', $companyScope);
        }

        return response()->json([
            'status' => true,
            'message' => 'Warehouses fetched successfully.',
            'data' => $query->latest()->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => ['required', 'string', 'max:100'],
            'warehouse_id' => [
                'required',
                'string',
                'max:100',
                Rule::unique('warehouses', 'warehouse_id')->where(
                    fn ($q) => $q->where(
                        'company_id',
                        strtoupper(trim((string) $request->input('company_id')))
                    )
                ),
            ],
            'warehouse_name' => ['required', 'string', 'max:255'],
        ]);

        $companyId = strtoupper(trim($validated['company_id']));

        $warehouse = Warehouse::create([
            'company_id' => $companyId,
            'warehouse_id' => trim($validated['warehouse_id']),
            'warehouse_name' => trim($validated['warehouse_name']),
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Warehouse created successfully.',
            'data' => $warehouse,
        ], 201);
    }

    public function show(Request $request, string $warehouse): JsonResponse
    {
        $resolved = $this->resolveWarehouse($request, $warehouse);

        if (! $resolved) {
            return response()->json([
                'status' => false,
                'message' => 'Warehouse not found.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Warehouse fetched successfully.',
            'data' => $resolved,
        ]);
    }

    public function update(Request $request, string $warehouse): JsonResponse
    {
        $resolved = $this->resolveWarehouse($request, $warehouse);

        if (! $resolved) {
            return response()->json([
                'status' => false,
                'message' => 'Warehouse not found.',
            ], 404);
        }

        $validated = $request->validate([
            'warehouse_id' => [
                'required',
                'string',
                'max:100',
                Rule::unique('warehouses', 'warehouse_id')
                    ->where(fn ($q) => $q->where('company_id', $resolved->company_id))
                    ->ignore($resolved->id),
            ],
            'warehouse_name' => ['required', 'string', 'max:255'],
        ]);

        $resolved->update([
            'warehouse_id' => trim($validated['warehouse_id']),
            'warehouse_name' => trim($validated['warehouse_name']),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Warehouse updated successfully.',
            'data' => $resolved->fresh(),
        ]);
    }

    public function destroy(Request $request, string $warehouse): JsonResponse
    {
        $resolved = $this->resolveWarehouse($request, $warehouse);

        if (! $resolved) {
            return response()->json([
                'status' => false,
                'message' => 'Warehouse not found.',
            ], 404);
        }

        $resolved->delete();

        return response()->json([
            'status' => true,
            'message' => 'Warehouse deleted successfully.',
        ]);
    }

    private function resolveWarehouse(Request $request, mixed $value): ?Warehouse
    {
        if ($value === null || $value === '') {
            return null;
        }

        $needle = trim((string) $value);
        if ($needle === '') {
            return null;
        }

        $companyScope = $this->companyScopeFromRequest($request);

        if (preg_match('/^\d+$/', $needle)) {
            $byId = Warehouse::query()->find((int) $needle);
            if ($byId) {
                if ($companyScope !== null && $companyScope !== ''
                    && strtoupper((string) $byId->company_id) !== $companyScope) {
                    return null;
                }

                return $byId;
            }
        }

        if ($companyScope === null || $companyScope === '') {
            return null;
        }

        return Warehouse::query()
            ->where('warehouse_id', $needle)
            ->where('company_id', $companyScope)
            ->first();
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
