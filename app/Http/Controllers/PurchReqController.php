<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\PurchReqJournal;
use App\Services\D365PurchReqService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class PurchReqController extends Controller
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
            return redirect()->route('modules.procurement.purch-req', [
                'company' => strtoupper((string) $selectedCompany->d365_id),
            ]);
        }

        $journals = PurchReqJournal::query()
            ->with('postedBy:id,name')
            ->when($selectedCompany, function ($query) use ($selectedCompany) {
                $query->where('company', $selectedCompany->d365_id);
            })
            ->orderByDesc('created_at')
            ->get();

        return view('modules.procurement.purch-req.index', [
            'companies' => $companies,
            'journals'  => $journals,
            'currentCompanyCode' => $selectedCompany?->d365_id,
        ]);
    }

    public function post(Request $request, D365PurchReqService $service): JsonResponse
    {
        try {
            set_time_limit(60);

            $validated = $request->validate([
                'draft_id'                    => ['nullable', 'integer', 'exists:purch_req_journals,id'],
                'company'                     => ['required', 'string', 'max:20'],
                'buying_legal_entity'         => ['nullable', 'string', 'max:20'],
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

            $draftId = isset($validated['draft_id']) ? (int) $validated['draft_id'] : null;
            $draft = null;
            if ($draftId) {
                $draft = PurchReqJournal::query()
                    ->where('id', $draftId)
                    ->whereNull('request_id')
                    ->whereNull('pr_no')
                    ->first();
            }

            if ($draft) {
                $draft->update([
                    'request_id'    => $requestId,
                    'pr_no'         => $prNo,
                    'company'       => $validated['company'],
                    'buying_legal_entity' => $validated['buying_legal_entity'] ?? $validated['company'],
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
                $journal = $draft->fresh();
            } else {
                $journal = PurchReqJournal::create([
                    'request_id'    => $requestId,
                    'pr_no'         => $prNo,
                    'company'       => $validated['company'],
                    'buying_legal_entity' => $validated['buying_legal_entity'] ?? $validated['company'],
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
            }

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

    public function saveDraft(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company'                     => ['nullable', 'string', 'max:20'],
            'buying_legal_entity'         => ['nullable', 'string', 'max:20'],
            'pr_date'                     => ['nullable', 'date'],
            'warehouse'                   => ['nullable', 'string', 'max:100'],
            'pool_id'                     => ['nullable', 'string', 'max:100'],
            'contact_name'                => ['nullable', 'string', 'max:255'],
            'remarks'                     => ['nullable', 'string', 'max:500'],
            'department'                  => ['nullable', 'string', 'max:255'],
            'lines'                       => ['nullable', 'array'],
            'lines.*.item_category'       => ['nullable', 'string', 'max:100'],
            'lines.*.item_id'             => ['nullable', 'string', 'max:100'],
            'lines.*.item_description'    => ['nullable', 'string', 'max:255'],
            'lines.*.required_date'       => ['nullable', 'date'],
            'lines.*.unit'                => ['nullable', 'string', 'max:30'],
            'lines.*.qty'                 => ['nullable', 'numeric', 'gt:0'],
            'lines.*.currency'            => ['nullable', 'string', 'max:10'],
            'lines.*.rate'                => ['nullable', 'numeric', 'min:0'],
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

        $attachmentsForDb = array_map(fn ($a) => [
            'file_name'    => $a['file_name'],
            'file_type'    => $a['file_type'],
            'mime_type'    => $a['mime_type'] ?? null,
            'size_bytes'   => $a['size_bytes'] ?? null,
            'file_content' => $a['file_content'],
        ], $validated['attachments'] ?? []);

        $journal = PurchReqJournal::create([
            'request_id'    => null,
            'pr_no'         => null,
            'company'       => $validated['company'] ?? null,
            'buying_legal_entity' => $validated['buying_legal_entity'] ?? ($validated['company'] ?? null),
            'pr_date'       => $validated['pr_date'] ?? null,
            'warehouse'     => $validated['warehouse'] ?? null,
            'pool_id'       => $validated['pool_id'] ?? null,
            'contact_name'  => $validated['contact_name'] ?? null,
            'remarks'       => $validated['remarks'] ?? null,
            'department'    => $validated['department'] ?? null,
            'lines'         => $validated['lines'] ?? [],
            'attachments'   => $attachmentsForDb,
            'd365_response' => null,
            'posted_by'     => auth()->id(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'PR saved as draft.',
            'journal_id' => $journal->id,
        ]);
    }

    public function updateDraft(Request $request, PurchReqJournal $journal): JsonResponse
    {
        if ($journal->request_id || $journal->pr_no) {
            return response()->json([
                'status' => false,
                'message' => 'Submitted PR cannot be edited.',
            ], 422);
        }

        $validated = $request->validate([
            'company'                     => ['nullable', 'string', 'max:20'],
            'buying_legal_entity'         => ['nullable', 'string', 'max:20'],
            'pr_date'                     => ['nullable', 'date'],
            'warehouse'                   => ['nullable', 'string', 'max:100'],
            'pool_id'                     => ['nullable', 'string', 'max:100'],
            'contact_name'                => ['nullable', 'string', 'max:255'],
            'remarks'                     => ['nullable', 'string', 'max:500'],
            'department'                  => ['nullable', 'string', 'max:255'],
            'lines'                       => ['nullable', 'array'],
            'lines.*.item_category'       => ['nullable', 'string', 'max:100'],
            'lines.*.item_id'             => ['nullable', 'string', 'max:100'],
            'lines.*.item_description'    => ['nullable', 'string', 'max:255'],
            'lines.*.required_date'       => ['nullable', 'date'],
            'lines.*.unit'                => ['nullable', 'string', 'max:30'],
            'lines.*.qty'                 => ['nullable', 'numeric', 'gt:0'],
            'lines.*.currency'            => ['nullable', 'string', 'max:10'],
            'lines.*.rate'                => ['nullable', 'numeric', 'min:0'],
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

        $attachmentsForDb = array_map(fn ($a) => [
            'file_name'    => $a['file_name'],
            'file_type'    => $a['file_type'],
            'mime_type'    => $a['mime_type'] ?? null,
            'size_bytes'   => $a['size_bytes'] ?? null,
            'file_content' => $a['file_content'],
        ], $validated['attachments'] ?? []);

        $journal->update([
            'company'       => $validated['company'] ?? null,
            'buying_legal_entity' => $validated['buying_legal_entity'] ?? ($validated['company'] ?? null),
            'pr_date'       => $validated['pr_date'] ?? null,
            'warehouse'     => $validated['warehouse'] ?? null,
            'pool_id'       => $validated['pool_id'] ?? null,
            'contact_name'  => $validated['contact_name'] ?? null,
            'remarks'       => $validated['remarks'] ?? null,
            'department'    => $validated['department'] ?? null,
            'lines'         => $validated['lines'] ?? [],
            'attachments'   => $attachmentsForDb,
            'posted_by'     => auth()->id(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Draft updated successfully.',
            'journal_id' => $journal->id,
        ]);
    }

    public function showJournal(PurchReqJournal $journal): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => $journal,
            'is_draft' => !$journal->request_id && !$journal->pr_no,
        ]);
    }

    public function destroyJournal(PurchReqJournal $journal): JsonResponse
    {
        $journal->delete();

        return response()->json([
            'status' => true,
            'message' => 'PR deleted successfully.',
        ]);
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

    public function lookupCatalog(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company' => ['required', 'string', 'max:20'],
        ]);

        $companyCode = strtoupper(trim((string) $validated['company']));
        $company = Company::resolveFromMixed($companyCode);

        if (!$company) {
            return response()->json([
                'status' => false,
                'message' => 'Company not found.',
                'categories' => [],
                'items' => [],
            ], 404);
        }

        $categories = ItemCategory::query()
            ->where('company_id', (int) $company->id)
            ->orderBy('name')
            ->get()
            ->map(function (ItemCategory $category) {
                $code = trim((string) ($category->item_category_id ?? ''));
                $name = trim((string) ($category->name ?? ''));

                return [
                    'id' => $code !== '' ? $code : $name,
                    'name' => $name !== '' ? $name : $code,
                ];
            })
            ->filter(fn (array $category) => $category['id'] !== '')
            ->values();

        $categoryLookup = [];
        foreach ($categories as $category) {
            $idKey = strtolower(trim((string) ($category['id'] ?? '')));
            $nameKey = strtolower(trim((string) ($category['name'] ?? '')));
            if ($idKey !== '') {
                $categoryLookup[$idKey] = $category['id'];
            }
            if ($nameKey !== '') {
                $categoryLookup[$nameKey] = $category['id'];
            }
        }

        $items = Item::query()
            ->where('company_id', (int) $company->id)
            ->orderBy('item_name')
            ->get()
            ->map(function (Item $item) {
                $itemId = trim((string) ($item->item_id ?? ''));
                $itemName = trim((string) ($item->item_name ?? ''));
                $rawCategory = trim((string) ($item->item_category_id ?? ''));

                $normalizedCategory = $categoryLookup[strtolower($rawCategory)] ?? $rawCategory;

                return [
                    'id' => $itemId,
                    'name' => $itemName,
                    'category' => $normalizedCategory,
                ];
            })
            ->filter(fn (array $item) => $item['id'] !== '')
            ->values();

        return response()->json([
            'status' => true,
            'message' => 'Catalog fetched.',
            'categories' => $categories,
            'items' => $items,
        ]);
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
