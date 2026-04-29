<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\ItemCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ItemCategoryMasterController extends Controller
{
    public function index(Request $request)
    {
        $companies = Company::query()->orderBy('name')->get();
        $currentCompanyCode = strtoupper((string) $request->query('company', ''));
        $selectedCompany = $companies->first(function ($c) use ($currentCompanyCode) {
            return strtoupper((string) $c->company_id) === $currentCompanyCode;
        }) ?? $companies->first();

        if ($selectedCompany) {
            $this->ensureCompanyCategories((int) $selectedCompany->id);
        }

        $categories = ItemCategory::query()
            ->when($selectedCompany, function ($query) use ($selectedCompany) {
                $query->where('company_id', $selectedCompany->id);
            })
            ->orderBy('name')
            ->get();

        return view('masters.categories.index', [
            'companies' => $companies,
            'categories' => $categories,
            'currentCompanyCode' => strtoupper((string) ($selectedCompany->company_id ?? $currentCompanyCode)),
            'selectedCompanyId' => $selectedCompany?->id,
        ]);
    }

    public function store(Request $request)
    {
        $currentCompanyCode = strtoupper((string) $request->query('company', ''));
        $selectedCompany = Company::resolveFromMixed($currentCompanyCode);

        if (!$selectedCompany) {
            return redirect()
                ->back()
                ->withErrors(['company' => 'Select a company first from the top selector.'])
                ->withInput();
        }

        $validated = $request->validate([
            'item_category_id' => [
                'required',
                'string',
                'max:100',
                Rule::unique('item_categories', 'd365_id')->where(function ($query) use ($selectedCompany) {
                    $query->where('company_id', (int) $selectedCompany->id);
                }),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
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

    /**
     * Keep categories aligned across companies by creating missing IDs.
     */
    private function ensureCompanyCategories(int $companyId): void
    {
        $templates = ItemCategory::query()
            ->whereNotNull('d365_id')
            ->where('d365_id', '!=', '')
            ->select(['d365_id', 'name'])
            ->distinct()
            ->orderBy('d365_id')
            ->get();

        foreach ($templates as $template) {
            ItemCategory::query()->firstOrCreate(
                [
                    'company_id' => $companyId,
                    'd365_id' => $template->d365_id,
                ],
                [
                    'name' => $template->name,
                ]
            );
        }
    }
}
