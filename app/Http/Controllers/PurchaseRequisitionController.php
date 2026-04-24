<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Services\D365ItemIssueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class PurchaseRequisitionController extends Controller
{
    public function index()
    {
        $companies = Company::query()
            ->select(['id', 'd365_id', 'name'])
            ->whereNotNull('d365_id')
            ->orderBy('name')
            ->get();

        return view('modules.procurement.purchase-requisition.index', [
            'companies' => $companies,
        ]);
    }

    public function post(Request $request, D365ItemIssueService $service): JsonResponse
    {
        $validated = $request->validate([
            'company' => ['required', 'string', 'max:20'],
            'request_id' => ['required', 'string', 'max:50'],
            'pr_no' => ['required', 'string', 'max:50'],
            'pr_date' => ['required', 'date_format:Y-m-d'],
            'warehouse' => ['required', 'string', 'max:100'],
            'pool_id' => ['required', 'string', 'max:100'],
            'contact_name' => ['required', 'string', 'max:120'],
            'remarks' => ['nullable', 'string', 'max:500'],
            'department' => ['required', 'string', 'max:120'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.line_no' => ['required', 'integer', 'min:1'],
            'lines.*.item_category' => ['required', 'string', 'max:100'],
            'lines.*.item_id' => ['required', 'string', 'max:100'],
            'lines.*.item_description' => ['required', 'string', 'max:255'],
            'lines.*.required_date' => ['required', 'date_format:Y-m-d'],
            'lines.*.unit' => ['required', 'string', 'max:30'],
            'lines.*.qty' => ['required', 'numeric', 'gt:0'],
            'lines.*.currency' => ['required', 'string', 'max:10'],
            'lines.*.rate' => ['required', 'numeric', 'min:0'],
            'lines.*.candy_budget' => ['required', 'numeric', 'min:0'],
            'lines.*.budget_resource_id' => ['nullable', 'string', 'max:100'],
            'lines.*.warranty' => ['nullable', 'string', 'max:100'],
            'attachments' => ['nullable', 'array'],
            'attachments.*.purch_id' => ['nullable', 'string', 'max:100'],
            'attachments.*.file_name' => ['required_with:attachments', 'string', 'max:255'],
            'attachments.*.file_type' => ['required_with:attachments', 'string', 'in:pdf,doc,docx,xls,xlsx'],
            'attachments.*.file_content_base64' => ['required_with:attachments', 'string'],
        ]);

        $d365Payload = [
            '_request' => [
                'DataAreaId' => trim($validated['company']),
                'PurchReqHeader' => [
                    'RequestID' => $validated['request_id'],
                    'PRNo' => $validated['pr_no'],
                    'PRDate' => $validated['pr_date'],
                    'Warehouse' => $validated['warehouse'],
                    'PoolID' => $validated['pool_id'],
                    'ContactName' => $validated['contact_name'],
                    'Remarks' => $validated['remarks'] ?? '',
                    'Department' => $validated['department'],
                ],
                'PurchReqLines' => array_map(function (array $line) {
                    return [
                        'LineNo' => $line['line_no'],
                        'ItemCategory' => $line['item_category'],
                        'ItemId' => $line['item_id'],
                        'ItemDescription' => $line['item_description'],
                        'RequiredDate' => $line['required_date'],
                        'Unit' => $line['unit'],
                        'Qty' => $line['qty'],
                        'Currency' => $line['currency'],
                        'Rate' => $line['rate'],
                        'CandyBudget' => $line['candy_budget'],
                        'BudgetResourceId' => $line['budget_resource_id'] ?? '',
                        'Warranty' => $line['warranty'] ?? 'N/A',
                    ];
                }, $validated['lines']),
                'PurchReqAttachments' => array_map(function (array $attachment) {
                    return [
                        'purchId' => $attachment['purch_id'] ?? '',
                        'fileName' => $attachment['file_name'],
                        'fileType' => $attachment['file_type'],
                        'FileContentBase64' => $attachment['file_content_base64'] ?? '',
                    ];
                }, $validated['attachments'] ?? []),
            ],
        ];

        try {
            $result = $service->postPurchaseRequisition($d365Payload);

            return response()->json([
                'status' => true,
                'message' => 'Purchase requisition posted to D365.',
                'data' => $result,
            ]);
        } catch (Throwable $e) {
            report($e);

            $errorMessage = trim($e->getMessage()) !== '' ? $e->getMessage() : 'Unknown error during purchase requisition post.';

            return response()->json([
                'status' => false,
                'message' => $errorMessage,
                'error' => $errorMessage,
            ], 500);
        }
    }
}
