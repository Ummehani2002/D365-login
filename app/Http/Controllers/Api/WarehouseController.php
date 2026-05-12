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

        if ($request->filled('company_id')) {
            $query->where('company_id', strtoupper(trim((string) $request->input('company_id'))));
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', trim((string) $request->input('warehouse_id')));
        }

        return response()->json([
            'status' => true,
            'message' => 'Warehouses fetched successfully.',
            'data' => $query->latest()->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $payload = $this->validatePayload($request);
        $payload['created_by'] = auth()->id();

        $warehouse = Warehouse::create($payload);

        return response()->json([
            'status' => true,
            'message' => 'Warehouse created successfully.',
            'data' => $warehouse,
        ], 201);
    }

    public function show(Request $request, string $warehouse): JsonResponse
    {
        $resolved = $this->resolveWarehouse($warehouse, $this->companyScopeFromRequest($request));

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
        $resolved = $this->resolveWarehouse($warehouse, $this->companyScopeFromRequest($request));

        if (! $resolved) {
            return response()->json([
                'status' => false,
                'message' => 'Warehouse not found.',
            ], 404);
        }

        $payload = $this->validatePayload($request, $resolved);
        unset($payload['created_by']);
        $resolved->update($payload);

        return response()->json([
            'status' => true,
            'message' => 'Warehouse updated successfully.',
            'data' => $resolved->fresh(),
        ]);
    }

    public function destroy(Request $request, string $warehouse): JsonResponse
    {
        $resolved = $this->resolveWarehouse($warehouse, $this->companyScopeFromRequest($request));

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

    private function resolveWarehouse(mixed $value, ?string $companyScope = null): ?Warehouse
    {
        if ($value === null || $value === '') {
            return null;
        }

        $needle = trim((string) $value);
        if ($needle === '') {
            return null;
        }

        if (preg_match('/^\d+$/', $needle)) {
            $byId = Warehouse::query()->find((int) $needle);
            if ($byId) {
                return $byId;
            }
        }

        $query = Warehouse::query()->where('warehouse_id', $needle);
        if ($companyScope !== null && $companyScope !== '') {
            $query->where('company_id', strtoupper($companyScope));
        }

        return $query->first();
    }

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

    private function validatePayload(Request $request, ?Warehouse $existing = null): array
    {
        $uniqueRule = Rule::unique('warehouses', 'warehouse_id')
            ->where(fn ($query) => $query->where(
                'company_id',
                strtoupper(trim((string) $request->input('company_id', '')))
            ));

        if ($existing) {
            $uniqueRule->ignore($existing->id);
        }

        $validated = $request->validate([
            'warehouse_id' => ['required', 'string', 'max:100', $uniqueRule],
            'warehouse_name' => ['required', 'string', 'max:255'],
            'company_id' => ['required', 'string', 'max:100'],
        ]);

        return [
            'warehouse_id' => trim($validated['warehouse_id']),
            'warehouse_name' => trim($validated['warehouse_name']),
            'company_id' => strtoupper(trim($validated['company_id'])),
        ];
    }
}
