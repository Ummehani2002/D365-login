<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\GrnJournal;
use App\Services\D365GrnService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class GrnController extends Controller
{
    public function index()
    {
        $companies = Company::query()
            ->select(['id', 'd365_id', 'name'])
            ->whereNotNull('d365_id')
            ->orderBy('name')
            ->get();

        $journals = GrnJournal::query()
            ->with('postedBy:id,name')
            ->latest()
            ->limit(50)
            ->get();

        return view('modules.procurement.grn.index', [
            'companies' => $companies,
            'journals' => $journals,
        ]);
    }

    public function search(Request $request, D365GrnService $service): JsonResponse
    {
        set_time_limit(60);

        $validated = $request->validate([
            'company'   => ['required', 'string', 'max:20'],
            'purch_id'  => ['nullable', 'string', 'max:100'],
        
            'vend_name' => ['nullable', 'string', 'max:255'],
            'proj_id'   => ['nullable', 'string', 'max:100'],
        ]);

        try {
            $company = trim($validated['company']);
            $purchId = trim((string) ($validated['purch_id'] ?? ''));
            $vendName = trim((string) ($validated['vend_name'] ?? ''));
            $projId = trim((string) ($validated['proj_id'] ?? ''));

            $raw = $service->lookup(
                $company,
                $purchId,
                $vendName,
                $projId
            );

            $rows = $this->normalizeRows($raw);
            $rows = $this->applySearchFilters(
                $rows,
                $purchId,
                $vendName,
                $projId
            );

            if ($rows === [] && ($vendName !== '' || $projId !== '')) {
                $fallbackRaw = $service->lookup($company, $purchId, '', '');
                $fallbackRows = $this->normalizeRows($fallbackRaw);
                $rows = $this->applySearchFilters($fallbackRows, $purchId, $vendName, $projId);
                if ($rows !== []) {
                    $raw = $fallbackRaw;
                }
            }

            return response()->json([
                'status'  => true,
                'message' => 'GRN data fetched from D365.',
                'rows'    => $rows,
                'data'    => $raw,
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'status' => false,
                'message' => 'GRN header lookup failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function view(Request $request)
    {
        $validated = $request->validate([
            'company' => ['required', 'string', 'max:20'],
            'purchase_id' => ['required', 'string', 'max:100'],
            'vendor_name' => ['nullable', 'string', 'max:255'],
            'project_id' => ['nullable', 'string', 'max:100'],
        ]);

        return view('modules.procurement.grn.view', [
            'company' => trim($validated['company']),
            'purchaseId' => trim($validated['purchase_id']),
            'vendorName' => trim((string) ($validated['vendor_name'] ?? '')),
            'projectId' => trim((string) ($validated['project_id'] ?? '')),
        ]);
    }

    public function lineDetails(Request $request, D365GrnService $service): JsonResponse
    {
        set_time_limit(60);

        $validated = $request->validate([
            'company'      => ['required', 'string', 'max:20'],
            'purchase_id'  => ['required', 'string', 'max:100'],
            'vendor_name'  => ['nullable', 'string', 'max:255'],
            'project_id'   => ['nullable', 'string', 'max:100'],
        ]);

        try {
            $raw = $service->lookupLines(
                trim($validated['company']),
                trim($validated['purchase_id'])
            );

            $lines = $this->normalizeLineRows($raw);

            return response()->json([
                'status' => true,
                'message' => 'GRN line details fetched from D365.',
                'header' => [
                    'purchase_order' => $validated['purchase_id'],
                    'vendor_name' => trim((string) ($validated['vendor_name'] ?? '')) ?: '-',
                    'project_id' => trim((string) ($validated['project_id'] ?? '')) ?: '-',
                    'packing_slip_id' => '',
                    'document_date' => '',
                ],
                'lines' => $lines,
                'data' => $raw,
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'status' => false,
                'message' => 'GRN line lookup failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function postPackingSlip(Request $request, D365GrnService $service): JsonResponse
    {
        set_time_limit(90);

        $validated = $request->validate([
            'company' => ['required', 'string', 'max:20'],
            'purchase_id' => ['required', 'string', 'max:100'],
            'packing_slip_id' => ['required', 'string', 'max:100'],
            'document_date' => ['required', 'date'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.line_number' => ['required', 'integer', 'min:1'],
            'lines.*.line_rec_id' => ['required'],
            'lines.*.receive_qty' => ['required', 'numeric', 'gte:0'],
        ]);

        try {
            $purchaseId = trim($validated['purchase_id']);
            $requestId = $this->generatePackingRequestId($purchaseId);

            $header = [
                'RequestID' => $requestId,
                'PackingSlipDate' => $validated['document_date'],
                'PurchId' => $purchaseId,
                'PackingSlipID' => trim($validated['packing_slip_id']),
            ];

            $lines = array_map(function (array $line) {
                return [
                    'LineNumber' => (int) $line['line_number'],
                    'PurchLineRecId' => (string) $line['line_rec_id'],
                    'ReceiveNow' => (float) $line['receive_qty'],
                ];
            }, $validated['lines']);

            foreach ($lines as $line) {
                if ((float) ($line['ReceiveNow'] ?? 0) < 0) {
                    return response()->json([
                        'status' => false,
                        'message' => 'ReceiveNow cannot be negative.',
                    ], 422);
                }
            }

            $raw = $service->postPackingSlip(trim($validated['company']), $header, $lines);

            $success = (bool) ($this->pickValue($raw, ['Success', 'success']) ?? false);
            $errorMessage = $this->pickValue($raw, ['ErrorMessage', 'errorMessage']);
            $infoMessage = $this->pickValue($raw, ['InfoMessage', 'infoMessage']);
            $packingSlipId = $this->pickValue($raw, ['PackingSlipId', 'packingSlipId']) ?: $header['PackingSlipID'];

            if (!$success) {
                return response()->json([
                    'status' => false,
                    'message' => $errorMessage ?: 'Posting failed in D365.',
                    'request_id' => $requestId,
                    'packing_slip_id' => $packingSlipId,
                    'data' => $raw,
                ], 422);
            }

            $journal = GrnJournal::query()->create([
                'request_id' => $requestId,
                'company' => trim($validated['company']),
                'purch_id' => $purchaseId,
                'project_id' => null,
                'vendor_name' => null,
                'packing_slip_id' => $packingSlipId,
                'document_date' => $validated['document_date'],
                'lines' => $lines,
                'd365_response' => $raw,
                'posted_by' => auth()->id(),
            ]);

            return response()->json([
                'status' => true,
                'message' => $infoMessage ?: 'Packing slip posted successfully.',
                'request_id' => $requestId,
                'packing_slip_id' => $packingSlipId,
                'journal_id' => $journal->id,
                'data' => $raw,
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage() !== '' ? $e->getMessage() : 'GRN post failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function normalizeRows(array $raw): array
    {
        $rows = $this->extractRows($raw);

        return array_map(function (array $row) {
            $purchIdLookupId = $this->pickValue($row, ['$id', 'id']);
            $purchIdLookupPurchId = $this->pickValue($row, ['Purch Id', 'PurchId', 'Purch_Id']);
            $purchIdLookupName = $this->pickValue($row, ['Purch name', 'PurchName', 'Purch_Name']);
            $purchIdLookupWarehouse = $this->pickValue($row, ['Project Warehouse', 'ProjectWarehouse', 'Warehouse']);

            if ($purchIdLookupPurchId !== null || $purchIdLookupName !== null || $purchIdLookupWarehouse !== null) {
                return [
                    'purchase_order'    => $purchIdLookupPurchId ?: '-',
                    'project_id'        => $purchIdLookupWarehouse ?: '-',
                    'vendor_name'       => $purchIdLookupName ?: '-',
                    'row_id'            => $purchIdLookupId ?: '-',
                    '_raw'              => $row,
                ];
            }

            $dataArea = $this->pickValue($row, ['DataAreaId', 'dataAreaId', 'DataArea', 'Company']);
            $requestId = $this->pickValue($row, ['RequestID', 'RequestId', 'ReqId', 'RequisitionId']);
            $packingSlipId = $this->pickValue($row, ['PackingSlipID', 'PackingSlipId', 'PackSlipId', 'SlipId']);
            $purchaseId = $this->pickValue($row, ['PurchId', 'PurchaseId', 'POId', 'PONumber']);
            $userName = $this->pickValue($row, ['UserName', 'UserId', 'CreatedBy', 'Worker', 'RequestedBy']);

            return [
                'purchase_order'    => $purchaseId ?: ($requestId ?: '-'),
                'project_id'        => $packingSlipId ?: ($dataArea ?: '-'),
                'vendor_name'       => $userName ?: '-',
                'row_id'            => $requestId ?: '-',
                '_raw'              => $row,
            ];
        }, $rows);
    }

    private function applySearchFilters(array $rows, string $purchId, string $vendName, string $projId): array
    {
        $purchNeedle = $this->normalizeForMatch($purchId);
        $vendorNeedle = $this->normalizeForMatch($vendName);
        $projectNeedle = $this->normalizeForMatch($projId);

        if ($purchNeedle === '' && $vendorNeedle === '' && $projectNeedle === '') {
            return $rows;
        }

        return array_values(array_filter($rows, function (array $row) use ($purchNeedle, $vendorNeedle, $projectNeedle) {
            $purchaseOrder = $this->normalizeForMatch((string) ($row['purchase_order'] ?? ''));
            $vendor = $this->normalizeForMatch((string) ($row['vendor_name'] ?? ''));
            $project = $this->normalizeForMatch((string) ($row['project_id'] ?? ''));

            $matchPurch = $purchNeedle === '' || str_contains($purchaseOrder, $purchNeedle);
            $matchVendor = $vendorNeedle === '' || $this->matchesLooseTokens($vendor, $vendorNeedle);
            $matchProject = $projectNeedle === '' || str_contains($project, $projectNeedle);

            return $matchPurch && $matchVendor && $matchProject;
        }));
    }

    private function normalizeForMatch(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9\s]+/u', ' ', $value) ?? $value;
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
        return trim($value);
    }

    private function matchesLooseTokens(string $haystack, string $needle): bool
    {
        if ($needle === '') {
            return true;
        }
        if (str_contains($haystack, $needle)) {
            return true;
        }

        $hayTokens = array_values(array_filter(explode(' ', $haystack)));
        $needleTokens = array_values(array_filter(explode(' ', $needle)));
        if ($needleTokens === []) {
            return true;
        }

        foreach ($needleTokens as $token) {
            if (mb_strlen($token) < 3) {
                continue;
            }

            $found = false;
            foreach ($hayTokens as $hayToken) {
                if (str_starts_with($hayToken, $token) || levenshtein($hayToken, $token) <= 1) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                return false;
            }
        }

        return true;
    }

    private function normalizeLineRows(array $raw): array
    {
        $rows = $this->extractRows($raw);

        return array_map(function (array $row) {
            return [
                'line_number'    => $this->pickValue($row, ['LineNumber', 'lineNumber', 'Line']) ?: '-',
                'item_id'        => $this->pickValue($row, ['ItemId', 'ItemID', 'itemId']) ?: '-',
                'name'           => $this->pickValue($row, ['Name', 'ItemName', 'Description']) ?: '-',
                'ordered_qty'    => $this->formatNumber($this->pickValue($row, ['PurchQty', 'OrderedQty', 'QtyOrdered'])),
                'remaining_qty'  => $this->formatNumber($this->pickValue($row, ['RemainPurchPhysical', 'RemainingQty', 'RemainQty'])),
                'receive_qty'    => '',
                'line_rec_id'    => $this->pickValue($row, ['LineRecId', 'lineRecId', 'PurchLineRecId']) ?: '',
                '_raw'           => $row,
            ];
        }, $rows);
    }

    private function extractRows(array $raw): array
    {
        foreach ($raw as $value) {
            if (!is_string($value)) {
                continue;
            }
            $decoded = $this->decodeJsonString($value);
            if (!is_array($decoded)) {
                continue;
            }
            $nested = $this->extractRows($decoded);
            if ($nested !== []) {
                return $nested;
            }
        }

        if (array_is_list($raw)) {
            $rows = array_values(array_filter($raw, fn ($row) => is_array($row)));
            if ($rows !== []) {
                return $rows;
            }
        }

        $containerKeys = [
            'data', 'value', 'rows', 'result', 'results', '_response', 'response',
            'Response', 'Result', 'return', 'Return',
        ];

        foreach ($containerKeys as $key) {
            if (!isset($raw[$key]) || !is_array($raw[$key])) {
                continue;
            }

            $nested = $this->extractRows($raw[$key]);
            if ($nested !== []) {
                return $nested;
            }
        }

        foreach ($raw as $value) {
            if (!is_array($value)) {
                continue;
            }

            $nested = $this->extractRows($value);
            if ($nested !== []) {
                return $nested;
            }
        }

        return $raw !== [] ? [$raw] : [];
    }

    private function pickValue(array $row, array $candidateKeys): ?string
    {
        $value = $this->pickValueRecursive($row, $candidateKeys);
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);
        return $trimmed === '' ? null : $trimmed;
    }

    private function pickValueRecursive(array $source, array $candidateKeys): string|int|float|null
    {
        $directMap = [];
        foreach ($source as $key => $value) {
            if (!is_scalar($value) && $value !== null) {
                continue;
            }
            $directMap[strtolower((string) $key)] = $value;
        }

        foreach ($candidateKeys as $key) {
            $lookup = strtolower($key);
            if (array_key_exists($lookup, $directMap)) {
                return $directMap[$lookup];
            }
        }

        foreach ($source as $value) {
            if (is_string($value)) {
                $decoded = $this->decodeJsonString($value);
                if (is_array($decoded)) {
                    $nested = $this->pickValueRecursive($decoded, $candidateKeys);
                    if ($nested !== null && $nested !== '') {
                        return $nested;
                    }
                }
            }
            if (!is_array($value)) {
                continue;
            }
            $nested = $this->pickValueRecursive($value, $candidateKeys);
            if ($nested !== null && $nested !== '') {
                return $nested;
            }
        }

        return null;
    }

    private function decodeJsonString(string $value): ?array
    {
        $trimmed = trim($value);
        if ($trimmed === '' || (($trimmed[0] ?? '') !== '{' && ($trimmed[0] ?? '') !== '[')) {
            return null;
        }

        $decoded = json_decode($trimmed, true);
        return is_array($decoded) ? $decoded : null;
    }

    private function formatNumber(?string $value): string
    {
        if ($value === null || $value === '') {
            return '0.00';
        }

        if (!is_numeric($value)) {
            return $value;
        }

        return number_format((float) $value, 2, '.', '');
    }

    private function generatePackingRequestId(string $purchaseId): string
    {
        $base = preg_replace('/[^A-Za-z0-9\-]/', '', strtoupper($purchaseId)) ?: 'REQ';
        return $base . '-' . now()->format('YmdHisv');
    }
}
