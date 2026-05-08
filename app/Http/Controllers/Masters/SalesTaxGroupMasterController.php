<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

class SalesTaxGroupMasterController extends Controller
{
    public function index(Request $request)
    {
        $currentCompanyCode = strtoupper(trim((string) $request->query('company', '')));
        $selectedCompany = $this->resolveScopedCompany($request->user(), $currentCompanyCode);

        return view('masters.sales_tax_group.index', [
            'currentCompanyCode' => strtoupper((string) ($selectedCompany?->d365_id ?? $currentCompanyCode)),
        ]);
    }
}
