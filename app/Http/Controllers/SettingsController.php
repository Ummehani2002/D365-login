<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    public function apiConfiguration()
    {
        return view('settings.api-configuration.index', [
            'apiBearerToken' => (string) config('services.webapp.api_bearer_token'),
        ]);
    }

    public function generateApiToken(Request $request)
    {
        $plainToken = 'wapp_' . Str::random(48);
        $tokenHash = hash('sha256', $plainToken);
        $expiresAt = now()->addHour();

        Cache::put("webapp:api-token:{$tokenHash}", [
            'user_id' => $request->user()?->id,
        ], $expiresAt);

        return response()->json([
            'status' => true,
            'message' => 'Temporary API token generated. Valid for 1 hour.',
            'token' => $plainToken,
            'expires_at' => $expiresAt->toIso8601String(),
        ]);
    }
}
