<?php

namespace App\Http\Controllers\Modules\Procurement;

use App\Http\Controllers\Controller;
use App\Models\BudgetResourceCode;
use App\Models\Company;
use App\Models\DepartmentManager;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Pool;
use App\Models\Project;
use App\Models\PurchReqJournal;
use App\Models\Warehouse;
use App\Services\D365PurchReqService;
use App\Support\DataAreaId;
use App\Support\PoolCategoryAllowlist;
use App\Support\PoolPurchReqMode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class PurchReqController extends Controller
{
    public function index(Request $request)
    {
        $allowedCompanyCodes = $this->allowedCompanyCodes(auth()->user());

        $companies = Company::query()
            ->select(['id', 'd365_id', 'name'])
            ->whereNotNull('d365_id')
            ->when($allowedCompanyCodes !== null, fn ($query) => $query->whereIn(\DB::raw('UPPER(d365_id)'), $allowedCompanyCodes->all()))
            ->orderBy('name')
            ->get();

        if ($companies->isEmpty()) {
            abort(403, 'You do not have access to any organization.');
        }

        $defaultCompany = $companies->first(fn (Company $c) => strtoupper((string) $c->d365_id) === 'PS') ?? $companies->first();
        $requestedCompanyCode = strtoupper(trim((string) $request->query('company', '')));
        $selectedCompany = $companies->first(fn (Company $c) => strtoupper((string) $c->d365_id) === $requestedCompanyCode) ?? $defaultCompany;

        if ($selectedCompany && strtoupper((string) $selectedCompany->d365_id) !== $requestedCompanyCode) {
            return redirect()->route('modules.procurement.purch-req', ['company' => strtoupper((string) $selectedCompany->d365_id)]);
        }

        $journals = PurchReqJournal::query()
            ->with('postedBy:id,name')
            ->when($selectedCompany, function ($q) use ($selectedCompany) {
                DataAreaId::whereUpperTrimEquals($q, 'company', (string) $selectedCompany->d365_id);
            })
            ->orderByDesc('created_at')
            ->get();

        return view('modules.procurement.purch-req.index', [
            'companies' => $companies,
            'journals' => $journals,
            'currentCompanyCode' => $selectedCompany?->d365_id,
        ]);
    }

    public function post(Request $request, D365PurchReqService $service): JsonResponse
    {
        try {
            set_time_limit(60);

            $validated = $request->validate([
                'draft_id' => ['nullable', 'integer', 'exists:purch_req_journals,id'],
                'company' => ['required', 'string', 'max:20'],
                'buying_legal_entity' => ['nullable', 'string', 'max:20'],
                'pr_date' => ['required', 'date'],
                'warehouse' => ['nullable', 'string', 'max:100'],
                'project_id' => ['nullable', 'string', 'max:100'],
                'pool_id' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::exists('pools', 'pool_id')->where(function ($q) use ($request) {
                        DataAreaId::whereUpperTrimEquals($q, 'company_id', (string) $request->input('company'));
                    }),
                ],
                'contact_name' => ['required', 'string', 'max:255'],
                'remarks' => ['nullable', 'string', 'max:500'],
                'department' => ['required', 'string', 'max:255'],
                'lines' => ['required', 'array', 'min:1'],
                'lines.*.item_category' => ['nullable', 'string', 'max:100'],
                'lines.*.item_id' => ['nullable', 'string', 'max:100'],
                'lines.*.item_description' => ['nullable', 'string', 'max:255'],
                'lines.*.required_date' => ['required', 'date'],
                'lines.*.unit' => ['nullable', 'string', 'max:30'],
                'lines.*.qty' => ['required', 'numeric', 'gt:0'],
                'lines.*.currency' => ['required', 'string', 'max:10'],
                'lines.*.rate' => ['required', 'numeric', 'min:0'],
                'lines.*.candy_budget' => ['nullable', 'numeric', 'min:0'],
                'lines.*.budget_resource_id' => ['nullable', 'string', 'max:100'],
                'lines.*.warranty' => ['nullable', 'string', 'max:100'],
                'attachments' => ['nullable', 'array'],
                'attachments.*.file_name' => ['required', 'string', 'max:255'],
                'attachments.*.file_type' => ['required', 'string', 'max:20'],
                'attachments.*.mime_type' => ['nullable', 'string', 'max:100'],
                'attachments.*.size_bytes' => ['nullable', 'numeric', 'min:0'],
                'attachments.*.file_content' => ['required', 'string'],
                'attachments.*.purch_id' => ['nullable', 'string', 'max:100'],
            ]);
            $this->assertCompanyAccess((string) $validated['company']);

            $validated['company'] = DataAreaId::normalize((string) $validated['company']);
            if (! empty($validated['buying_legal_entity'])) {
                $validated['buying_legal_entity'] = DataAreaId::normalize((string) $validated['buying_legal_entity']);
            }

            $poolRow = Pool::query()
                ->where('pool_id', trim((string) $validated['pool_id']))
                ->tap(fn ($q) => DataAreaId::whereUpperTrimEquals($q, 'company_id', $validated['company']))
                ->first();

            if (! $poolRow) {
                throw ValidationException::withMessages(['pool_id' => ['Pool not found for this company.']]);
            }

            $this->assertPurchReqMatchesPool($validated, $poolRow);
            $validated['lines'] = $this->normalizeSubmittedLines($validated['lines'], $poolRow);

            $companyRow = Company::resolveFromMixed($validated['company']);
            $categoryCodeMap = $companyRow ? $this->buildPurchReqCategoryCodeMap($companyRow) : [];

            $requestId = $this->generatePRRequestId((string) $validated['company'], (string) ($validated['pr_date'] ?? ''));
            $prNo = $this->generatePRNo();

            $d365Payload = [
                '_request' => [
                    'DataAreaId' => trim($validated['company']),
                    'PurchReqHeader' => [
                        'RequestID' => $requestId,
                        'PRNo' => $prNo,
                        'PRDate' => $validated['pr_date'],
                        'Warehouse' => trim((string) ($validated['warehouse'] ?? '')),
                        'ProjectId' => trim((string) ($validated['project_id'] ?? '')),
                        'PoolID' => $validated['pool_id'],
                        'ContactName' => $validated['contact_name'],
                        'Remarks' => $validated['remarks'] ?? '',
                        'Department' => $validated['department'],
                    ],
                    'PurchReqLines' => array_map(function (array $line, int $idx) use ($poolRow, $categoryCodeMap) {
                        $itemId = trim((string) ($line['item_id'] ?? ''));
                        $catIn = trim((string) ($line['item_category'] ?? ''));
                        $catKey = strtolower($catIn);
                        $itemCategoryForD365 = ($catKey !== '' && isset($categoryCodeMap[$catKey]))
                            ? $categoryCodeMap[$catKey]
                            : $catIn;

                        $row = [
                            'LineNo' => $idx + 1,
                            'ItemCategory' => $itemCategoryForD365,
                            'ItemDescription' => (string) ($line['item_description'] ?? ''),
                            'RequiredDate' => $line['required_date'],
                            'Unit' => (string) ($line['unit'] ?? ''),
                            'Qty' => (float) $line['qty'],
                            'Currency' => (string) ($line['currency'] ?? ''),
                            'Rate' => (float) $line['rate'],
                            'CandyBudget' => (float) ($line['candy_budget'] ?? 0),
                            'BudgetResourceId' => (string) ($line['budget_resource_id'] ?? ''),
                            'Warranty' => (string) ($line['warranty'] ?? 'N/A'),
                        ];

                        // Category-only pools: do not send ItemId at all — D365 treats null/"" as an item lookup and fails.
                        if (PoolPurchReqMode::requiresTypedItemId($poolRow) && $itemId !== '') {
                            $row['ItemId'] = $itemId;
                        }

                        return $row;
                    }, $validated['lines'], array_keys($validated['lines'])),
                    'PurchReqAttachments' => array_map(fn ($att) => [
                        'purchId' => $att['purch_id'] ?? '',
                        'fileName' => $att['file_name'],
                        'fileType' => $att['file_type'],
                        'FileContentBase64' => $att['file_content'],
                    ], $validated['attachments'] ?? []),
                ],
            ];

            $result = $service->postPurchReq($d365Payload);

            if ($this->isFailedD365Response($result)) {
                $d365Msg = $this->extractD365ErrorMessage($result);

                return response()->json([
                    'status' => false,
                    'message' => 'PR submission failed.',
                    'error' => 'D365 returned: '.$d365Msg,
                    'data' => $result,
                ], 422);
            }

            // Prefer the PR No that D365 assigned; fall back to locally generated
            $prNo = $this->extractPRNoFromD365($result, $prNo);

            $attachmentsForDb = array_map(fn ($a) => [
                'file_name' => $a['file_name'],
                'file_type' => $a['file_type'],
                'mime_type' => $a['mime_type'] ?? null,
                'size_bytes' => $a['size_bytes'] ?? null,
                'file_content' => $a['file_content'],
            ], $validated['attachments'] ?? []);

            $draftId = isset($validated['draft_id']) ? (int) $validated['draft_id'] : null;
            $draft = $draftId ? PurchReqJournal::query()->where('id', $draftId)->whereNull('request_id')->whereNull('pr_no')->first() : null;

            if ($draft) {
                if (! $draft->canBeManagedBy(auth()->user())) {
                    return response()->json(['status' => false, 'message' => 'You do not have access to submit or modify this purchase requisition.'], 403);
                }
                $draft->update(['request_id' => $requestId, 'pr_no' => $prNo, 'company' => $validated['company'], 'buying_legal_entity' => $validated['buying_legal_entity'] ?? $validated['company'], 'pr_date' => $validated['pr_date'], 'warehouse' => $validated['warehouse'] ?? null, 'project_id' => $validated['project_id'] ?? null, 'pool_id' => $validated['pool_id'], 'contact_name' => $validated['contact_name'], 'remarks' => $validated['remarks'] ?? null, 'department' => $validated['department'], 'lines' => $validated['lines'], 'attachments' => $attachmentsForDb, 'd365_response' => $result, 'posted_by' => auth()->id()]);
                $journal = $draft->fresh();
            } else {
                $journal = PurchReqJournal::create(['request_id' => $requestId, 'pr_no' => $prNo, 'company' => $validated['company'], 'buying_legal_entity' => $validated['buying_legal_entity'] ?? $validated['company'], 'pr_date' => $validated['pr_date'], 'warehouse' => $validated['warehouse'] ?? null, 'project_id' => $validated['project_id'] ?? null, 'pool_id' => $validated['pool_id'], 'contact_name' => $validated['contact_name'], 'remarks' => $validated['remarks'] ?? null, 'department' => $validated['department'], 'lines' => $validated['lines'], 'attachments' => $attachmentsForDb, 'd365_response' => $result, 'posted_by' => auth()->id()]);
            }

            return response()->json(['status' => true, 'message' => 'Purchase Requisition submitted to D365.', 'request_id' => $requestId, 'pr_no' => $prNo, 'journal_id' => $journal->id, 'data' => $result]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            return response()->json(['status' => false, 'message' => 'PR submission failed.', 'error' => $e->getMessage()], 500);
        }
    }

    public function saveDraft(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company' => ['nullable', 'string', 'max:20'],
            'buying_legal_entity' => ['nullable', 'string', 'max:20'],
            'pr_date' => ['nullable', 'date'],
            'warehouse' => ['nullable', 'string', 'max:100', $this->warehouseIdExistsForCompanyRule($request)],
            'project_id' => ['nullable', 'string', 'max:100', $this->projectIdExistsForCompanyRule($request)],
            'pool_id' => ['nullable', 'string', 'max:100', $this->poolIdExistsForCompanyRule($request)],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string', 'max:500'],
            'department' => ['nullable', 'string', 'max:255'],
            'lines' => ['nullable', 'array'],
            'lines.*.item_category' => ['nullable', 'string', 'max:100'],
            'lines.*.item_id' => ['nullable', 'string', 'max:100'],
            'lines.*.item_description' => ['nullable', 'string', 'max:255'],
            'lines.*.required_date' => ['nullable', 'date'],
            'lines.*.unit' => ['nullable', 'string', 'max:30'],
            'lines.*.qty' => ['nullable', 'numeric', 'gt:0'],
            'lines.*.currency' => ['nullable', 'string', 'max:10'],
            'lines.*.rate' => ['nullable', 'numeric', 'min:0'],
            'lines.*.candy_budget' => ['nullable', 'numeric', 'min:0'],
            'lines.*.budget_resource_id' => ['nullable', 'string', 'max:100'],
            'lines.*.warranty' => ['nullable', 'string', 'max:100'],
            'attachments' => ['nullable', 'array'],
            'attachments.*.file_name' => ['required', 'string', 'max:255'],
            'attachments.*.file_type' => ['required', 'string', 'max:20'],
            'attachments.*.mime_type' => ['nullable', 'string', 'max:100'],
            'attachments.*.size_bytes' => ['nullable', 'numeric', 'min:0'],
            'attachments.*.file_content' => ['required', 'string'],
            'attachments.*.purch_id' => ['nullable', 'string', 'max:100'],
        ]);

        if (! empty($validated['company'])) {
            $this->assertCompanyAccess((string) $validated['company']);
        }

        $attachmentsForDb = array_map(fn ($a) => ['file_name' => $a['file_name'], 'file_type' => $a['file_type'], 'mime_type' => $a['mime_type'] ?? null, 'size_bytes' => $a['size_bytes'] ?? null, 'file_content' => $a['file_content']], $validated['attachments'] ?? []);

        $journal = PurchReqJournal::create(['request_id' => null, 'pr_no' => null, 'company' => $validated['company'] ?? null, 'buying_legal_entity' => $validated['buying_legal_entity'] ?? ($validated['company'] ?? null), 'pr_date' => $validated['pr_date'] ?? null, 'warehouse' => $validated['warehouse'] ?? null, 'project_id' => $validated['project_id'] ?? null, 'pool_id' => $validated['pool_id'] ?? null, 'contact_name' => $validated['contact_name'] ?? null, 'remarks' => $validated['remarks'] ?? null, 'department' => $validated['department'] ?? null, 'lines' => $validated['lines'] ?? [], 'attachments' => $attachmentsForDb, 'd365_response' => null, 'posted_by' => auth()->id()]);

        return response()->json(['status' => true, 'message' => 'PR saved as draft.', 'journal_id' => $journal->id]);
    }

    public function updateDraft(Request $request, PurchReqJournal $journal): JsonResponse
    {
        if ($journal->request_id || $journal->pr_no) {
            return response()->json(['status' => false, 'message' => 'Submitted PR cannot be edited.'], 422);
        }
        if (! $journal->canBeManagedBy(auth()->user())) {
            return response()->json(['status' => false, 'message' => 'You do not have access to edit this purchase requisition. You can view it only.'], 403);
        }

        $validated = $request->validate([
            'company' => ['nullable', 'string', 'max:20'],
            'buying_legal_entity' => ['nullable', 'string', 'max:20'],
            'pr_date' => ['nullable', 'date'],
            'warehouse' => ['nullable', 'string', 'max:100', $this->warehouseIdExistsForCompanyRule($request)],
            'project_id' => ['nullable', 'string', 'max:100', $this->projectIdExistsForCompanyRule($request)],
            'pool_id' => ['nullable', 'string', 'max:100', $this->poolIdExistsForCompanyRule($request)],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string', 'max:500'],
            'department' => ['nullable', 'string', 'max:255'],
            'lines' => ['nullable', 'array'],
            'lines.*.item_category' => ['nullable', 'string', 'max:100'],
            'lines.*.item_id' => ['nullable', 'string', 'max:100'],
            'lines.*.item_description' => ['nullable', 'string', 'max:255'],
            'lines.*.required_date' => ['nullable', 'date'],
            'lines.*.unit' => ['nullable', 'string', 'max:30'],
            'lines.*.qty' => ['nullable', 'numeric', 'gt:0'],
            'lines.*.currency' => ['nullable', 'string', 'max:10'],
            'lines.*.rate' => ['nullable', 'numeric', 'min:0'],
            'lines.*.candy_budget' => ['nullable', 'numeric', 'min:0'],
            'lines.*.budget_resource_id' => ['nullable', 'string', 'max:100'],
            'lines.*.warranty' => ['nullable', 'string', 'max:100'],
            'attachments' => ['nullable', 'array'],
            'attachments.*.file_name' => ['required', 'string', 'max:255'],
            'attachments.*.file_type' => ['required', 'string', 'max:20'],
            'attachments.*.mime_type' => ['nullable', 'string', 'max:100'],
            'attachments.*.size_bytes' => ['nullable', 'numeric', 'min:0'],
            'attachments.*.file_content' => ['required', 'string'],
            'attachments.*.purch_id' => ['nullable', 'string', 'max:100'],
        ]);

        if (! empty($validated['company'])) {
            $this->assertCompanyAccess((string) $validated['company']);
        }

        $attachmentsForDb = array_map(fn ($a) => ['file_name' => $a['file_name'], 'file_type' => $a['file_type'], 'mime_type' => $a['mime_type'] ?? null, 'size_bytes' => $a['size_bytes'] ?? null, 'file_content' => $a['file_content']], $validated['attachments'] ?? []);

        $journal->update(['company' => $validated['company'] ?? null, 'buying_legal_entity' => $validated['buying_legal_entity'] ?? ($validated['company'] ?? null), 'pr_date' => $validated['pr_date'] ?? null, 'warehouse' => $validated['warehouse'] ?? null, 'project_id' => $validated['project_id'] ?? null, 'pool_id' => $validated['pool_id'] ?? null, 'contact_name' => $validated['contact_name'] ?? null, 'remarks' => $validated['remarks'] ?? null, 'department' => $validated['department'] ?? null, 'lines' => $validated['lines'] ?? [], 'attachments' => $attachmentsForDb]);

        return response()->json(['status' => true, 'message' => 'Draft updated successfully.', 'journal_id' => $journal->id]);
    }

    public function showJournal(PurchReqJournal $journal): JsonResponse
    {
        $this->assertCompanyAccess((string) $journal->company);

        return response()->json(['status' => true, 'data' => $journal, 'is_draft' => ! $journal->request_id && ! $journal->pr_no, 'can_manage' => $journal->canBeManagedBy(auth()->user())]);
    }

    public function destroyJournal(PurchReqJournal $journal): JsonResponse
    {
        $this->assertCompanyAccess((string) $journal->company);
        if (! $journal->canBeManagedBy(auth()->user())) {
            return response()->json(['status' => false, 'message' => 'You do not have access to delete this purchase requisition. You can view it only.'], 403);
        }
        $journal->delete();

        return response()->json(['status' => true, 'message' => 'PR deleted successfully.']);
    }

    public function lookupUnits(Request $request, D365PurchReqService $service): JsonResponse
    {
        set_time_limit(60);
        $validated = $request->validate(['company' => ['required', 'string', 'max:20'], 'item_id' => ['nullable', 'string', 'max:100']]);
        $this->assertCompanyAccess((string) $validated['company']);

        try {
            $data = $service->lookupUnits(trim($validated['company']), $validated['item_id'] ?? '');

            return response()->json(['status' => true, 'message' => 'Units fetched.', 'units' => $this->normalizeUnits($data), 'data' => $data]);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['status' => false, 'message' => 'Unit lookup failed.', 'error' => $e->getMessage()], 500);
        }
    }

    public function lookupCatalog(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company' => ['nullable', 'string', 'max:20'],
            'pool_id' => ['nullable', 'string', 'max:100'],
        ]);
        $companyCode = trim((string) ($validated['company'] ?? ''));
        $poolId = trim((string) ($validated['pool_id'] ?? ''));

        if ($poolId !== '' && $companyCode === '') {
            return response()->json([
                'status' => false,
                'message' => 'Company is required when filtering catalog by pool.',
                'errors' => ['company' => ['Select a company before loading pool-scoped categories.']],
            ], 422);
        }

        if ($companyCode !== '') {
            $this->assertCompanyAccess($companyCode);
        }
        $company = $companyCode !== '' ? Company::resolveFromMixed($companyCode) : null;

        if ($companyCode !== '' && ! $company) {
            return response()->json(['status' => false, 'message' => 'Unknown company code.', 'errors' => ['company' => ['No company found for this DataAreaId.']]], 422);
        }

        $pool = null;
        if ($poolId !== '' && $company) {
            $pool = Pool::query()
                ->where('pool_id', $poolId)
                ->tap(fn ($q) => DataAreaId::whereUpperTrimEquals($q, 'company_id', $companyCode))
                ->first();
            if (! $pool) {
                return response()->json([
                    'status' => false,
                    'message' => 'Pool not found for this company.',
                    'errors' => ['pool_id' => ['Unknown pool for the selected company.']],
                ], 422);
            }
        }

        $allowTokens = PoolCategoryAllowlist::tokensFromPool($pool);

        $categoriesQuery = ItemCategory::query()->select(['company_id', 'd365_id', 'name'])->orderBy('name');
        if ($company) {
            $categoriesQuery->where('company_id', $company->id);
        }

        $baseCategories = $categoriesQuery->get()->map(function (ItemCategory $category) {
            $code = trim((string) ($category->getAttribute('d365_id') ?? ''));
            $name = trim((string) ($category->name ?? ''));

            return ['id' => $code !== '' ? $code : $name, 'name' => $name !== '' ? $name : $code];
        })->filter(fn ($c) => $c['id'] !== '')->unique(fn ($c) => strtolower($c['id']))->values();

        $categories = $baseCategories;
        $applyPoolCategoryFilter = $allowTokens !== [];
        if ($applyPoolCategoryFilter) {
            $filtered = $baseCategories->filter(function (array $c) use ($allowTokens) {
                return PoolCategoryAllowlist::categoryMatchesAllowlist((string) $c['id'], (string) $c['name'], $allowTokens);
            })->values();
            if ($filtered->isNotEmpty()) {
                $categories = $filtered;
            } else {
                $applyPoolCategoryFilter = false;
            }
        }

        $categoryLookup = [];
        foreach ($categories as $cat) {
            $idKey = strtolower(trim((string) ($cat['id'] ?? '')));
            $nameKey = strtolower(trim((string) ($cat['name'] ?? '')));
            if ($idKey !== '') {
                $categoryLookup[$idKey] = $cat['id'];
            }
            if ($nameKey !== '') {
                $categoryLookup[$nameKey] = $cat['id'];
            }
        }

        $itemsQuery = Item::query()->select(['company_id', 'd365_id', 'd365_item_id', 'item_name', 'item_category_id']);
        if ($company) {
            $itemsQuery->where('company_id', $company->id);
        }

        if ($categories->isEmpty()) {
            $distinctCats = (clone $itemsQuery)->whereNotNull('item_category_id')->where('item_category_id', '!=', '')->select('item_category_id')->distinct()->orderBy('item_category_id')->pluck('item_category_id');
            $fromItems = $distinctCats->map(fn ($c) => ['id' => trim((string) $c), 'name' => trim((string) $c)])->values();
            $categories = $fromItems;
            if ($applyPoolCategoryFilter) {
                $filtered = $fromItems->filter(function (array $c) use ($allowTokens) {
                    return PoolCategoryAllowlist::categoryMatchesAllowlist((string) $c['id'], (string) $c['name'], $allowTokens);
                })->values();
                if ($filtered->isNotEmpty()) {
                    $categories = $filtered;
                } else {
                    $applyPoolCategoryFilter = false;
                }
            }
            foreach ($categories as $cat) {
                $idKey = strtolower(trim((string) ($cat['id'] ?? '')));
                if ($idKey !== '') {
                    $categoryLookup[$idKey] = $cat['id'];
                }
            }
        }

        $items = $itemsQuery->orderBy('item_name')->get()->map(function (Item $item) use ($categoryLookup) {
            $itemId = trim((string) ($item->item_id ?? ''));
            $itemName = trim((string) ($item->item_name ?? ''));
            $rawCat = trim((string) ($item->item_category_id ?? ''));

            return ['id' => $itemId, 'name' => $itemName, 'category' => $categoryLookup[strtolower($rawCat)] ?? $rawCat];
        })->filter(fn ($i) => $i['id'] !== '');

        if ($applyPoolCategoryFilter) {
            $filteredItems = $items->filter(function (array $i) use ($allowTokens) {
                $cat = trim((string) ($i['category'] ?? ''));

                return PoolCategoryAllowlist::categoryMatchesAllowlist($cat, $cat, $allowTokens);
            })->values();
            if ($filteredItems->isNotEmpty()) {
                $items = $filteredItems;
            }
        }

        $items = $items->values();

        return response()->json(['status' => true, 'message' => 'Catalog fetched.', 'categories' => $categories, 'items' => $items]);
    }

    public function lookupDepartmentManagers(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company' => ['nullable', 'string', 'max:20'],
            'company_id' => ['nullable', 'string', 'max:20'],
        ]);

        $companyCode = strtoupper(trim((string) ($validated['company'] ?? $validated['company_id'] ?? '')));
        if ($companyCode === '') {
            return response()->json([
                'status' => true,
                'message' => 'Department managers fetched.',
                'data' => [],
            ]);
        }

        $this->assertCompanyAccess($companyCode);

        $rows = DepartmentManager::query()
            ->tap(fn ($q) => DataAreaId::whereUpperTrimEquals($q, 'company_id', $companyCode))
            ->orderBy('employee_name')
            ->get(['id', 'employee_name', 'department', 'company_id']);

        return response()->json([
            'status' => true,
            'message' => 'Department managers fetched.',
            'data' => $rows,
        ]);
    }

    public function lookupPools(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company' => ['nullable', 'string', 'max:20'],
            'company_id' => ['nullable', 'string', 'max:20'],
        ]);

        $companyCode = strtoupper(trim((string) ($validated['company'] ?? $validated['company_id'] ?? '')));
        if ($companyCode === '') {
            return response()->json([
                'status' => true,
                'message' => 'Pools fetched.',
                'data' => [],
            ]);
        }

        $this->assertCompanyAccess($companyCode);

        $rows = Pool::query()
            ->tap(fn ($q) => DataAreaId::whereUpperTrimEquals($q, 'company_id', $companyCode))
            ->orderBy('pool_id')
            ->get([
                'id',
                'pool_id',
                'name',
                'company_id',
                'uses_project',
                'uses_warehouse',
                'has_attachment',
                'has_item_category',
                'has_item_id',
                'item_id',
                'category_item',
            ])
            ->map(fn (Pool $p) => [
                'id' => $p->id,
                'pool_id' => $p->pool_id,
                'name' => $p->name,
                'company_id' => $p->company_id,
                'uses_project' => (bool) $p->uses_project,
                'uses_warehouse' => (bool) $p->uses_warehouse,
                'has_attachment' => (bool) $p->has_attachment,
                'has_item_category' => (bool) $p->has_item_category,
                'has_item_id' => (bool) $p->has_item_id,
                'requires_typed_item_id' => PoolPurchReqMode::requiresTypedItemId($p),
            ])
            ->values();

        return response()->json([
            'status' => true,
            'message' => 'Pools fetched.',
            'data' => $rows,
        ]);
    }

    public function lookupProjects(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company' => ['nullable', 'string', 'max:20'],
            'company_id' => ['nullable', 'string', 'max:20'],
        ]);

        $companyCode = strtoupper(trim((string) ($validated['company'] ?? $validated['company_id'] ?? '')));
        if ($companyCode === '') {
            return response()->json([
                'status' => true,
                'message' => 'Projects fetched.',
                'data' => [],
            ]);
        }

        $this->assertCompanyAccess($companyCode);

        $rows = Project::query()
            ->whereHas('company', fn ($q) => $q->whereRaw('UPPER(TRIM(d365_id)) = ?', [$companyCode]))
            ->orderBy('name')
            ->get(['d365_id', 'name']);

        $data = $rows->map(fn ($p) => [
            'project_id' => trim((string) ($p->d365_id ?? '')),
            'name' => trim((string) ($p->name ?? '')),
        ])->filter(fn ($r) => $r['project_id'] !== '')->values();

        return response()->json([
            'status' => true,
            'message' => 'Projects fetched.',
            'data' => $data,
        ]);
    }

    public function lookupWarehouses(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company' => ['nullable', 'string', 'max:20'],
            'company_id' => ['nullable', 'string', 'max:20'],
        ]);

        $companyCode = strtoupper(trim((string) ($validated['company'] ?? $validated['company_id'] ?? '')));
        if ($companyCode === '') {
            return response()->json([
                'status' => true,
                'message' => 'Warehouses fetched.',
                'data' => [],
            ]);
        }

        $this->assertCompanyAccess($companyCode);

        $rows = Warehouse::query()
            ->tap(fn ($q) => DataAreaId::whereUpperTrimEquals($q, 'company_id', $companyCode))
            ->orderBy('warehouse_id')
            ->get(['warehouse_id', 'warehouse_name']);

        return response()->json([
            'status' => true,
            'message' => 'Warehouses fetched.',
            'data' => $rows,
        ]);
    }

    public function lookupBudgetResourceCodes(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company' => ['nullable', 'string', 'max:20'],
            'company_id' => ['nullable', 'string', 'max:20'],
        ]);

        $companyCode = strtoupper(trim((string) ($validated['company'] ?? $validated['company_id'] ?? '')));
        if ($companyCode === '') {
            return response()->json([
                'status' => true,
                'message' => 'Budget resource codes fetched.',
                'data' => [],
            ]);
        }

        $this->assertCompanyAccess($companyCode);

        $rows = BudgetResourceCode::query()
            ->tap(fn ($q) => DataAreaId::whereUpperTrimEquals($q, 'company_id', $companyCode))
            ->orderBy('resource_code')
            ->get(['resource_code', 'description', 'project']);

        return response()->json([
            'status' => true,
            'message' => 'Budget resource codes fetched.',
            'data' => $rows,
        ]);
    }

    public function downloadAttachment(PurchReqJournal $journal, int $index): Response
    {
        $this->assertCompanyAccess((string) $journal->company);
        $att = $this->resolveAttachment($journal, $index);
        $content = base64_decode($att['file_content'] ?? '');
        $mime = $att['mime_type'] ?? 'application/octet-stream';
        $fileName = $att['file_name'] ?? 'attachment';

        return response($content, 200, ['Content-Type' => $mime, 'Content-Disposition' => 'inline; filename="'.$fileName.'"', 'Content-Length' => strlen($content)]);
    }

    public function viewBase64(PurchReqJournal $journal, int $index): Response
    {
        $this->assertCompanyAccess((string) $journal->company);
        $att = $this->resolveAttachment($journal, $index);
        $b64 = $att['file_content'] ?? '';
        $fileName = $att['file_name'] ?? 'attachment';

        return response($b64, 200, ['Content-Type' => 'text/plain; charset=utf-8', 'Content-Disposition' => 'inline; filename="'.$fileName.'.base64.txt"']);
    }

    private function resolveAttachment(PurchReqJournal $journal, int $index): array
    {
        $attachments = $journal->attachments ?? [];
        if (! isset($attachments[$index])) {
            abort(404, 'Attachment not found.');
        }

        return $attachments[$index];
    }

    private function normalizeUnits(array $result): array
    {
        $rows = array_is_list($result) && count($result) > 0 && is_array($result[0]) ? $result : ($result['data'] ?? []);

        return array_values(array_filter(array_map(function ($row) {
            $id = $row['Unit Id'] ?? $row['d365_unit_id'] ?? $row['Symbol'] ?? $row['UnitId'] ?? '';
            $name = $row['unit_name'] ?? $row['Description'] ?? $row['UnitName'] ?? $id;

            return $id !== '' ? ['id' => $id, 'name' => $name] : null;
        }, $rows)));
    }

    private function normalizeSubmittedLines(array $lines, Pool $pool): array
    {
        $itemIds = collect($lines)->map(fn ($l) => trim((string) ($l['item_id'] ?? '')))->filter()->unique()->values();
        $itemCategoryMap = [];
        if ($itemIds->isNotEmpty()) {
            $items = Item::query()->select(['d365_id', 'd365_item_id', 'item_category_id'])->whereIn('d365_id', $itemIds->all())->orWhereIn('d365_item_id', $itemIds->all())->get();
            foreach ($items as $item) {
                $category = trim((string) ($item->item_category_id ?? ''));
                if ($category === '') {
                    continue;
                }
                foreach (array_filter([trim((string) ($item->d365_id ?? '')), trim((string) ($item->d365_item_id ?? ''))]) as $key) {
                    $itemCategoryMap[strtolower($key)] = $category;
                }
            }
        }

        return array_map(function (array $line) use ($itemCategoryMap, $pool) {
            $itemId = trim((string) ($line['item_id'] ?? ''));
            $itemCategory = trim((string) ($line['item_category'] ?? ''));
            $unit = trim((string) ($line['unit'] ?? ''));

            if (! $pool->has_item_id) {
                $itemId = '';
            }
            if (! $pool->has_item_category) {
                $itemCategory = '';
            }

            if ($itemCategory === '' && $itemId !== '') {
                $itemCategory = $itemCategoryMap[strtolower($itemId)] ?? '';
            }

            $categoryDrivenLine = $pool->has_item_category
                && ! PoolPurchReqMode::requiresTypedItemId($pool)
                && $itemId === '';
            if ($categoryDrivenLine) {
                $unit = 'NOS';
            } elseif ($itemId === '' && ! $pool->has_item_category) {
                $unit = '';
            }

            $line['item_id'] = $itemId;
            $line['item_category'] = $itemCategory;
            $line['unit'] = $unit;

            return $line;
        }, $lines);
    }

    /**
     * Map lowercase category id or display name → canonical D365 item category code (d365_id).
     * Sending display names (e.g. "inventory items") in PurchReqLines can make D365 validate them as ItemId and fail.
     *
     * @return array<string, string>
     */
    private function buildPurchReqCategoryCodeMap(Company $company): array
    {
        $map = [];
        foreach (ItemCategory::query()->where('company_id', $company->id)->get(['d365_id', 'name']) as $row) {
            $code = trim((string) ($row->getAttribute('d365_id') ?? ''));
            $name = trim((string) ($row->name ?? ''));
            $canonical = $code !== '' ? $code : $name;
            if ($code !== '') {
                $map[strtolower($code)] = $canonical;
            }
            if ($name !== '') {
                $nk = strtolower($name);
                if (! isset($map[$nk])) {
                    $map[$nk] = $canonical;
                }
            }
        }

        return $map;
    }

    /**
     * Format: {COMPANY}-PR-{YEAR}-{SEQ} e.g. PS-PR-2026-001 (sequence per company + calendar year).
     */
    private function generatePRRequestId(string $company, string $prDate): string
    {
        $companyKey = strtoupper(trim($company));
        if ($companyKey === '') {
            $companyKey = 'UNK';
        }

        $year = $prDate !== ''
            ? (int) Carbon::parse($prDate)->format('Y')
            : (int) now()->year;

        $settingKey = 'purch_req_id_seq_'.preg_replace('/[^A-Za-z0-9_]/', '_', $companyKey).'_'.$year;

        $next = \DB::transaction(function () use ($settingKey) {
            $current = (int) \App\Models\AppSetting::get($settingKey, 0);
            $next = $current + 1;
            \App\Models\AppSetting::set($settingKey, $next);

            return $next;
        });

        return sprintf('%s-PR-%d-%03d', $companyKey, $year, $next);
    }

    private function generatePRNo(): string
    {
        $next = \DB::transaction(function () {
            $current = (int) \App\Models\AppSetting::get('purch_req_no_sequence', 0);
            $next = $current + 1;
            \App\Models\AppSetting::set('purch_req_no_sequence', $next);

            return $next;
        });

        return 'PR-'.str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Read the PR number D365 assigned (e.g. PurchReqId: "PR-064662").
     * Falls back to $fallback only when D365 does not return an id.
     */
    private function extractPRNoFromD365(array $result, string $fallback): string
    {
        $keys = [
            'PurchReqId',
            'PurchReqID',
            'purchReqId',
            'PurchReqNo',
            'PurchaseRequisitionNo',
            'RequisitionNo',
            'PurchId',
            'purchId',
            'PRNo',
            'PrNo',
            'prNo',
        ];

        $found = $this->searchForScalarValue($result, $keys);
        if ($found !== null) {
            $found = trim($found);
            if ($found !== '' && strcasecmp($found, trim($fallback)) !== 0) {
                return $found;
            }
        }

        $info = $this->searchForScalarValue($result, ['InfoMessage', 'InfoMsg', 'Message', 'message']);
        if ($info !== null && preg_match('/\b(PR-\d+)\b/i', $info, $matches)) {
            $parsed = $matches[1];
            if (strcasecmp($parsed, trim($fallback)) !== 0) {
                return $parsed;
            }
        }

        return $fallback;
    }

    private function searchForScalarValue(array $payload, array $possibleKeys): ?string
    {
        foreach ($possibleKeys as $key) {
            if (isset($payload[$key]) && is_scalar($payload[$key])) {
                return (string) $payload[$key];
            }
        }
        foreach ($payload as $value) {
            if (! is_array($value)) {
                continue;
            }
            $found = $this->searchForScalarValue($value, $possibleKeys);
            if ($found !== null) {
                return $found;
            }
        }

        return null;
    }

    private function isFailedD365Response(array $result): bool
    {
        return array_key_exists('Success', $result) && $result['Success'] === false;
    }

    private function extractD365ErrorMessage(array $result): string
    {
        $parts = [];
        foreach (['ErrorMessage', 'InfoMessage', 'Message', 'message'] as $key) {
            if (! isset($result[$key]) || ! is_scalar($result[$key])) {
                continue;
            }
            $value = trim((string) $result[$key]);
            if ($value !== '') {
                $parts[] = $value;
            }
        }

        return $parts !== [] ? implode(' ', array_unique($parts)) : 'D365 rejected the purchase requisition.';
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function assertPurchReqMatchesPool(array &$validated, Pool $pool): void
    {
        if (! $pool->has_item_category && ! $pool->has_item_id) {
            throw ValidationException::withMessages(['pool_id' => ['This pool must allow item category and/or item ID on lines (update Pool Master).']]);
        }

        $company = strtoupper(trim((string) $validated['company']));
        $errors = [];

        if ($pool->uses_warehouse) {
            $w = trim((string) ($validated['warehouse'] ?? ''));
            if ($w === '') {
                $errors['warehouse'] = ['Warehouse is required for this pool.'];
            } elseif (! Warehouse::query()
                ->where('warehouse_id', $w)
                ->tap(fn ($q) => DataAreaId::whereUpperTrimEquals($q, 'company_id', $company))
                ->exists()) {
                $errors['warehouse'] = ['The warehouse does not exist for the selected company.'];
            }
        } else {
            $validated['warehouse'] = null;
        }

        if ($pool->uses_project) {
            $p = trim((string) ($validated['project_id'] ?? ''));
            if ($p === '') {
                $errors['project_id'] = ['Project is required for this pool.'];
            } elseif (! Project::query()->where('d365_id', $p)->whereHas('company', fn ($q) => $q->whereRaw('UPPER(TRIM(d365_id)) = ?', [$company]))->exists()) {
                $errors['project_id'] = ['The project does not exist for the selected company.'];
            }
        } else {
            $validated['project_id'] = null;
        }

        $attachments = $validated['attachments'] ?? [];
        if ($pool->has_attachment && (! is_array($attachments) || count($attachments) === 0)) {
            $errors['attachments'] = ['At least one attachment is required for this pool.'];
        }

        foreach ($validated['lines'] as $idx => $line) {
            $i = $idx + 1;
            $cat = trim((string) ($line['item_category'] ?? ''));
            $iid = trim((string) ($line['item_id'] ?? ''));
            if ($pool->has_item_category && $cat === '') {
                $errors["lines.$idx.item_category"] = ["Line {$i}: Item category is required for this pool."];
            }
            if (PoolPurchReqMode::requiresTypedItemId($pool) && $iid === '') {
                $errors["lines.$idx.item_id"] = ["Line {$i}: Item ID is required for this pool."];
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function warehouseIdExistsForCompanyRule(Request $request): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($request): void {
            if ($value === null || $value === '') {
                return;
            }
            $company = strtoupper(trim((string) $request->input('company', '')));
            if ($company === '') {
                return;
            }
            $exists = Warehouse::query()
                ->where('warehouse_id', trim((string) $value))
                ->tap(fn ($q) => DataAreaId::whereUpperTrimEquals($q, 'company_id', $company))
                ->exists();
            if (! $exists) {
                $fail('The warehouse does not exist for the selected company.');
            }
        };
    }

    private function projectIdExistsForCompanyRule(Request $request): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($request): void {
            if ($value === null || $value === '') {
                return;
            }
            $company = strtoupper(trim((string) $request->input('company', '')));
            if ($company === '') {
                return;
            }
            $exists = Project::query()
                ->where('d365_id', trim((string) $value))
                ->whereHas('company', fn ($q) => $q->whereRaw('UPPER(TRIM(d365_id)) = ?', [$company]))
                ->exists();
            if (! $exists) {
                $fail('The project does not exist for the selected company.');
            }
        };
    }

    private function poolIdExistsForCompanyRule(Request $request): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($request): void {
            if ($value === null || $value === '') {
                return;
            }
            $company = strtoupper(trim((string) $request->input('company', '')));
            if ($company === '') {
                return;
            }
            $exists = Pool::query()
                ->where('pool_id', trim((string) $value))
                ->tap(fn ($q) => DataAreaId::whereUpperTrimEquals($q, 'company_id', $company))
                ->exists();
            if (! $exists) {
                $fail('The pool does not exist for the selected company.');
            }
        };
    }
}
