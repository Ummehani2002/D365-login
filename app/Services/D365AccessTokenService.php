<?php

namespace App\Services;

use App\Models\D365Token;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class D365AccessTokenService
{
    /**
     * Returns a cached Azure token when still valid, otherwise fetches a new one and persists it.
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
     */
    public function fetchAndStoreToken(string $generatedBy = 'system'): string
    {
        $tenantId = (string) config('services.d365.tenant_id');
        $clientId = (string) config('services.d365.client_id');
        $clientSecret = (string) config('services.d365.client_secret');
        $scope = (string) config('services.d365.scope');

        if (! $tenantId || ! $clientId || ! $clientSecret || ! $scope) {
            throw new RuntimeException('D365 credentials are not fully configured in .env');
        }

        $tokenUrl = "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/token";

        $response = Http::asForm()->post($tokenUrl, [
            'grant_type' => 'client_credentials',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'scope' => $scope,
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Failed to get Azure access token: '.$response->status().' - '.$response->body());
        }

        $accessToken = $response->json('access_token');
        $expiresIn = (int) ($response->json('expires_in') ?? 3599);

        if (! $accessToken) {
            throw new RuntimeException('Azure token response did not include access_token.');
        }

        D365Token::create([
            'access_token' => $accessToken,
            'expires_at' => now()->addSeconds($expiresIn),
            'generated_by' => $generatedBy,
        ]);

        return $accessToken;
    }

    /**
     * If the current DB token expires within this many seconds, fetch a new one (no-op when still fresh).
     */
    public function refreshIfExpiringWithin(int $secondsThreshold = 600): void
    {
        $latest = D365Token::query()->latest('id')->first();

        if ($latest === null) {
            $this->fetchAndStoreToken();

            return;
        }

        if ($latest->expires_at->lte(now()->addSeconds($secondsThreshold))) {
            $this->fetchAndStoreToken();
        }
    }

    /**
     * Runs an HTTP request with a bearer token; on 401, forces a new token and retries once.
     *
     * @param  callable(string): Response  $callback
     */
    public function requestWithBearerRetry(callable $callback): Response
    {
        $response = $callback($this->getAccessToken());

        if ($response->status() === 401) {
            $this->fetchAndStoreToken();

            return $callback($this->getAccessToken());
        }

        return $response;
    }
}
