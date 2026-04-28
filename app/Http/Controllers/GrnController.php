<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\GrnJournal;
use App\Services\D365GrnService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Throwable;

class GrnController extends Controller
{
    public function index(Request $request)
    {
        $companies = Company::query()
            ->select(['id', 'd365_id', 'name'])
            ->whereNotNull('d365_id')
            ->orderBy('name')
            ->get();

        $defaultCompany = $companies->first(function (Company $company) {
            return strtoupper((string) $company->d365_id) === 'PS';
        }) ?? $companies->first();

        $requestedCompanyCode = strtoupper(trim((string) $request->query('company', '')));
        $selectedCompany = $companies->first(function (Company $company) use ($requestedCompanyCode) {
            return strtoupper((string) $company->d365_id) === $requestedCompanyCode;
        }) ?? $defaultCompany;

        if ($selectedCompany && strtoupper((string) $selectedCompany->d365_id) !== $requestedCompanyCode) {
            return redirect()->route('grns.index', [
                'company' => strtoupper((string) $selectedCompany->d365_id),
            ]);
        }

        $journals = collect();
        if (Schema::hasTable('grn_journals')) {
            $journals = GrnJournal::query()
                ->when($selectedCompany, function ($query) use ($selectedCompany) {
                    $query->where('company', $selectedCompany->d365_id);
                })
                ->orderByDesc('created_at')
                ->get();
        }

        return view('modules.procurement.grn.index', [
            'companies' => $companies,
            'currentCompanyCode' => $selectedCompany?->d365_id,
            'journals' => $journals,
        ]);
    }

