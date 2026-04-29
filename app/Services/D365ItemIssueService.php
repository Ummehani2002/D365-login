<?php
 
namespace App\Services;
 
use App\Models\AppSetting;
use App\Models\D365Token;
use Illuminate\Support\Facades\Http;
use RuntimeException;
 
class D365ItemIssueService
{
    public function lookupItems(string $dataAreaId, ?string $projectId = null): array
    {
        $payload = [
            'DataAreaId' => $dataAreaId,
            'ProjectId'  => $projectId ?? '',
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
 
    public function lookupOnHand(string $dataAreaId, string $itemId): array
    {
        $payload = [
            'DataAreaId' => $dataAreaId,
            'ItemId'     => $itemId,
        ];
 
        return $this->postToConfiguredPath('item_onhand_path', $payload);
    }
 
    public function lookupUnits(string $dataAreaId, string $itemId = ''): array
    {
        $payload = [
            'DataAreaId' => $dataAreaId,
            'ItemId'     => $itemId,
        ];
 
        return $this->postToConfiguredPath('unit_lookup_path', $payload);
    }

    public function postPurchaseRequisition(array $payload): array
    {
        return $this->postToConfiguredPath('purchase_requisition_post_path', $payload);
    }
 
    protected function postToConfiguredPath(string $pathConfigKey, array $payload): array
    {
        $path    = (string) config("services.d365.{$pathConfigKey}");
 
        if ($path === '') {
            throw new RuntimeException("D365 endpoint path is missing: {$pathConfigKey}");
        }
 
        return $this->postToPath($path, $payload);
    }

    protected function postToPath(string $path, array $payload): array
    {
        $token = $this->getAccessToken();
        $baseUrl = rtrim((string) AppSetting::get('d365_base_url'), '/');

        if ($baseUrl === '') {
            throw new RuntimeException('D365 base URL is not configured. Go to Settings > D365 Credentials and save your credentials first.');
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->asJson()
            ->connectTimeout(10)
            ->timeout(25)
            ->post($baseUrl . '/' . ltrim($path, '/'), $payload);

        if ($response->failed()) {
            throw new RuntimeException(
                'D365 API failed with status ' . $response->status() . ': ' . $response->body()
            );
        }

        $json = $response->json();
        return is_array($json) ? $json : ['raw' => $response->body()];
    }
 
    /**
     * Returns a valid access token.
     * Uses the cached DB token when still valid; fetches a fresh one otherwise.
     */
    public function getAccessToken(): string
    {
        $cached = D365Token::current();
 
        if ($cached) {
            return $cached->access_token;
        }
 
        return $this->fetchAndStoreToken();
    }
 
    /**
     * Fetches a fresh token from Azure AD, saves it in the DB, and returns it.
     * Credentials are read from the DB (app_settings) first, then fall back to .env.
     */
    public function fetchAndStoreToken(string $generatedBy = 'system'): string
    {
        $creds = AppSetting::d365Creds();

        $tenantId     = $creds['d365_tenant_id'] ?? '';
        $clientId     = $creds['d365_client_id'] ?? '';
        $clientSecret = $creds['d365_client_secret'] ?? '';
        $scope        = $creds['d365_scope'] ?? '';
 
        if (! $tenantId || ! $clientId || ! $clientSecret || ! $scope) {
            throw new RuntimeException('D365 credentials are not configured. Go to Settings > D365 Credentials and save your credentials first.');
        }
 
        $tokenUrl = "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/token";
 
        $response = Http::asForm()
            ->connectTimeout(10)
            ->timeout(15)
            ->post($tokenUrl, [
                'grant_type'    => 'client_credentials',
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'scope'         => $scope,
            ]);
 
        if ($response->failed()) {
            throw new RuntimeException('Failed to get Azure access token: ' . $response->status() . ' - ' . $response->body());
        }
 
        $accessToken = $response->json('access_token');
        $expiresIn   = (int) ($response->json('expires_in') ?? 3599);
 
        if (! $accessToken) {
            throw new RuntimeException('Azure token response did not include access_token.');
        }
 
        D365Token::create([
            'access_token' => $accessToken,
            'expires_at'   => now()->addSeconds($expiresIn),
            'generated_by' => $generatedBy,
        ]);
 
        return $accessToken;
    }
}