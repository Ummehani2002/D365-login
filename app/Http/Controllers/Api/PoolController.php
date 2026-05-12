<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pool;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PoolController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->normalizeLegacyPoolIdInput($request);

        $query = Pool::query();

        if ($request->filled('company_id')) {
            $query->where('company_id', strtoupper(trim((string) $request->input('company_id'))));
        }

        if ($request->filled('pool_id')) {
            $query->where('pool_id', trim((string) $request->input('pool_id')));
        }

        return response()->json([
            'status' => true,
            'message' => 'Pools fetched successfully.',
            'data' => $query->latest()->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->normalizeLegacyPoolIdInput($request);
        $payload = $this->validatePayload($request);
        $pool = Pool::create($payload);

        return response()->json([
            'status' => true,
            'message' => 'Pool created successfully.',
            'data' => $pool,
        ], 201);
    }

    public function show(Request $request, string $pool): JsonResponse
    {
        $resolved = $this->resolvePool($pool, $this->companyScopeFromRequest($request));

        if (! $resolved) {
            return response()->json([
                'status' => false,
                'message' => 'Pool not found.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Pool fetched successfully.',
            'data' => $resolved,
        ]);
    }

    public function update(Request $request, string $pool): JsonResponse
    {
        $this->normalizeLegacyPoolIdInput($request);
        $resolved = $this->resolvePool($pool, $this->companyScopeFromRequest($request));

        if (! $resolved) {
            return response()->json([
                'status' => false,
                'message' => 'Pool not found.',
            ], 404);
        }

        $payload = $this->validatePayload($request, $resolved);
        $resolved->update($payload);

        return response()->json([
            'status' => true,
            'message' => 'Pool updated successfully.',
            'data' => $resolved->fresh(),
        ]);
    }

    public function destroy(Request $request, string $pool): JsonResponse
    {
        $resolved = $this->resolvePool($pool, $this->companyScopeFromRequest($request));

        if (! $resolved) {
            return response()->json([
                'status' => false,
                'message' => 'Pool not found.',
            ], 404);
        }

        $resolved->delete();

        return response()->json([
            'status' => true,
            'message' => 'Pool deleted successfully.',
        ]);
    }

    public function syncFromD365(Request $request): JsonResponse
    {
        $this->normalizeLegacyPoolIdInput($request);

        $validated = $request->validate(array_merge([
            'pool_id' => ['required', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:255'],
            'company_id' => ['required', 'string', 'max:100'],
        ], $this->poolOptionalD365ValidationRules()));

        $companyId = strtoupper(trim($validated['company_id']));
        $poolId = trim($validated['pool_id']);

        $upsert = array_merge(
            ['name' => trim($validated['name'])],
            $this->mergeOptionalPoolD365FieldsFromRequest($request, $validated)
        );

        $pool = Pool::updateOrCreate(
            [
                'pool_id' => $poolId,
                'company_id' => $companyId,
            ],
            $upsert
        );

        return response()->json([
            'status' => true,
            'message' => 'Pool synced successfully.',
            'data' => $pool,
        ]);
    }

    /**
     * Accept legacy JSON key `d365_pool_id` as an alias for `pool_id`.
     */
    private function normalizeLegacyPoolIdInput(Request $request): void
    {
        if ($request->filled('d365_pool_id') && ! $request->filled('pool_id')) {
            $request->merge([
                'pool_id' => $request->input('d365_pool_id'),
            ]);
        }

        $queryBag = $request->query();
        if (isset($queryBag['d365_pool_id']) && ! isset($queryBag['pool_id'])) {
            $request->query->set('pool_id', (string) $queryBag['d365_pool_id']);
        }
    }

    private function resolvePool(mixed $value, ?string $companyScope = null): ?Pool
    {
        if ($value === null || $value === '') {
            return null;
        }

        $needle = trim((string) $value);
        if ($needle === '') {
            return null;
        }

        if (preg_match('/^\d+$/', $needle)) {
            $byId = Pool::query()->find((int) $needle);

            if ($byId) {
                return $byId;
            }
        }

        $query = Pool::query()->where('pool_id', $needle);
        if ($companyScope !== null && $companyScope !== '') {
            $query->where('company_id', strtoupper($companyScope));
        }

        return $query->first();
    }

    /**
     * Scope lookups by company when resolving by pool_id (not numeric id).
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

    private function validatePayload(Request $request, ?Pool $pool = null): array
    {
        $uniqueRule = Rule::unique('pools', 'pool_id')
            ->where(fn ($query) => $query->where(
                'company_id',
                strtoupper(trim((string) $request->input('company_id', '')))
            ));

        if ($pool) {
            $uniqueRule->ignore($pool->id);
        }

        $validated = $request->validate(array_merge([
            'pool_id' => ['required', 'string', 'max:100', $uniqueRule],
            'name' => ['required', 'string', 'max:255'],
            'company_id' => ['required', 'string', 'max:100'],
        ], $this->poolOptionalD365ValidationRules()));

        $out = [
            'pool_id' => trim($validated['pool_id']),
            'name' => trim($validated['name']),
            'company_id' => strtoupper(trim($validated['company_id'])),
        ];

        return array_merge($out, $this->mergeOptionalPoolD365FieldsFromValidated($validated));
    }

    /**
     * Optional D365 fields — include only keys you have for that pool. Omitted keys are not changed on PATCH/sync.
     *
     * @return array<string, array<int, string>>
     */
    private function poolOptionalD365ValidationRules(): array
    {
        return [
            'project' => ['sometimes', 'nullable', 'string', 'max:500'],
            'warehouse' => ['sometimes', 'nullable', 'string', 'max:500'],
            'warehouse_company_id' => ['sometimes', 'nullable', 'string', 'max:100'],
            'attachment' => ['sometimes', 'nullable', 'string', 'max:60000'],
            'item_category' => ['sometimes', 'nullable', 'string', 'max:500'],
            'item_id' => ['sometimes', 'nullable', 'string', 'max:200'],
            'project_warehouse' => ['sometimes', 'nullable', 'string', 'max:500'],
            'category_item' => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }

    /** @return array<string, string|null> */
    private function mergeOptionalPoolD365FieldsFromRequest(Request $request, array $validated): array
    {
        $out = [];
        foreach (array_keys($this->poolOptionalD365ValidationRules()) as $key) {
            if (! $request->has($key)) {
                continue;
            }
            $v = $validated[$key] ?? null;
            $trimmed = ($v !== null && trim((string) $v) !== '') ? trim((string) $v) : null;
            $out[$key] = ($key === 'warehouse_company_id' && $trimmed !== null) ? strtoupper($trimmed) : $trimmed;
        }

        return $out;
    }

    /** @return array<string, string|null> */
    private function mergeOptionalPoolD365FieldsFromValidated(array $validated): array
    {
        $out = [];
        foreach (array_keys($this->poolOptionalD365ValidationRules()) as $key) {
            if (! array_key_exists($key, $validated)) {
                continue;
            }
            $v = $validated[$key];
            $trimmed = ($v !== null && trim((string) $v) !== '') ? trim((string) $v) : null;
            $out[$key] = ($key === 'warehouse_company_id' && $trimmed !== null) ? strtoupper($trimmed) : $trimmed;
        }

        return $out;
    }
}
