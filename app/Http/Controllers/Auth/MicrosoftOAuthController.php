<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;

class MicrosoftOAuthController extends Controller
{
    public function redirectToMicrosoft()
    {
        try {
            // Check if required configuration is present
            $clientId = config('services.microsoft.client_id');
            $clientSecret = config('services.microsoft.client_secret');
            $redirectUri = (string) config('services.microsoft.redirect');
            
            if (empty($clientId) || empty($clientSecret)) {
                \Log::error('Microsoft OAuth configuration missing', [
                    'client_id_set' => !empty($clientId),
                    'client_secret_set' => !empty($clientSecret),
                    'redirect_uri_set' => !empty($redirectUri),
                ]);
                return redirect('/')->with('error', 'Microsoft authentication is not properly configured. Please contact the administrator.');
            }
            
            return Socialite::driver('microsoft')
                ->redirectUrl($redirectUri)
                ->redirect();
        } catch (\Exception $e) {
            \Log::error('Microsoft OAuth redirect failed: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect('/')->with('error', 'Microsoft authentication is not properly configured. Please contact the administrator. Error: ' . $e->getMessage());
        }
    }
    
    public function handleMicrosoftCallback(Request $request)
    {
        try {
            $redirectUri = (string) config('services.microsoft.redirect');

            $microsoftUser = Socialite::driver('microsoft')
                ->redirectUrl($redirectUri)
                ->user();
            // ... rest of your code
            
            // Find or create user
            $user = User::where('email', $microsoftUser->getEmail())->first();
            
            if (!$user) {
                $user = User::create([
                    'name' => $microsoftUser->getName() ?? $microsoftUser->getNickname() ?? $microsoftUser->getEmail(),
                    'email' => $microsoftUser->getEmail(),
                    'microsoft_id' => $microsoftUser->getId(),
                    'password' => Hash::make(Str::random(24)),
                ]);
            } else {
                // Update Microsoft ID if missing
                if (empty($user->microsoft_id)) {
                    $user->microsoft_id = $microsoftUser->getId();
                    $user->save();
                }
            }
            
            // Log the user in
            Auth::login($user, true);
            
            // Redirect to dashboard
            return redirect()->route('dashboard');
        } catch (\Exception $e) {
            \Log::error('Microsoft OAuth callback failed: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return redirect('/')->with('error', 'Microsoft authentication failed. Please try again or contact support.');
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}