<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Services\Rbac\MenuAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function __construct(
        private readonly MenuAccessService $menuAccessService
    ) {
    }

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string $permissionReference): Response
    {
        $user = $request->user();
        if (! $user) {
            abort(403, 'Unauthorized.');
        }

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $companyValue = $request->query('company');
        if ($companyValue === null) {
            $companyValue = $request->route('company');
        }
        if ($companyValue === null) {
            $companyValue = $request->input('company');
        }
        if ($companyValue === null) {
            $companyValue = $request->input('company_id');
        }
        if ($companyValue === null) {
            $companyValue = $request->json('company');
        }
        if ($companyValue === null) {
            $companyValue = $request->json('company_id');
        }
        if ($companyValue === null) {
            $companyValue = $user->accessibleCompanyD365Codes()->first();

            if (
                is_string($companyValue)
                && trim($companyValue) !== ''
                && $request->isMethod('GET')
                && $request->expectsHtml()
            ) {
                return redirect()->to($request->fullUrlWithQuery([
                    'company' => strtoupper(trim($companyValue)),
                ]));
            }
        }

        $company = Company::resolveFromMixed($companyValue);
        if (! $company) {
            abort(403, 'Selected company context is missing or invalid.');
        }

        if ($user->hasPermissionForCompany($company, 'super.access')) {
            return $next($request);
        }

        $permissionSlug = $this->resolvePermissionSlug($permissionReference);
        if ($permissionSlug === null || $permissionSlug === '') {
            abort(403, 'This action is not mapped to a permission.');
        }

        if (! $user->hasPermissionForCompany($company, $permissionSlug)) {
            abort(403, 'You do not have permission for this action.');
        }

        return $next($request);
    }

    private function resolvePermissionSlug(string $permissionReference): ?string
    {
        $ref = trim($permissionReference);
        if ($ref === '') {
            return null;
        }

        if (str_starts_with($ref, 'menu:')) {
            $menuKey = substr($ref, strlen('menu:'));

            return $this->menuAccessService->permissionSlugForMenuKey($menuKey);
        }

        return $ref;
    }
}

