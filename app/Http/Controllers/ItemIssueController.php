<?php
 
namespace App\Http\Controllers;
 
use App\Models\Company;
use App\Models\ItemIssueJournal;
use App\Models\Project;
use App\Services\D365ItemIssueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;
 
class ItemIssueController extends Controller
{
    public function index(Request $request)
    {
        $companies = Company::query()
            ->select(['id', 'd365_id', 'name'])
            ->whereNotNull('d365_id')
            ->orderBy('name')
            ->get();

        $defaultCompany = $companies->first(function (Company $company) {
            return strtoupper((string) $company->d365_id) === 'PS'
                || strtoupper((string) $company->name) === 'PS';
        });

        $fallbackCompany = $defaultCompany ?? $companies->first();
        $requestedCompanyCode = strtoupper(trim((string) $request->query('company', '')));
        $selectedCompany = $companies->first(function (Company $company) use ($requestedCompanyCode) {
            return strtoupper((string) $company->d365_id) === $requestedCompanyCode;
        }) ?? $fallbackCompany;

        if ($selectedCompany && strtoupper((string) $selectedCompany->d365_id) !== $requestedCompanyCode) {
            return redirect()->route('modules.project-management.item-issue', [
                'company' => strtoupper((string) $selectedCompany->d365_id),
            ]);
        }
 
        $projects = Project::query()
            ->select(['id', 'd365_id', 'name'])
            ->whereHas('company', function ($query) use ($selectedCompany) {
                if (!$selectedCompany) {
                    return;
                }

                $query->where('id', $selectedCompany->id);
            })
            ->orderBy('name')
            ->get();
 
        $journals = ItemIssueJournal::query()
            ->with('postedBy:id,name')
            ->when($selectedCompany, function ($query) use ($selectedCompany) {
                $query->where('company', $selectedCompany->d365_id);
            })
            ->orderByDesc('created_at')
            ->get();
 
        return view('modules.project-management.item-issue.index', [
            'companies' => $companies,
            'projects'  => $projects,
            'journals'  => $journals,
            'currentCompanyCode' => $selectedCompany?->d365_id,
        ]);
    }
 
    public function lookupItems(Request $request, D365ItemIssueService $service): JsonResponse
    {
        $validated = $request->validate([
            'company'    => ['required', 'string', 'max:20'],
            'project_id' => ['nullable', 'string', 'max:100'],
        ]);
 
        try {
            $data = $service->lookupItems($this->resolveCompanyDataAreaId($validated['company']), $validated['project_id'] ?? null);
 
            return response()->json([
                'status' => true,
                'message' => 'Items fetched from D365.',
                'data' => $data,
            ]);
        } catch (Throwable $e) {
            report($e);
 
            $errorMessage = trim($e->getMessage()) !== '' ? $e->getMessage() : 'Unknown error during item lookup.';

            return response()->json([
                'status' => false,
                'message' => $errorMessage,
                'error' => $errorMessage,
            ], 500);
        }
    }
 
    public function lookupProjects(Request $request, D365ItemIssueService $service): JsonResponse
    {
        $validated = $request->validate([
            'company' => ['required', 'string', 'max:20'],
            'ProjectId' => ['nullable', 'string', 'max:100'],
        ]);
 
        try {
            $data = $service->lookupProjects($this->resolveCompanyDataAreaId($validated['company']), $validated['ProjectId'] ?? null);
 
            return response()->json([
                'status' => true,
                'message' => 'Projects fetched from D365.',
                'data' => $data,
            ]);
        } catch (Throwable $e) {
            report($e);
 
            $errorMessage = trim($e->getMessage()) !== '' ? $e->getMessage() : 'Unknown error during project lookup.';

            return response()->json([
                'status' => false,
                'message' => $errorMessage,
                'error' => $errorMessage,
            ], 500);
        }
    }
 
