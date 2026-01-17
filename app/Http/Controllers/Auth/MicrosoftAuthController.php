<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class MicrosoftAuthController extends Controller
{
    public function redirectToMicrosoft()
    {
        // Use microsoft driver (not microsoft-azure)
        return Socialite::driver('microsoft')
            ->scopes(['openid', 'profile', 'email', 'User.Read'])
            ->redirect();
    }

    public function handleMicrosoftCallback()
    {
        try {
            // Get user from Microsoft
            $microsoftUser = Socialite::driver('microsoft')->user();
            
            // Debug: Check what data we get
            // dd($microsoftUser);
            
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
            // More detailed error logging
            \Log::error('Microsoft auth failed: ' . $e->getMessage());
            
            return redirect('/')->withErrors([
                'error' => 'Microsoft authentication failed. Please try again. Error: ' . $e->getMessage()
            ]);
        }
    }
    
    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }
}