<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WarehouseMasterController extends Controller
{
    public function index(Request $request): View
    {
        $companyId = strtoupper(trim((string) $request->query('company', '')));

        $warehouses = Warehouse::query()
            ->when($companyId !== '', fn ($q) => $q->where('company_id', $companyId))
            ->when($companyId === '', fn ($q) => $q->whereRaw('1 = 0'))
            ->orderByDesc('created_at')
            ->get();

        return view('masters.warehouses.index', [
            'warehouses' => $warehouses,
            'currentCompanyCode' => $companyId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $companyId = strtoupper(trim((string) $request->query('company', '')));
        if ($companyId === '') {
            return redirect()->route('masters.warehouses.index')->withErrors(['company' => 'Select a company (top right) before creating a warehouse.'])->withInput();
        }

        $validated = $request->validate([
            'warehouse_id' => [
                'required',
                'string',
                'max:100',
                Rule::unique('warehouses', 'warehouse_id')->where(fn ($q) => $q->where('company_id', $companyId)),
            ],
            'warehouse_name' => ['required', 'string', 'max:255'],
        ]);

        Warehouse::create([
            'company_id' => $companyId,
            'warehouse_id' => trim($validated['warehouse_id']),
            'warehouse_name' => trim($validated['warehouse_name']),
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('masters.warehouses.index', ['company' => $companyId])->with('status', 'Warehouse created successfully.');
    }

    public function destroy(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $companyId = strtoupper(trim((string) $request->query('company', '')));
        if ($companyId === '' || strtoupper((string) $warehouse->company_id) !== $companyId) {
            abort(403, 'Warehouse does not belong to the selected company.');
        }

        $warehouse->delete();

        return redirect()->route('masters.warehouses.index', ['company' => $companyId])->with('status', 'Warehouse deleted successfully.');
    }
}
