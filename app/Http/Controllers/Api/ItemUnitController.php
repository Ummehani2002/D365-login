<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Item;
use App\Models\ItemUnit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ItemUnitController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $company = $this->resolveScopedCompany(
            $request->user(),
            (string) $request->query('company', $request->query('company_id', ''))
        );

        return response()->json([
            'status' => true,
            'message' => 'Item units fetched successfully.',
            'data' => ItemUnit::query()
                ->when($company, fn ($q) => $q->where('company_id', $company->id))
                ->with(['item', 'company:id,d365_id,name'])
                ->latest()
                ->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $company = $this->resolveScopedCompany(
            $request->user(),
            (string) $request->input('company_id', $request->query('company', $request->query('company_id', '')))
        );
        if (! $company) {
            return response()->json([
                'status' => false,
                'message' => 'company_id is required, must exist, and must be accessible.',
            ], 422);
        }

        $input = $request->all();
        if (array_key_exists('defination', $input) && ! array_key_exists('definition', $input)) {
            $request->merge(['definition' => $input['defination']]);
        }

        $validated = $request->validate([
            'unit_id' => ['required', 'string', 'max:50'],
            'unit_name' => ['required', 'string', 'max:255'],
            'definition' => ['nullable', 'string', 'max:5000'],
            'item_id' => ['nullable', 'integer', 'exists:items,id'],
            'item_d365_id' => ['nullable', 'string', 'max:100'],
        ]);

        if (empty($validated['item_id']) && empty($validated['item_d365_id'])) {
            throw ValidationException::withMessages([
                'item_id' => ['Provide item_id (items table id from Item master) or item_d365_id (same code as in Item master).'],
            ]);
        }

        $itemPk = (int) ($validated['item_id'] ?? 0);
        if ($itemPk === 0) {
            $code = strtoupper(trim((string) $validated['item_d365_id']));
            $item = Item::query()
                ->where('company_id', $company->id)
                ->where(function ($q) use ($code) {
                    $q->whereRaw('UPPER(TRIM(d365_id)) = ?', [$code])
                        ->orWhereRaw('UPPER(TRIM(d365_item_id)) = ?', [$code]);
                })
                ->first();
            if ($item === null) {
                throw ValidationException::withMessages([
                    'item_d365_id' => ['No item found in Item master with this id.'],
                ]);
            }
            $itemPk = (int) $item->id;
        }

        $itemCompanyId = (int) Item::query()->whereKey($itemPk)->value('company_id');
        if ($itemCompanyId !== (int) $company->id) {
            throw ValidationException::withMessages([
                'item_id' => ['Selected item does not belong to the selected company.'],
            ]);
        }

        $unitId = strtoupper(trim($validated['unit_id']));

        $row = ItemUnit::updateOrCreate(
            [
                'company_id' => $company->id,
                'item_id' => $itemPk,
                'unit_id' => $unitId,
            ],
            [
                'unit_name' => trim($validated['unit_name']),
                'definition' => isset($validated['definition']) && $validated['definition'] !== ''
                    ? trim($validated['definition'])
                    : null,
            ],
        );

        if ($row->wasRecentlyCreated && auth()->check()) {
            $row->forceFill(['created_by' => auth()->id()])->save();
        }

        return response()->json([
            'status' => true,
            'message' => $row->wasRecentlyCreated
                ? 'Item unit created successfully.'
                : 'Item unit updated successfully.',
            'data' => $row->fresh()->load('item'),
        ], $row->wasRecentlyCreated ? 201 : 200);
    }

    public function destroy(ItemUnit $item_unit): JsonResponse
    {
        $companyCode = (string) Company::query()->whereKey($item_unit->company_id)->value('d365_id');
        $this->assertCompanyAccess($companyCode);
        $item_unit->delete();

        return response()->json([
            'status' => true,
            'message' => 'Item unit deleted successfully.',
        ]);
    }
}
