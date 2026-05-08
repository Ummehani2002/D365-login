<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\ItemCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ItemCategoryMasterController extends Controller
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

        $categories = collect();
        if ($selectedCompany) {
            $categoriesQuery = ItemCategory::query()->orderBy('name');
            if (Schema::hasColumn('item_categories', 'company_id')) {
                $categoriesQuery->where('company_id', $selectedCompany->id);
            }
            $categories = $categoriesQuery->get();
        }

        if ($selectedCompany && $categories->isEmpty()) {
            $templateCompanyId = null;
            if (Schema::hasColumn('item_categories', 'company_id')) {
                $templateCompanyId = ItemCategory::query()
                    ->whereNotNull('company_id')
                    ->whereNotNull('d365_id')
                    ->where('d365_id', '!=', '')
                    ->value('company_id');
            }

            if ($templateCompanyId) {
                $categories = ItemCategory::query()
                    ->where('company_id', $templateCompanyId)
                    ->orderBy('name')
                    ->get();
            }
        }

        return view('masters.categories.index', [
            'companies' => $companies,
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
            'item_category_id' => [
                'required', 'string', 'max:100',
                Rule::unique('item_categories', 'd365_id')->where(function ($query) use ($selectedCompany) {
                    $query->where('company_id', (int) $selectedCompany->id);
                }),
            ],
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('item_categories', 'name')->where(function ($query) use ($selectedCompany) {
                    $query->where('company_id', (int) $selectedCompany->id);
                }),
            ],
        ]);

        ItemCategory::create([
            'company_id' => $selectedCompany->id,
            'item_category_id' => $validated['item_category_id'],
            'name' => $validated['name'],
        ]);

        $company = strtoupper((string) $request->query('company', ''));
        $params = $company !== '' ? ['company' => $company] : [];

        return redirect()
            ->route('masters.categories.index', $params)
            ->with('status', 'Item category created successfully.');
    }
}
