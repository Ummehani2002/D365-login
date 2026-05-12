<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Support\DataAreaId;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WarehouseMasterController extends Controller
{
    public function index(Request $request): View
    {
        $companyCode = strtoupper(trim((string) $request->query('company', '')));

        $warehouses = Warehouse::query()
            ->when($companyCode !== '', fn ($q) => DataAreaId::whereUpperTrimEquals($q, 'company_id', $companyCode))
            ->orderByDesc('created_at')
            ->get();

        return view('masters.warehouses.index', ['warehouses' => $warehouses]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'company_id' => DataAreaId::normalize((string) $request->input('company_id')),
        ]);

        $validated = $request->validate([
            'company_id' => ['required', 'string', 'max:100'],
            'warehouse_id' => [
                'required',
                'string',
                'max:100',
                Rule::unique('warehouses', 'warehouse_id')->where(function ($q) use ($request) {
                    DataAreaId::whereUpperTrimEquals($q, 'company_id', (string) $request->input('company_id'));
                }),
            ],
            'warehouse_name' => ['required', 'string', 'max:255'],
        ]);

        Warehouse::create([
            'company_id' => $validated['company_id'],
            'warehouse_id' => trim($validated['warehouse_id']),
            'warehouse_name' => trim($validated['warehouse_name']),
            'created_by' => auth()->id(),
        ]);

        $params = ['company' => DataAreaId::normalize($validated['company_id'])];

        return redirect()->route('masters.warehouses.index', $params)->with('status', 'Warehouse created successfully.');
    }

    public function destroy(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $warehouse->delete();

        $params = ['company' => strtoupper((string) $warehouse->company_id)];

        return redirect()->route('masters.warehouses.index', $params)->with('status', 'Warehouse deleted successfully.');
    }
}
