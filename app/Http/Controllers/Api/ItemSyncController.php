<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Item;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class ItemSyncController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::query()->with('company:id,d365_id,name');

        $company = $this->resolveCompany($request);
        if ($company) {
            $query->where('company_id', $company->id);
        }

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('d365_id', 'like', "%{$search}%")
                    ->orWhere('d365_item_id', 'like', "%{$search}%")
                    ->orWhere('item_name', 'like', "%{$search}%");
            });
        }

        $items = $query->latest()->limit(200)->get();

        return response()->json([
            'success' => true,
            'count' => $items->count(),
            'data' => $items,
        ]);
    }

    public function store(Request $request)
    {
        $company = $this->resolveCompany($request);
        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Provide valid company code in `company` or `company_id`.',
            ], 422);
        }

        $request->merge([
            'd365_id' => $request->input('d365_id', $request->input('item_id')),
        ]);

        $validated = $request->validate([
            'd365_id' => 'nullable|string|max:100',
            'd365_item_id' => 'nullable|string|max:50',
            'item_name' => 'required|string|max:255',
            'type' => 'nullable|string|max:50',
            'item_category_id' => [
                'nullable',
                'string',
                'max:255',
                Rule::exists('item_categories', 'name')->where(function ($query) use ($company) {
                    $query->where('company_id', (int) $company->id);
                }),
            ],
        ]);

        $resolvedD365Id = trim((string) ($validated['d365_id'] ?? $validated['d365_item_id'] ?? ''));
        if ($resolvedD365Id === '') {
            return response()->json([
                'success' => false,
                'message' => 'item_id is required (or provide d365_id / d365_item_id).',
            ], 422);
        }

        $item = Item::updateOrCreate(
            [
                'company_id' => $company->id,
                'd365_id' => $resolvedD365Id,
            ],
            [
                'company_id' => $company->id,
                'd365_id' => $resolvedD365Id,
                'd365_item_id' => $resolvedD365Id,
                'item_name' => $validated['item_name'],
                'type' => $validated['type'] ?? null,
                'item_category_id' => $validated['item_category_id'] ?? null,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Item synced successfully',
            'data' => $item,
        ]);
    }

    private function resolveCompany(Request $request): ?Company
    {
        if ($request->filled('company_id')) {
            return Company::resolveFromMixed($request->input('company_id'));
        }

        $companyCode = strtoupper((string) ($request->input('company') ?? $request->input('company_d365_id') ?? ''));
        if ($companyCode === '') {
            return null;
        }

        return Company::resolveFromMixed($companyCode);
    }
}
