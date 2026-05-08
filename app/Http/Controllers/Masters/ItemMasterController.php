<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Item;
use App\Models\ItemCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ItemMasterController extends Controller
{
    public function index(Request $request)
    {
        $companies = $this->scopedCompaniesQuery($request->user())->orderBy('name')->get();
        if ($companies->isEmpty()) {
            abort(403, 'You do not have access to any organization.');
        }
        $currentCompanyCode = strtoupper((string) $request->query('company', ''));
        $selectedCompany = $companies->first(function ($c) use ($currentCompanyCode) {
            return strtoupper((string) $c->d365_id) === $currentCompanyCode;
        }) ?? $companies->first();

        $itemsQuery = Item::query()->orderByDesc('created_at');
        if ($selectedCompany && Schema::hasColumn('items', 'company_id')) {
            $itemsQuery->where('company_id', $selectedCompany->id);
        }
        $items = $itemsQuery->get();

        $categoriesQuery = ItemCategory::query()->orderBy('name');
        if ($selectedCompany && Schema::hasColumn('item_categories', 'company_id')) {
            $categoriesQuery->where('company_id', $selectedCompany->id);
        }
        $categories = $categoriesQuery->get();

        return view('masters.items.index', [
            'companies' => $companies,
            'items' => $items,
            'categories' => $categories,
            'currentCompanyCode' => strtoupper((string) ($selectedCompany->d365_id ?? $currentCompanyCode)),
            'selectedCompanyId' => $selectedCompany?->id,
        ]);
    }

    public function store(Request $request)
    {
        $currentCompanyCode = strtoupper((string) $request->query('company', ''));
        $selectedCompany = $this->resolveScopedCompany($request->user(), $currentCompanyCode);

        if (!$selectedCompany) {
            return redirect()
                ->back()
                ->withErrors(['company' => 'Select a company first from the top selector.'])
                ->withInput();
        }

        $validated = $request->validate([
            'item_id' => ['required', 'string', 'max:100'],
            'item_name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:50'],
            'item_category_id' => [
                'nullable', 'string', 'max:255',
                Rule::exists('item_categories', 'name')->where(function ($query) use ($selectedCompany) {
                    $query->where('company_id', (int) $selectedCompany->id);
                }),
            ],
        ]);

        Item::updateOrCreate(
            ['company_id' => $selectedCompany->id, 'd365_id' => $validated['item_id']],
            [
                'company_id' => $selectedCompany->id,
                'item_id' => $validated['item_id'],
                'item_name' => $validated['item_name'],
                'type' => $validated['type'] ?? null,
                'item_category_id' => $validated['item_category_id'] ?? null,
            ]
        );

        $company = strtoupper((string) $request->query('company', ''));
        $params = $company !== '' ? ['company' => $company] : [];

        return redirect()
            ->route('masters.items.index', $params)
            ->with('status', 'Item saved successfully.');
    }
}
