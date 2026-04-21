<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\D365Token;
use App\Services\D365ItemIssueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class SettingsController extends Controller
{
    public function tokenIndex(): \Illuminate\View\View
    {
        $token = D365Token::latest()->first();
        return view('settings.token', compact('token'));
    }

    public function credsIndex(): \Illuminate\View\View
    {
        $creds = AppSetting::d365Creds();
        return view('settings.credentials', compact('creds'));
    }

    public function saveCredentials(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'd365_tenant_id'     => ['required', 'string', 'max:255'],
            'd365_client_id'     => ['required', 'string', 'max:255'],
            'd365_client_secret' => ['required', 'string', 'max:500'],
            'd365_base_url'      => ['required', 'string', 'max:500'],
        ]);

        $baseUrl = rtrim($validated['d365_base_url'], '/');
        $scope   = $baseUrl . '/.default';

        $existing = AppSetting::d365Creds();
        $changed  = $existing['d365_tenant_id'] !== $validated['d365_tenant_id']
            || $existing['d365_client_id'] !== $validated['d365_client_id']
            || $existing['d365_client_secret'] !== $validated['d365_client_secret']
            || $existing['d365_base_url'] !== $baseUrl;

        AppSetting::set('d365_tenant_id', $validated['d365_tenant_id']);
        AppSetting::set('d365_client_id', $validated['d365_client_id']);
        AppSetting::set('d365_client_secret', $validated['d365_client_secret']);
        AppSetting::set('d365_base_url', $baseUrl);
        AppSetting::set('d365_scope', $scope);

        if ($changed) {
            D365Token::query()->delete();
        }

        return response()->json([
            'status' => true,
            'changed' => $changed,
            'message' => $changed
                ? 'Credentials updated. Token cleared - a fresh one will be fetched on the next API call.'
                : 'Credentials saved. Nothing changed - existing token remains active.',
        ]);
    }

    public function generateToken(Request $request, D365ItemIssueService $service): JsonResponse
    {
        try {
            $userName = auth()->user()?->name ?? 'manual';
            $service->fetchAndStoreToken($userName);

            $token = D365Token::latest()->first();

            return response()->json([
                'status'             => true,
                'message'            => 'Token generated successfully.',
                'expires_at'         => $token->expires_at->toIso8601String(),
                'generated_at_human' => $token->created_at->format('d M Y  H:i:s'),
                'expires_at_human'   => $token->expires_at->format('d M Y  H:i:s'),
                'duration_minutes'   => (int) round($token->created_at->diffInSeconds($token->expires_at) / 60),
                'seconds_remaining'  => $token->secondsRemaining(),
                'generated_by'       => $token->generated_by,
                'full_token'         => $token->access_token,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
