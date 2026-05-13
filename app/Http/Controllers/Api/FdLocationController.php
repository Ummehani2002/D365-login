<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FdLocation;
use App\Support\DataAreaId;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FdLocationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = FdLocation::query()->orderBy('fd_location_id');

        if ($request->filled('fd_location_id')) {
            $query->where('fd_location_id', trim((string) $request->input('fd_location_id')));
        }

        $companyScope = $this->companyScopeFromRequest($request);
        if ($companyScope !== null && $companyScope !== '') {
            DataAreaId::whereUpperTrimEquals($query, 'company_id', $companyScope);
        }

        return response()->json([
            'status' => true,
            'message' => 'FD locations fetched successfully.',
            'data' => $query->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $companyNorm = DataAreaId::normalize((string) $request->input('company_id'));

        $validated = $request->validate([
            'company_id' => ['required', 'string', 'max:100'],
            'fd_location_id' => [
                'required',
                'string',
                'max:100',
                Rule::unique('fd_locations', 'fd_location_id')->where(
                    fn ($q) => $q->where('company_id', $companyNorm)
                ),
            ],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $companyId = DataAreaId::normalize($validated['company_id']);

        $row = FdLocation::create([
            'company_id' => $companyId,
            'fd_location_id' => trim($validated['fd_location_id']),
            'description' => (isset($validated['description']) && trim((string) $validated['description']) !== '')
                ? trim((string) $validated['description'])
                : null,
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'FD location created successfully.',
            'data' => $row,
        ], 201);
    }

    public function show(Request $request, FdLocation $fd_location): JsonResponse
    {
        if (! $this->rowMatchesCompanyScope($request, $fd_location)) {
            return response()->json([
                'status' => false,
                'message' => 'FD location not found.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'FD location fetched successfully.',
            'data' => $fd_location,
        ]);
    }

    public function update(Request $request, FdLocation $fd_location): JsonResponse
    {
        if (! $this->rowMatchesCompanyScope($request, $fd_location)) {
            return response()->json([
                'status' => false,
                'message' => 'FD location not found.',
            ], 404);
        }

        $validated = $request->validate([
            'fd_location_id' => [
                'required',
                'string',
                'max:100',
                Rule::unique('fd_locations', 'fd_location_id')
                    ->where(fn ($q) => $q->where('company_id', $fd_location->company_id))
                    ->ignore($fd_location->id),
            ],
            'description' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        $data = ['fd_location_id' => trim($validated['fd_location_id'])];
        if (array_key_exists('description', $validated)) {
            $v = $validated['description'];
            $data['description'] = $v === null || $v === '' ? null : trim((string) $v);
        }
        $fd_location->update($data);

        return response()->json([
            'status' => true,
            'message' => 'FD location updated successfully.',
            'data' => $fd_location->fresh(),
        ]);
    }

    public function destroy(Request $request, FdLocation $fd_location): JsonResponse
    {
        if (! $this->rowMatchesCompanyScope($request, $fd_location)) {
            return response()->json([
                'status' => false,
                'message' => 'FD location not found.',
            ], 404);
        }

        $fd_location->delete();

        return response()->json([
            'status' => true,
            'message' => 'FD location deleted successfully.',
        ]);
    }

    private function rowMatchesCompanyScope(Request $request, FdLocation $row): bool
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
