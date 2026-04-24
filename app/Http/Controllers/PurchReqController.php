<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\PurchReqJournal;
use App\Services\D365PurchReqService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class PurchReqController extends Controller
{
    public function index()
    {
        $companies = Company::query()
            ->select(['id', 'd365_id', 'name'])
            ->whereNotNull('d365_id')
            ->orderBy('name')
            ->get();

        $journals = PurchReqJournal::query()
            ->with('postedBy:id,name')
            ->orderByDesc('created_at')
            ->get();

        return view('modules.procurement.purch-req.index', [
            'companies' => $companies,
            'journals'  => $journals,
        ]);
    }

    public function post(Request $request, D365PurchReqService $service): JsonResponse
    {
        try {
            set_time_limit(60);

            $validated = $request->validate([
                'company'                     => ['required', 'string', 'max:20'],
                'pr_date'                     => ['required', 'date'],
                'warehouse'                   => ['required', 'string', 'max:100'],
                'pool_id'                     => ['required', 'string', 'max:100'],
                'contact_name'                => ['required', 'string', 'max:255'],
                'remarks'                     => ['nullable', 'string', 'max:500'],
                'department'                  => ['required', 'string', 'max:255'],
                'lines'                       => ['required', 'array', 'min:1'],
                'lines.*.item_category'       => ['required', 'string', 'max:100'],
                'lines.*.item_id'             => ['required', 'string', 'max:100'],
                'lines.*.item_description'    => ['nullable', 'string', 'max:255'],
                'lines.*.required_date'       => ['required', 'date'],
                'lines.*.unit'                => ['required', 'string', 'max:30'],
                'lines.*.qty'                 => ['required', 'numeric', 'gt:0'],
                'lines.*.currency'            => ['required', 'string', 'max:10'],
                'lines.*.rate'                => ['required', 'numeric', 'min:0'],
                'lines.*.candy_budget'        => ['nullable', 'numeric', 'min:0'],
                'lines.*.budget_resource_id'  => ['nullable', 'string', 'max:100'],
                'lines.*.warranty'            => ['nullable', 'string', 'max:100'],
                'attachments'                 => ['nullable', 'array'],
                'attachments.*.file_name'     => ['required', 'string', 'max:255'],
                'attachments.*.file_type'     => ['required', 'string', 'max:20'],
                'attachments.*.mime_type'     => ['nullable', 'string', 'max:100'],
                'attachments.*.size_bytes'    => ['nullable', 'numeric', 'min:0'],
                'attachments.*.file_content'  => ['required', 'string'],
                'attachments.*.purch_id'      => ['nullable', 'string', 'max:100'],
            ]);

            $requestId = $this->generatePRRequestId();
            $prNo      = $this->generatePRNo();

            $d365Payload = [
                '_request' => [
                    'DataAreaId'     => trim($validated['company']),
                    'PurchReqHeader' => [
                        'RequestID'   => $requestId,
                        'PRNo'        => $prNo,
                        'PRDate'      => $validated['pr_date'],
                        'Warehouse'   => $validated['warehouse'],
                        'PoolID'      => $validated['pool_id'],
                        'ContactName' => $validated['contact_name'],
                        'Remarks'     => $validated['remarks'] ?? '',
                        'Department'  => $validated['department'],
                    ],
                    'PurchReqLines' => array_map(function (array $line, int $idx) {
                        return [
                            'LineNo'           => $idx + 1,
                            'ItemCategory'     => $line['item_category'],
                            'ItemId'           => $line['item_id'],
                            'ItemDescription'  => $line['item_description'] ?? '',
                            'RequiredDate'     => $line['required_date'],
                            'Unit'             => $line['unit'],
                            'Qty'              => (float) $line['qty'],
                            'Currency'         => $line['currency'],
                            'Rate'             => (float) $line['rate'],
                            'CandyBudget'      => (float) ($line['candy_budget'] ?? 0),
                            'BudgetResourceId' => $line['budget_resource_id'] ?? '',
                            'Warranty'         => $line['warranty'] ?? 'N/A',
                        ];
                    }, $validated['lines'], array_keys($validated['lines'])),
                    'PurchReqAttachments' => array_map(function (array $att) {
                        return [
                            'purchId'           => $att['purch_id'] ?? '',
                            'fileName'          => $att['file_name'],
                            'fileType'          => $att['file_type'],
                            'FileContentBase64' => $att['file_content'],
                        ];
                    }, $validated['attachments'] ?? []),
                ],
            ];

            $result = $service->postPurchReq($d365Payload);

            if ($this->isFailedD365Response($result)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'PR submission failed.',
                    'error'   => $this->extractD365ErrorMessage($result),
                    'data'    => $result,
                ], 422);
            }

            $attachmentsForDb = array_map(fn ($a) => [
                'file_name'    => $a['file_name'],
                'file_type'    => $a['file_type'],
                'mime_type'    => $a['mime_type'] ?? null,
                'size_bytes'   => $a['size_bytes'] ?? null,
                'file_content' => $a['file_content'],
            ], $validated['attachments'] ?? []);

            $journal = PurchReqJournal::create([
                'request_id'    => $requestId,
                'pr_no'         => $prNo,
                'company'       => $validated['company'],
                'pr_date'       => $validated['pr_date'],
                'warehouse'     => $validated['warehouse'],
                'pool_id'       => $validated['pool_id'],
                'contact_name'  => $validated['contact_name'],
                'remarks'       => $validated['remarks'] ?? null,
                'department'    => $validated['department'],
                'lines'         => $validated['lines'],
                'attachments'   => $attachmentsForDb,
                'd365_response' => $result,
                'posted_by'     => auth()->id(),
            ]);

            return response()->json([
                'status'     => true,
                'message'    => 'Purchase Requisition submitted to D365.',
                'request_id' => $requestId,
                'pr_no'      => $prNo,
                'journal_id' => $journal->id,
                'data'       => $result,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'status'  => false,
                'message' => 'PR submission failed.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function lookupUnits(Request $request, D365PurchReqService $service): JsonResponse
    {
        set_time_limit(60);

        $validated = $request->validate([
            'company' => ['required', 'string', 'max:20'],
            'item_id' => ['nullable', 'string', 'max:100'],
        ]);

        try {
            $data = $service->lookupUnits(
                $this->resolveCompanyDataAreaId($validated['company']),
                $validated['item_id'] ?? ''
            );

            return response()->json([
                'status'  => true,
                'message' => 'Units fetched.',
                'units'   => $this->normalizeUnits($data),
                'data'    => $data,
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'status'  => false,
                'message' => 'Unit lookup failed.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function downloadAttachment(PurchReqJournal $journal, int $index): Response
    {
        $att = $this->resolveAttachment($journal, $index);

        $content  = base64_decode($att['file_content'] ?? '');
        $mime     = $att['mime_type'] ?? 'application/octet-stream';
        $fileName = $att['file_name'] ?? 'attachment';

        return response($content, 200, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
            'Content-Length'      => strlen($content),
        ]);
    }

    public function viewBase64(PurchReqJournal $journal, int $index): Response
    {
        $att      = $this->resolveAttachment($journal, $index);
        $b64      = $att['file_content'] ?? '';
        $fileName = $att['file_name'] ?? 'attachment';

        return response($b64, 200, [
            'Content-Type'        => 'text/plain; charset=utf-8',
            'Content-Disposition' => 'inline; filename="' . $fileName . '.base64.txt"',
        ]);
    }

    private function resolveAttachment(PurchReqJournal $journal, int $index): array
    {
        $attachments = $journal->attachments ?? [];

        if (!isset($attachments[$index])) {
            abort(404, 'Attachment not found.');
        }

        return $attachments[$index];
    }

    private function normalizeUnits(array $result): array
    {
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

    private function resolveCompanyDataAreaId(string $company): string
    {
        return trim($company);
    }

    private function generatePRRequestId(): string
    {
        $next = \DB::transaction(function () {
            $current = (int) \App\Models\AppSetting::get('purch_req_id_sequence', 0);
            $next    = $current + 1;
            \App\Models\AppSetting::set('purch_req_id_sequence', $next);

            return $next;
        });

        return 'REQ-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    private function generatePRNo(): string
    {
        $next = \DB::transaction(function () {
            $current = (int) \App\Models\AppSetting::get('purch_req_no_sequence', 0);
            $next    = $current + 1;
            \App\Models\AppSetting::set('purch_req_no_sequence', $next);

            return $next;
        });

        return 'PR-' . str_pad($next, 4, '0', STR_PAD_LEFT);
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

        return $parts !== [] ? implode(' ', array_unique($parts)) : 'D365 rejected the purchase requisition.';
    }
}
