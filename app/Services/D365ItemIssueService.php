<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class D365ItemIssueService
{
    public function __construct(
        protected D365AccessTokenService $tokens,
    ) {}

    public function lookupItems(string $dataAreaId, ?string $itemId = null): array
    {
        $payload = [
            'DataAreaId' => $dataAreaId,
            'ItemId' => $itemId ?? '',
        ];

        return $this->postToConfiguredPath('item_lookup_path', $payload);
    }

    public function lookupProjects(string $dataAreaId, ?string $projectId = null): array
    {
        $payload = [
            'DataAreaId' => $dataAreaId,
            'ProjectId' => $projectId ?? '',
        ];

        return $this->postToConfiguredPath('project_lookup_path', $payload);
    }

    public function postItemIssue(array $payload): array
    {
        return $this->postToConfiguredPath('item_issue_post_path', $payload);
    }

    protected function postToConfiguredPath(string $pathConfigKey, array $payload): array
    {
        $baseUrl = rtrim((string) config('services.d365.base_url'), '/');
        $path = (string) config("services.d365.{$pathConfigKey}");

        if ($baseUrl === '') {
            throw new RuntimeException('D365 base URL is not configured.');
        }

        if ($path === '') {
            throw new RuntimeException("D365 endpoint path is missing: {$pathConfigKey}");
        }

        $url = $baseUrl.'/'.ltrim($path, '/');

        $response = $this->tokens->requestWithBearerRetry(
            fn (string $token) => Http::withToken($token)
                ->acceptJson()
                ->asJson()
                ->post($url, $payload)
        );

        if ($response->failed()) {
            throw new RuntimeException(
                'D365 API failed with status '.$response->status().': '.$response->body()
            );
        }

        $json = $response->json();

        return is_array($json) ? $json : ['raw' => $response->body()];
    }

    public function getAccessToken(): string
    {
        return $this->tokens->getAccessToken();
    }

    public function fetchAndStoreToken(string $generatedBy = 'system'): string
    {
        return $this->tokens->fetchAndStoreToken($generatedBy);
    }
}
