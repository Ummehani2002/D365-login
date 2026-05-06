<?php

namespace App\Providers;

use App\Console\Commands\ServeCommand;
use App\Models\Company;
use App\Services\Rbac\MenuAccessService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->extend('command.serve', fn () => $this->app->make(ServeCommand::class));
    }

    public function boot(): void
    {
        Event::listen(
            \SocialiteProviders\Manager\SocialiteWasCalled::class,
            \SocialiteProviders\Microsoft\MicrosoftExtendSocialite::class.'@handle'
        );

        View::composer('*', function ($view) {
            $user = Auth::user();

            if (! $user) {
                $view->with('globalCompanyOptions', collect());
                $view->with('globalSelectedCompany', '');
                $view->with('authIsSuperAdmin', false);
                $view->with('authCanAccessMasters', false);
                $view->with('authShowMastersSettingsNav', false);
                $view->with('canItemIssue', false);
                $view->with('canPr', false);
                $view->with('canGrn', false);
                $view->with('canModulesGeneral', false);
                return;
            }

            $companies = collect();

            try {
                if (Schema::hasTable('companies')) {
                    $companies = Company::query()
                        ->select(['d365_id', 'name'])
                        ->whereNotNull('d365_id')
                        ->orderBy('name')
                        ->get();
                }
            } catch (Throwable) {
                $companies = collect();
            }

            if ($user && ! $user->isSuperAdmin()) {
                try {
                    $allowedCodes = $user->accessibleCompanyD365Codes()
                        ->map(fn (mixed $code) => strtoupper(trim((string) $code)))
                        ->filter()
                        ->unique()
                        ->values();

                    $companies = $companies->filter(function ($company) use ($allowedCodes) {
                        $code = strtoupper(trim((string) ($company->d365_id ?? '')));
                        return $code !== '' && $allowedCodes->contains($code);
                    })->values();
                } catch (Throwable) {
                    $companies = collect();
                }
            }

            $selectedCompany = strtoupper(trim((string) request()->query('company', '')));
            if (($selectedCompany === '' || ! $companies->contains(fn ($company) => strtoupper((string) $company->d365_id) === $selectedCompany)) && $companies->isNotEmpty()) {
                $preferred = $companies->first(fn ($company) => strtoupper((string) ($company->d365_id ?? '')) === 'ML')
                    ?? $companies->first(fn ($company) => strtoupper((string) ($company->d365_id ?? '')) === 'PS')
                    ?? $companies->first();
                $selectedCompany = strtoupper((string) ($preferred->d365_id ?? ''));
            }

            $view->with('globalCompanyOptions', $companies);
            $view->with('globalSelectedCompany', $selectedCompany);
            $isSuperAdmin = false;
            $canAccessMasters = false;
            if ($user) {
                try {
                    $isSuperAdmin = $user->isSuperAdmin();
                    $canAccessMasters = $user->canAccessMasters();
                } catch (Throwable) {
                    $isSuperAdmin = false;
                    $canAccessMasters = false;
                }
            }
            $view->with('authIsSuperAdmin', $isSuperAdmin);
            $view->with('authCanAccessMasters', $canAccessMasters);
            $view->with('authShowMastersSettingsNav', $canAccessMasters);

            if ($user) {
                try {
                    /** @var MenuAccessService $menuAccessService */
                    $menuAccessService = app(MenuAccessService::class);
                    $selectedCompanyModel = $menuAccessService->resolveCompanyFromCode($selectedCompany);
                    $visibility = $menuAccessService->menuVisibilityForUser($user, $selectedCompanyModel);

                    $canItemIssue = (bool) ($visibility['modules.project-management.item-issue'] ?? false);
                    $canPr = (bool) ($visibility['modules.procurement.purch-req'] ?? false);
                    $canGrn = (bool) ($visibility['modules.procurement.grn'] ?? false);

                    $view->with('canItemIssue', $canItemIssue);
                    $view->with('canPr', $canPr);
                    $view->with('canGrn', $canGrn);
                    $view->with('canModulesGeneral', false);
                } catch (Throwable) {
                    $view->with('canItemIssue', false);
                    $view->with('canPr', false);
                    $view->with('canGrn', false);
                    $view->with('canModulesGeneral', false);
                }
            } else {
                $view->with('canItemIssue', false);
                $view->with('canPr', false);
                $view->with('canGrn', false);
                $view->with('canModulesGeneral', false);
            }
        });
    }
}
