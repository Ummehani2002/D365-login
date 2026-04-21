<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class D365CompanyService
{
    public function __construct(
        protected D365AccessTokenService $tokens,
    ) {}

    public function fetchCompanies(): array
    {
        $baseUrl = rtrim((string) config('services.d365.base_url'), '/');
        $companiesPath = (string) config('services.d365.companies_path', '/companies');

        if (empty($baseUrl)) {
            throw new RuntimeException('D365 base URL is not configured.');
        }

        $url = $baseUrl.$companiesPath;

        $response = $this->tokens->requestWithBearerRetry(
            fn (string $token) => Http::withToken($token)
                ->acceptJson()
                ->get($url)
        );

        if ($response->failed()) {
            throw new RuntimeException('D365 companies API failed: '.$response->status());
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

            $d365Id = $record['id']
                ?? $record['company']
                ?? $record['dataAreaId']
                ?? null;

            $name = $record['name']
                ?? $record['companyName']
                ?? $record['company']
                ?? null;

            if (! $d365Id || ! $name) {
                continue;
            }

            $normalized[] = [
                'd365_id' => (string) $d365Id,
                'name' => (string) $name,
            ];
        }

        return $normalized;
    }
}
