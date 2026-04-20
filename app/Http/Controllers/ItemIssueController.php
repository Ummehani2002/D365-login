<?php
namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Project;
use App\Services\D365ItemIssueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ItemIssueController extends Controller
{
    public function index()
    {
        $companies = Company::query()
            ->select(['id', 'd365_id', 'name'])
            ->whereNotNull('d365_id')
            ->orderBy('name')
            ->get();

        $projects = Project::query()
            ->select(['id', 'd365_id', 'name'])
            ->orderBy('name')
            ->get();

        return view('modules.project-management.item-issue.index', [
            'companies' => $companies,
            'projects' => $projects,
        ]);
    }

    public function lookupItems(Request $request, D365ItemIssueService $service): JsonResponse
    {
        $validated = $request->validate([
            'company' => ['required', 'string', 'max:20'],
            'ItemId' => ['nullable', 'string', 'max:100'],
        ]);

        try {
            $data = $service->lookupItems($this->resolveCompanyDataAreaId($validated['company']), $validated['ItemId'] ?? null);

            return response()->json([
                'status' => true,
                'message' => 'Items fetched from D365.',
                'data' => $data,
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'status' => false,
                'message' => 'Item lookup failed.',
                'error' => $e->getMessage(),
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

            return response()->json([
                'status' => false,
                'message' => 'Project lookup failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function post(Request $request, D365ItemIssueService $service): JsonResponse
    {
        $validated = $request->validate([
            'ui_only' => ['nullable', 'boolean'],
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

        // UI-only mode skips D365 call and returns a simulated successful post.
        if (($validated['ui_only'] ?? false) === true) {
            $journalId = 'TEST-' . now()->format('YmdHis');

            return response()->json([
                'status' => true,
                'message' => 'UI test mode: item issue simulated successfully.',
                'request_id' => $requestId,
                'journal_id' => $journalId,
                'response_preview' => 'UI only mode, no D365 call made.',
                'data' => [
                    '_request' => [
                        'DataAreaId' => $dataAreaId,
                        'ItemIssueHeader' => [
                            'RequestId' => $requestId,
                            'Description' => $validated['description'],
                            'InventSiteId' => $inventSiteId,
                            'InventLocationId' => $inventLocationId,
                        ],
                        'ItemIssueLines' => $validated['lines'],
                    ],
                ],
            ]);
        }

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

            return response()->json([
                'status' => true,
                'message' => 'Item issue posted to D365.',
                'request_id' => $requestId,
                'journal_id' => $journalId,
                'response_preview' => json_encode($result, JSON_UNESCAPED_SLASHES),
                'data' => $result,
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'status' => false,
                'message' => 'Item issue post failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
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
        return 'REQ' . now()->format('YmdHis');
    }
}