    public function post(Request $request, D365ItemIssueService $service): JsonResponse
    {
        $validated = $request->validate([
            'company' => ['required', 'string', 'max:20'],
            'project_id' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:255'],
            'invent_site_id' => ['required', 'string', 'max:100'],
            'invent_location_id' => ['required', 'string', 'max:100'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.project_id' => ['required', 'string', 'max:100'],
            'lines.*.item_id' => ['required', 'string', 'max:100'],
            'lines.*.category' => ['required', 'string', 'max:100'],
            'lines.*.currency' => ['required', 'string', 'max:20'],
            'lines.*.sales_price' => ['required', 'numeric'],
            'lines.*.unit' => ['required', 'string', 'max:30'],
            'lines.*.tax_group' => ['required', 'string', 'max:100'],
            'lines.*.tax_item_group' => ['nullable', 'string', 'max:100'],
            'lines.*.qty' => ['required', 'numeric', 'gt:0'],
            'lines.*.price_unit' => ['required', 'numeric', 'gt:0'],
            'lines.*.line_num' => ['required', 'integer', 'min:1'],
            'lines.*.wms_location' => ['required', 'string', 'max:100'],
        ]);
 
        $requestId = $this->generateRequestId();
        $dataAreaId = $this->resolveCompanyDataAreaId($validated['company']);
        $inventSiteId = $validated['invent_site_id'];
        $inventLocationId = $validated['invent_location_id'];
 
        $d365Payload = [
            '_request' => [
                'DataAreaId' => $dataAreaId,
                'ItemIssueHeader' => [
                    'RequestId' => $requestId,
                    'Description' => $validated['description'],
                    'InventSiteId' => $inventSiteId,
                    'InventLocationId' => $inventLocationId,
                ],
                'ItemIssueLines' => array_map(function (array $line) use ($requestId, $inventSiteId, $inventLocationId) {
                    return [
                        'RequestId' => $requestId,
                        'InventSiteId' => $inventSiteId,
                        'InventLocationId' => $inventLocationId,
                        'ProjId' => $line['project_id'],
                        'ProjCategoryId' => $line['category'],
                        'ItemId' => $line['item_id'],
                        'ProjSalesCurrencyId' => $line['currency'],
                        'ProjSalesPrice' => $line['sales_price'],
                        'ProjUnitID' => $line['unit'],
                        'ProjTaxGroupId' => $line['tax_group'],
                        'ProjTaxItemGroupId' => $line['tax_item_group'] ?? '',
                        'Qty' => $line['qty'],
                        'PriceUnit' => $line['price_unit'],
                        'LineNum' => $line['line_num'],
                        'wMSLocationId' => $line['wms_location'],
                        'InventSizeId' => '',
                        'InventSerialId' => '',
                        'InventStyleId' => '',
                    ];
                }, $validated['lines']),
            ],
        ];
 
        try {
            $result = $service->postItemIssue($d365Payload);
 
            if ($this->isFailedD365Response($result)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Item issue post failed.',
                    'error' => $this->extractD365ErrorMessage($result),
                    'data' => $result,
                ], 422);
            }
 
            $journalId = $this->extractJournalId($result);
 
            $savedJournal = ItemIssueJournal::create([
                'request_id'         => $requestId,
                'journal_id'         => $journalId,
                'company'            => $validated['company'],
                'project_id'         => $validated['project_id'],
                'description'        => $validated['description'],
                'invent_site_id'     => $validated['invent_site_id'],
                'invent_location_id' => $validated['invent_location_id'],
                'tax_group_id'       => $validated['lines'][0]['tax_group'] ?? null,
                'tax_item_group_id'  => $validated['lines'][0]['tax_item_group'] ?? null,
                'lines'              => $validated['lines'],
                'posted_by'          => auth()->id(),
            ]);
 
            return response()->json([
                'status'           => true,
                'message'          => 'Item issue posted to D365.',
                'journal_db_id'    => $savedJournal->id,
                'request_id'       => $requestId,
                'journal_id'       => $journalId,
                'response_preview' => json_encode($result, JSON_UNESCAPED_SLASHES),
                'data'             => $result,
            ]);
        } catch (Throwable $e) {
            report($e);
 
            $errorMessage = trim($e->getMessage()) !== '' ? $e->getMessage() : 'Unknown error during item issue post.';

            return response()->json([
                'status' => false,
                'message' => $errorMessage,
                'error' => $errorMessage,
            ], 500);
        }
    }
 
    public function lookupOnHand(Request $request, D365ItemIssueService $service): JsonResponse
    {
        $validated = $request->validate([
            'company' => ['required', 'string', 'max:20'],
            'item_id' => ['required', 'string', 'max:100'],
        ]);
 
        try {
            $data = $service->lookupOnHand(
                $this->resolveCompanyDataAreaId($validated['company']),
                $validated['item_id']
            );
 
            $qty = $this->extractOnHandQty($data);
 
            return response()->json([
                'status'  => true,
                'message' => 'On-hand qty fetched.',
                'qty'     => $qty,
                'data'    => $data,
            ]);
        } catch (Throwable $e) {
            report($e);
 
            $errorMessage = trim($e->getMessage()) !== '' ? $e->getMessage() : 'Unknown error during on-hand lookup.';

            return response()->json([
                'status'  => false,
                'message' => $errorMessage,
                'error'   => $errorMessage,
            ], 500);
        }
    }
 
    public function lookupUnits(Request $request, D365ItemIssueService $service): JsonResponse
    {
        $validated = $request->validate([
            'company' => ['required', 'string', 'max:20'],
            'item_id' => ['nullable', 'string', 'max:100'],
        ]);
 
        try {
            $data = $service->lookupUnits(
                $this->resolveCompanyDataAreaId($validated['company']),
                $validated['item_id'] ?? ''
            );
 
            $units = $this->normalizeUnits($data);
 
            return response()->json([
                'status'  => true,
                'message' => 'Units fetched.',
                'units'   => $units,
                'data'    => $data,
            ]);
        } catch (Throwable $e) {
            report($e);
 
            $errorMessage = trim($e->getMessage()) !== '' ? $e->getMessage() : 'Unknown error during unit lookup.';

            return response()->json([
                'status'  => false,
                'message' => $errorMessage,
                'error'   => $errorMessage,
            ], 500);
        }
    }

    public function showJournal(ItemIssueJournal $journal): JsonResponse
    {
        $journal->load('postedBy:id,name');

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $journal->id,
                'request_id' => $journal->request_id,
                'journal_id' => $journal->journal_id,
                'company' => $journal->company,
                'project_id' => $journal->project_id,
                'description' => $journal->description,
                'invent_site_id' => $journal->invent_site_id,
                'invent_location_id' => $journal->invent_location_id,
                'tax_group_id' => $journal->tax_group_id,
                'tax_item_group_id' => $journal->tax_item_group_id,
                'lines' => $journal->lines ?? [],
                'posted_by_name' => $journal->postedBy?->name,
                'created_at' => optional($journal->created_at)->toDateTimeString(),
            ],
        ]);
    }

    public function destroyJournal(ItemIssueJournal $journal): JsonResponse
    {
        $journal->delete();

        return response()->json([
            'status' => true,
            'message' => 'Journal deleted successfully.',
        ]);
    }
 
    private function extractOnHandQty(array $result): float
    {
        $keys = ['AvailPhysical', 'availPhysical', 'PhysicalAvailableQuantity', 'OnHandQty', 'Qty', 'qty'];
 
        foreach ($keys as $key) {
            if (isset($result[$key]) && is_numeric($result[$key])) {
                return (float) $result[$key];
            }
        }
 
        if (isset($result['data']) && is_array($result['data'])) {
            foreach ($keys as $key) {
                if (isset($result['data'][$key]) && is_numeric($result['data'][$key])) {
                    return (float) $result['data'][$key];
                }
            }
 
            if (isset($result['data']['value']) && is_array($result['data']['value'])) {
                $first = $result['data']['value'][0] ?? [];
                foreach ($keys as $key) {
                    if (isset($first[$key]) && is_numeric($first[$key])) {
                        return (float) $first[$key];
                    }
                }
            }
        }
 
        return 0.0;
    }
 
    private function normalizeUnits(array $result): array
    {
        // D365 returns a plain array: [{ "$id": "1", "Unit Id": "NOS" }, ...]
        $rows = [];
 
        if (array_is_list($result) && count($result) > 0 && is_array($result[0])) {
            $rows = $result;
        } elseif (isset($result['data']) && is_array($result['data'])) {
            $rows = $result['data'];
        }
 
        return array_values(array_filter(array_map(function ($row) {
            $id   = $row['Unit Id'] ?? $row['d365_unit_id'] ?? $row['Symbol'] ?? $row['UnitId'] ?? '';
            $name = $row['unit_name'] ?? $row['Description'] ?? $row['UnitName'] ?? $id;
            return $id !== '' ? ['id' => $id, 'name' => $name] : null;
        }, $rows)));
    }
 
    private function extractJournalId(array $result): ?string
    {
        $possibleKeys = [
            'JournalId',
            'JournalID',
            'journalId',
            'ItemJournalId',
            'ItemIssueJournalId',
            'ParmId',
            'parmId',
            'Voucher',
            'voucher',
            'RequestId',
        ];
 
        return $this->searchForScalarValue($result, $possibleKeys);
    }
 
    private function searchForScalarValue(array $payload, array $possibleKeys): ?string
    {
        foreach ($possibleKeys as $key) {
            if (isset($payload[$key]) && is_scalar($payload[$key])) {
                return (string) $payload[$key];
            }
        }
 
        foreach ($payload as $value) {
            if (!is_array($value)) {
                continue;
            }
 
            $found = $this->searchForScalarValue($value, $possibleKeys);
 
            if ($found !== null) {
                return $found;
            }
        }
 
        return null;
    }
 
    private function resolveCompanyDataAreaId(string $company): string
    {
        return trim($company);
    }
 
    private function isFailedD365Response(array $result): bool
    {
        if (array_key_exists('Success', $result)) {
            return $result['Success'] === false;
        }
 
        return false;
    }
 
    private function extractD365ErrorMessage(array $result): string
    {
        $parts = [];
 
        foreach (['ErrorMessage', 'InfoMessage', 'Message', 'message'] as $key) {
            if (!isset($result[$key]) || !is_scalar($result[$key])) {
                continue;
            }
 
            $value = trim((string) $result[$key]);
 
            if ($value !== '') {
                $parts[] = $value;
            }
        }
 
        if ($parts !== []) {
            return implode(' ', array_unique($parts));
        }
 
        return 'D365 rejected the item issue request.';
    }
 
    private function generateRequestId(): string
    {
        $year = now()->format('Y');
        $prefix = "IS{$year}";

        $latestRequestId = ItemIssueJournal::query()
            ->where('request_id', 'like', $prefix . '%')
            ->orderByDesc('request_id')
            ->value('request_id');

        $nextSequence = 1;

        if (is_string($latestRequestId) && preg_match('/^' . preg_quote($prefix, '/') . '(\d{4})$/', $latestRequestId, $matches)) {
            $nextSequence = ((int) $matches[1]) + 1;
        }

        return sprintf('%s%04d', $prefix, $nextSequence);
    }
}
 
 
 

