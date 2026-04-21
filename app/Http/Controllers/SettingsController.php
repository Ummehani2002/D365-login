<?php

namespace App\Http\Controllers;

use App\Models\D365Token;
use App\Services\D365AccessTokenService;
use Illuminate\View\View;
use Throwable;

class SettingsController extends Controller
{
    public function index(D365AccessTokenService $tokens): View
    {
        try {
            $tokens->getAccessToken();
        } catch (Throwable $e) {
            report($e);
        }

        $token = D365Token::latest()->first();

        return view('settings.index', compact('token'));
    }
}
