<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Project;
use App\Services\D365ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProjectMasterController extends Controller
{
    public function index()
    {
        return view('masters.project.index', [
            'apiBearerToken' => (string) config('services.webapp.api_bearer_token'),
        ]);
    }

    public function syncFromD365(D365ProjectService $d365ProjectService): JsonResponse
    {
        try {
            $projects = $d365ProjectService->fetchProjects();

            $inserted = 0;
            $updated = 0;
            $createdCompanies = 0;

            DB::transaction(function () use ($projects, &$inserted, &$updated, &$createdCompanies): void {
                foreach ($projects as $row) {
                    $company = Company::resolveFromMixed($row['company_d365_id']);

                    if (!$company) {
                        $company = Company::create([
                            'company_id' => $row['company_d365_id'],
                            'name' => 'D365 ' . $row['company_d365_id'],
                            'created_by' => auth()->id(),
                        ]);
                        $createdCompanies++;
                    }

                    $existing = Project::query()->where('d365_id', $row['d365_id'])->first();

                    if ($existing) {
                        $existing->update(['company_id' => $company->id, 'name' => $row['name']]);
                        $updated++;
                    } else {
                        Project::create([
                            'company_id' => $company->id,
                            'd365_id' => $row['d365_id'],
                            'name' => $row['name'],
                            'created_by' => auth()->id(),
                        ]);
                        $inserted++;
                    }
                }
            });

            return response()->json([
                'status' => true,
                'message' => "D365 project sync complete. Inserted: {$inserted}, Updated: {$updated}, Companies created: {$createdCompanies}",
                'meta' => ['inserted' => $inserted, 'updated' => $updated, 'companies_created' => $createdCompanies, 'source_count' => count($projects)],
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'status' => false,
                'message' => 'Failed to sync projects from D365. Check API settings and logs.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
