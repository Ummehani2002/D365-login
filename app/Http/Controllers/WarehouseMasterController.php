<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WarehouseMasterController extends Controller
{
    public function index(): View
    {
        $warehouses = Warehouse::query()
            ->orderByDesc('created_at')
            ->get();

        return view('masters.warehouses.index', [
            'warehouses' => $warehouses,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'warehouse_id' => ['required', 'string', 'max:100', 'unique:warehouses,warehouse_id'],
            'warehouse_name' => ['required', 'string', 'max:255'],
        ]);

        Warehouse::create([
            'warehouse_id' => trim($validated['warehouse_id']),
            'warehouse_name' => trim($validated['warehouse_name']),
            'created_by' => auth()->id(),
        ]);

        $company = strtoupper((string) $request->query('company', ''));
        $params = $company !== '' ? ['company' => $company] : [];

        return redirect()
            ->route('masters.warehouses.index', $params)
            ->with('status', 'Warehouse created successfully.');
    }

    public function destroy(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $warehouse->delete();

        $company = strtoupper((string) $request->query('company', ''));
        $params = $company !== '' ? ['company' => $company] : [];

        return redirect()
            ->route('masters.warehouses.index', $params)
            ->with('status', 'Warehouse deleted successfully.');
    }
}