    public function lookupHeaders(Request $request, D365GrnService $service): JsonResponse
    {
        $validated = $request->validate([
            'company' => ['required', 'string', 'max:20'],
            'packing_slip_id' => ['nullable', 'string', 'max:100'],
            'purch_id' => ['nullable', 'string', 'max:100'],
            'vendor_name' => ['nullable', 'string', 'max:200'],
            'project_id' => ['nullable', 'string', 'max:100'],
        ]);

        if (
            trim((string) ($validated['purch_id'] ?? '')) === '' &&
            trim((string) ($validated['vendor_name'] ?? '')) === '' &&
            trim((string) ($validated['project_id'] ?? '')) === ''
        ) {
            return response()->json([
                'status' => false,
                'message' => 'Fill at least one filter: PurchId, Vendor Name or Project ID.',
            ], 422);
        }

        try {
            $payload = [
                'DataAreaId' => trim($validated['company']),
                'PackingSlipID' => trim((string) ($validated['packing_slip_id'] ?? '')),
                'PurchId' => trim((string) ($validated['purch_id'] ?? '')),
                'VendName' => trim((string) ($validated['vendor_name'] ?? '')),
                'ProjId' => trim((string) ($validated['project_id'] ?? '')),
            ];

            $result = $service->lookupHeaders($payload);

            return response()->json([
                'status' => true,
                'message' => 'GRN headers fetched.',
                'rows' => $this->normalizeHeaders($result),
                'data' => $result,
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

    public function lookupLines(Request $request, D365GrnService $service): JsonResponse
    {
        $validated = $request->validate([
            'company' => ['required', 'string', 'max:20'],
            'request_id' => ['nullable', 'string', 'max:100'],
            'packing_slip_id' => ['nullable', 'string', 'max:100'],
            'purch_id' => ['nullable', 'string', 'max:100'],
            'vendor_name' => ['nullable', 'string', 'max:200'],
            'project_id' => ['nullable', 'string', 'max:100'],
        ]);

        try {
            $payload = [
                'DataAreaId' => trim($validated['company']),
                'RequestID' => trim((string) ($validated['request_id'] ?? '')),
                'PackingSlipID' => trim((string) ($validated['packing_slip_id'] ?? '')),
                'PurchId' => trim((string) ($validated['purch_id'] ?? '')),
                'VendName' => trim((string) ($validated['vendor_name'] ?? '')),
                'ProjId' => trim((string) ($validated['project_id'] ?? '')),
            ];

            $result = $service->lookupLines($payload);

            return response()->json([
                'status' => true,
                'message' => 'GRN lines fetched.',
                'rows' => $this->normalizeLines($result),
                'data' => $result,
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

    public function post(Request $request, D365GrnService $service): JsonResponse
    {
        $validated = $request->validate([
            'company' => ['required', 'string', 'max:20'],
            'request_id' => ['required', 'string', 'max:100'],
            'packing_slip_date' => ['required', 'date'],
            'purch_id' => ['required', 'string', 'max:100'],
            'project_id' => ['nullable', 'string', 'max:100'],
            'vendor_name' => ['nullable', 'string', 'max:200'],
            'packing_slip_id' => ['required', 'string', 'max:100'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.line_number' => ['required', 'integer', 'min:1'],
            'lines.*.purch_line_rec_id' => ['required', 'numeric'],
            'lines.*.receive_now' => ['required', 'numeric', 'min:0'],
        ]);

        $d365Payload = [
            '_request' => [
                'DataAreaId' => trim($validated['company']),
                'PurchPackHeader' => [
                    'RequestID' => trim($validated['request_id']),
                    'PackingSlipDate' => $validated['packing_slip_date'],
                    'PurchId' => trim($validated['purch_id']),
                    'PackingSlipID' => trim($validated['packing_slip_id']),
                ],
                'PurchPackLines' => array_map(function (array $line) {
                    return [
                        'LineNumber' => (int) $line['line_number'],
                        'PurchLineRecId' => (float) $line['purch_line_rec_id'],
                        'ReceiveNow' => (float) $line['receive_now'],
                    ];
                }, $validated['lines']),
            ],
        ];

        try {
            $result = $service->postGrn($d365Payload);

            if ($this->isFailedD365Response($result)) {
                return response()->json([
                    'status' => false,
                    'message' => 'GRN post failed.',
                    'error' => $this->extractD365ErrorMessage($result),
                    'data' => $result,
                ], 422);
            }

            $saved = null;
            if (Schema::hasTable('grn_journals')) {
                $saved = GrnJournal::create([
                    'request_id' => trim($validated['request_id']),
                    'company' => trim($validated['company']),
                    'purch_id' => trim($validated['purch_id']),
                    'project_id' => trim((string) ($validated['project_id'] ?? '')),
                    'vendor_name' => trim((string) ($validated['vendor_name'] ?? '')),
                    'packing_slip_id' => trim($validated['packing_slip_id']),
                    'document_date' => $validated['packing_slip_date'],
                    'lines' => $validated['lines'],
                    'd365_response' => $result,
                    'posted_by' => auth()->id(),
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'GRN posted successfully.',
                'journal_id' => $saved?->id,
                'data' => $result,
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'status' => false,
                'message' => 'GRN post failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function normalizeHeaders(array $result): array
    {
        $rows = $this->extractRows($result);

        return array_values(array_filter(array_map(function ($row) {
            if (!is_array($row)) {
                return null;
            }

            $requestId = (string) ($row['RequestID'] ?? $row['RequestId'] ?? $row['request_id'] ?? '');
            $packingSlipId = (string) ($row['PackingSlipID'] ?? $row['PackingSlipId'] ?? $row['packing_slip_id'] ?? '');
            $purchId = (string) ($row['PurchId'] ?? $row['PurchID'] ?? $row['purch_id'] ?? '');
            $packingSlipDate = (string) ($row['PackingSlipDate'] ?? $row['DocumentDate'] ?? $row['packing_slip_date'] ?? '');
            $vendorName = (string) ($row['VendName'] ?? $row['VendorName'] ?? $row['vendor_name'] ?? '');
            $projectId = (string) ($row['ProjId'] ?? $row['ProjectId'] ?? $row['project_id'] ?? '');

            if ($purchId === '' && $requestId === '') {
                return null;
            }

            return [
                'request_id' => $requestId,
                'packing_slip_id' => $packingSlipId,
                'purch_id' => $purchId,
                'vendor_name' => $vendorName,
                'project_id' => $projectId,
                'packing_slip_date' => $packingSlipDate,
            ];
        }, $rows)));
    }

    private function normalizeLines(array $result): array
    {
        $rows = $this->extractRows($result);

        return array_values(array_filter(array_map(function ($row) {
            if (!is_array($row)) {
                return null;
            }

            $lineNumber = $row['LineNumber'] ?? $row['LineNum'] ?? $row['line_number'] ?? null;
            $recId = $row['PurchLineRecId'] ?? $row['PurchLineRecID'] ?? $row['purch_line_rec_id'] ?? null;
            $itemId = (string) ($row['ItemId'] ?? $row['ItemID'] ?? $row['item_id'] ?? '');
            $itemName = (string) ($row['ItemName'] ?? $row['Name'] ?? $row['item_name'] ?? '');
            $orderedQty = $row['PurchQty'] ?? $row['OrderedQty'] ?? $row['ordered_qty'] ?? '';
            $remainingQty = $row['RemainPurchPhysical'] ?? $row['RemainingQty'] ?? $row['remaining_qty'] ?? '';
            $receiveNow = $row['ReceiveNow'] ?? $row['receive_now'] ?? $remainingQty ?? 0;

            if ($lineNumber === null || $recId === null) {
                return null;
            }

            return [
                'line_number' => (int) $lineNumber,
                'purch_line_rec_id' => (float) $recId,
                'item_id' => $itemId,
                'item_name' => $itemName,
                'ordered_qty' => is_numeric($orderedQty) ? (float) $orderedQty : $orderedQty,
                'remaining_qty' => is_numeric($remainingQty) ? (float) $remainingQty : $remainingQty,
                'receive_now' => (float) $receiveNow,
            ];
        }, $rows)));
    }

    private function extractRows(array $result): array
    {
        if (array_is_list($result)) {
            return $result;
        }

        foreach (['data', 'value', 'rows', 'result'] as $key) {
            if (isset($result[$key]) && is_array($result[$key])) {
                return $result[$key];
            }
        }

        return [];
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

        return $parts !== [] ? implode(' ', array_unique($parts)) : 'D365 rejected the GRN request.';
    }
}
