## Microsoft SSO Setup (Laravel)

### Prerequisites
- PHP / Composer installed
- Laravel project running
- Microsoft Entra app registration created

### Environment variables
Add in `.env`:
```env
MICROSOFT_CLIENT_ID=your-client-id
MICROSOFT_CLIENT_SECRET=your-client-secret
MICROSOFT_TENANT_ID=your-tenant-id
MICROSOFT_REDIRECT_URI=http://localhost:9876/auth/microsoft/callback

config/services.php

'microsoft' => [
    'client_id' => env('MICROSOFT_CLIENT_ID'),
    'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
    'redirect' => env('MICROSOFT_REDIRECT_URI'),
    'tenant' => env('MICROSOFT_TENANT_ID'),

    Route::get('/auth/microsoft/redirect', [MicrosoftAuthController::class, 'redirect'])->name('microsoft.redirect');
Route::get('/auth/microsoft/callback', [MicrosoftAuthController::class, 'callback'])->name('microsoft.callback');
],

Test flow
Open login page
Click "Sign in with Microsoft"
Complete Microsoft login
Confirm redirect to dashboard



---

