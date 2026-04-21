<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class D365ProjectService
{
    public function __construct(
        protected D365AccessTokenService $tokens,
    ) {}

    public function fetchProjects(): array
    {
        $baseUrl = rtrim((string) config('services.d365.base_url'), '/');
        $projectsPath = (string) config('services.d365.projects_path', '/projects');

        if ($baseUrl === '') {
            throw new RuntimeException('D365 base URL is not configured.');
        }

        $url = $baseUrl.'/'.ltrim($projectsPath, '/');

        $response = $this->tokens->requestWithBearerRetry(
            fn (string $token) => Http::withToken($token)
                ->acceptJson()
                ->get($url)
        );

        if ($response->failed()) {
            throw new RuntimeException('D365 projects API failed: '.$response->status());
        }

        $payload = $response->json();
        $records = $payload['value'] ?? $payload;

        if (! is_array($records)) {
            return [];
        }

        $normalized = [];

        foreach ($records as $record) {
            if (! is_array($record)) {
                continue;
            }

            $projectId = $record['ProjectId']
                ?? $record['ProjId']
                ?? $record['projectId']
                ?? $record['id']
                ?? null;

            $name = $record['Name']
                ?? $record['ProjectName']
                ?? $record['name']
                ?? $record['description']
                ?? null;

            $companyD365Id = $record['DataAreaId']
                ?? $record['dataAreaId']
                ?? $record['CompanyId']
                ?? $record['companyId']
                ?? $record['LegalEntityId']
                ?? null;

            if (! $projectId || ! $name || ! $companyD365Id) {
                continue;
            }

            $normalized[] = [
                'd365_id' => (string) $projectId,
                'name' => (string) $name,
                'company_d365_id' => (string) $companyD365Id,
            ];
        }

        return $normalized;
    }
}
