<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            abort(403, 'You do not have permission for this action.');
        }

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $companyValue = $request->query('company');
        if ($companyValue === null) {
            $companyValue = $request->route('company');
        }
        if ($companyValue === null) {
            $companyValue = $user->accessibleCompanyD365Codes()->first();
        }

        $company = Company::resolveFromMixed($companyValue);
        if ($company && $user->hasPermissionForCompany($company, 'Super.access')) {
            return $next($request);
        }

        abort(403, 'You do not have permission for this action.');
    }
}
