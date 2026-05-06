<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\SalesTaxGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SalesTaxGroupController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $company = $this->resolveScopedCompany(
            $request->user(),
            (string) $request->query('company', $request->query('company_id', ''))
        );

        return response()->json([
            'status' => true,
            'message' => 'Sales tax groups fetched successfully.',
            'data' => SalesTaxGroup::query()
                ->when($company, fn ($q) => $q->where('company_id', $company->id))
                ->with('company:id,d365_id,name')
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

        $validated = $request->validate([
            'tax_group_id' => [
                'required',
                'string',
                'max:100',
                Rule::unique('sales_tax_groups', 'tax_group_id')->where(
                    fn ($q) => $q->where('company_id', $company->id)
                ),
            ],
            'tax_group_name' => ['required', 'string', 'max:255'],
        ]);

        $group = SalesTaxGroup::create([
            'company_id' => $company->id,
            'tax_group_id' => trim($validated['tax_group_id']),
            'tax_group_name' => trim($validated['tax_group_name']),
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Sales tax group created successfully.',
            'data' => $group,
        ], 201);
    }

    public function destroy(SalesTaxGroup $sales_tax_group): JsonResponse
    {
        $companyCode = (string) Company::query()->whereKey($sales_tax_group->company_id)->value('d365_id');
        $this->assertCompanyAccess($companyCode);
        $sales_tax_group->delete();

        return response()->json([
            'status' => true,
            'message' => 'Sales tax group deleted successfully.',
        ]);
    }
}
