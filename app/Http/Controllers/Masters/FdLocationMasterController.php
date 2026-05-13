<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\FdLocation;
use App\Support\DataAreaId;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FdLocationMasterController extends Controller
{
    public function index(Request $request): View
    {
        $companyCode = strtoupper(trim((string) $request->query('company', '')));

        $rows = FdLocation::query()
            ->when($companyCode !== '', fn ($q) => DataAreaId::whereUpperTrimEquals($q, 'company_id', $companyCode))
            ->orderBy('fd_location_id')
            ->get();

        return view('masters.fd-locations.index', ['fdLocations' => $rows]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'company_id' => DataAreaId::normalize((string) $request->input('company_id')),
        ]);

        $validated = $request->validate([
            'company_id' => ['required', 'string', 'max:100'],
            'fd_location_id' => [
                'required',
                'string',
                'max:100',
                Rule::unique('fd_locations', 'fd_location_id')->where(function ($q) use ($request) {
                    DataAreaId::whereUpperTrimEquals($q, 'company_id', (string) $request->input('company_id'));
                }),
            ],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        FdLocation::create([
            'company_id' => $validated['company_id'],
            'fd_location_id' => trim($validated['fd_location_id']),
            'description' => isset($validated['description']) && trim((string) $validated['description']) !== '' ? trim((string) $validated['description']) : null,
            'created_by' => auth()->id(),
        ]);

        $params = ['company' => DataAreaId::normalize($validated['company_id'])];

        return redirect()->route('masters.fd-locations.index', $params)->with('status', 'FD location saved.');
    }

    public function destroy(Request $request, FdLocation $fdLocation): RedirectResponse
    {
        $fdLocation->delete();

        $params = ['company' => strtoupper((string) $fdLocation->company_id)];

        return redirect()->route('masters.fd-locations.index', $params)->with('status', 'FD location deleted.');
    }
}
