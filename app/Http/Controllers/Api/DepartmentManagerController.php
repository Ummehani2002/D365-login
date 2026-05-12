<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DepartmentManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentManagerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $company = $this->resolveScopedCompany(
            $request->user(),
            (string) $request->query('company', $request->query('company_id', ''))
        );

        $query = DepartmentManager::query()
            ->when($company, fn ($q) => $q->where('company_id', strtoupper((string) $company->d365_id)))
            ->orderBy('employee_name');

        return response()->json([
            'status' => true,
            'message' => 'Department managers fetched successfully.',
            'data' => $query->get(),
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
            'employee_name' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'max:255'],
            'company_id' => ['required', 'string', 'max:100'],
        ]);

        $row = DepartmentManager::updateOrCreate(
            [
                'company_id' => strtoupper((string) $company->d365_id),
                'employee_name' => trim((string) $validated['employee_name']),
                'department' => trim((string) $validated['department']),
            ],
            [
                'company_id' => strtoupper((string) $company->d365_id),
            ]
        );

        return response()->json([
            'status' => true,
            'message' => $row->wasRecentlyCreated
                ? 'Department manager created successfully.'
                : 'Department manager already exists.',
            'data' => $row,
        ], $row->wasRecentlyCreated ? 201 : 200);
    }

    public function destroy(Request $request, DepartmentManager $department_manager): JsonResponse
    {
        $this->assertCompanyAccess((string) $department_manager->company_id);
        $department_manager->delete();

        return response()->json([
            'status' => true,
            'message' => 'Department manager deleted successfully.',
        ]);
    }
}
