<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\DataAreaId;
use App\Models\Pool;
use Illuminate\Database\QueryException;
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
            DataAreaId::whereUpperTrimEquals($query, 'company_id', (string) $request->input('company_id'));
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

        try {
            $pool = Pool::create($payload);
        } catch (QueryException $e) {
            if ($this->isUniqueConstraintViolation($e)) {
                return response()->json([
                    'status' => false,
                    'message' => 'A pool with this Pool ID already exists for this company (or Pool ID is globally taken if your DB still uses the legacy unique index on pool_id only). Use PUT /api/pool/{pool} to update, or choose another Pool ID.',
                    'error' => 'duplicate_pool',
                ], 422);
            }
            throw $e;
        }

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

        try {
            $resolved->update($payload);
        } catch (QueryException $e) {
            if ($this->isUniqueConstraintViolation($e)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Another pool already uses this Pool ID for this company (or Pool ID conflicts with the legacy global unique index). Choose a different Pool ID.',
                    'error' => 'duplicate_pool',
                ], 422);
            }
            throw $e;
        }

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

    /**
     * Back-compat: DELETE /api/pool (singular) with id in query or JSON body — same as DELETE /api/pool/{pool}.
     */
    public function destroyAlias(Request $request): JsonResponse
    {
        $this->normalizeLegacyPoolIdInput($request);

        $poolKey = $request->input('pool_id')
            ?? $request->input('id')
            ?? $request->query('pool_id')
            ?? $request->query('id');

        if ($poolKey === null || trim((string) $poolKey) === '') {
            return response()->json([
                'status' => false,
                'message' => 'Provide which pool to delete: send `id` (numeric PK) or `pool_id` in the JSON body or query string; when deleting by `pool_id`, include `company_id` or `company` if the same pool_id exists for multiple companies.',
            ], 422);
        }

        return $this->destroy($request, trim((string) $poolKey));
    }

    public function syncFromD365(Request $request): JsonResponse
    {
        $this->normalizeLegacyPoolIdInput($request);
        $this->normalizePoolBooleanInputs($request);

        $validated = $request->validate(array_merge([
            'pool_id' => ['required', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:255'],
            'company_id' => ['required', 'string', 'max:100'],
        ], $this->poolBooleanFlagRules(), $this->poolOptionalStringValidationRules()));

        $companyId = strtoupper(trim($validated['company_id']));
        $poolId = trim($validated['pool_id']);

        $upsert = array_merge(
            ['name' => trim($validated['name'])],
            $this->mergeBooleanFlagsFromValidated($validated),
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

    /**
     * Manager sync: accept true/false, 1/0, yes/no, y/n, on/off (case-insensitive) then validate as boolean.
     */
    private function normalizePoolBooleanInputs(Request $request): void
    {
        // Accept friendly alias keys from manager payload and map them to DB flag columns.
        $aliases = [
            'project' => 'uses_project',
            'warehouse' => 'uses_warehouse',
            'attachment' => 'has_attachment',
            'item_category' => 'has_item_category',
            'item_id' => 'has_item_id',
            'fd_location' => 'has_fd_location',
        ];

        foreach ($aliases as $aliasKey => $targetKey) {
            if (! $request->exists($aliasKey) || $request->exists($targetKey)) {
                continue;
            }
            $parsedAlias = $this->parseYesNoToBool($request->input($aliasKey));
            if ($parsedAlias !== null) {
                $request->merge([$targetKey => $parsedAlias]);
            }
        }

        foreach ($this->poolBooleanFlagKeys() as $key) {
            if (! $request->exists($key)) {
                continue;
            }
            $parsed = $this->parseYesNoToBool($request->input($key));
            if ($parsed === null) {
                $request->request->remove($key);

                continue;
            }
            $request->merge([$key => $parsed]);
        }
    }

    private function parseYesNoToBool(mixed $value): ?bool
    {
        if ($value === null) {
            return null;
        }
        if (is_bool($value)) {
            return $value;
        }
        if (is_int($value) || is_float($value)) {
            if ((int) $value === 1) {
                return true;
            }
            if ((int) $value === 0) {
                return false;
            }

            return null;
        }
        $s = strtolower(trim((string) $value));
        if ($s === '') {
            return null;
        }
        if (in_array($s, ['1', 'true', 'yes', 'y', 'on'], true)) {
            return true;
        }
        if (in_array($s, ['0', 'false', 'no', 'n', 'off'], true)) {
            return false;
        }

        return null;
    }

    /** @return list<string> */
    private function poolBooleanFlagKeys(): array
    {
        return ['uses_project', 'uses_warehouse', 'has_attachment', 'has_item_category', 'has_item_id', 'has_fd_location'];
    }

    /** @return array<string, array<int, string>> */
    private function poolBooleanFlagRules(): array
    {
        $rules = [];
        foreach ($this->poolBooleanFlagKeys() as $key) {
            $rules[$key] = ['sometimes', 'boolean'];
        }

        return $rules;
    }

    /** @return array<string, bool> */
    private function mergeBooleanFlagsFromValidated(array $validated): array
    {
        $out = [];
        foreach ($this->poolBooleanFlagKeys() as $key) {
            if (! array_key_exists($key, $validated)) {
                continue;
            }
            $out[$key] = (bool) $validated[$key];
        }

        return $out;
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
            DataAreaId::whereUpperTrimEquals($query, 'company_id', $companyScope);
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
        $this->normalizePoolBooleanInputs($request);

        $uniqueRule = Rule::unique('pools', 'pool_id')
            ->where(function ($query) use ($request) {
                DataAreaId::whereUpperTrimEquals($query, 'company_id', (string) $request->input('company_id', ''));
            });

        if ($pool) {
            $uniqueRule->ignore($pool->id);
        }

        $validated = $request->validate(array_merge([
            'pool_id' => ['required', 'string', 'max:100', $uniqueRule],
            'name' => ['required', 'string', 'max:255'],
            'company_id' => ['required', 'string', 'max:100'],
        ], $this->poolBooleanFlagRules(), $this->poolOptionalStringValidationRules()));

        $out = [
            'pool_id' => trim($validated['pool_id']),
            'name' => trim($validated['name']),
            'company_id' => strtoupper(trim($validated['company_id'])),
        ];

        return array_merge(
            $out,
            $this->mergeBooleanFlagsFromValidated($validated),
            $this->mergeOptionalPoolD365FieldsFromValidated($validated)
        );
    }

    private function isUniqueConstraintViolation(QueryException $e): bool
    {
        $msg = strtolower($e->getMessage());
        if (str_contains($msg, 'duplicate') || str_contains($msg, 'unique constraint')) {
            return true;
        }
        $sqlState = (string) ($e->errorInfo[0] ?? '');
        // PostgreSQL unique_violation
        if ($sqlState === '23505') {
            return true;
        }
        $driverCode = $e->errorInfo[1] ?? null;
        // MySQL ER_DUP_ENTRY
        if ($driverCode === 1062) {
            return true;
        }
        // SQLite constraint
        if ($driverCode === 19 && str_contains($msg, 'unique')) {
            return true;
        }

        return false;
    }

    /**
     * Optional D365 text fields — include only keys you have for that row. Omitted keys are not changed on PATCH/sync.
     *
     * @return array<string, array<int, string>>
     */
    private function poolOptionalStringValidationRules(): array
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
        foreach (array_keys($this->poolOptionalStringValidationRules()) as $key) {
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
        foreach (array_keys($this->poolOptionalStringValidationRules()) as $key) {
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
