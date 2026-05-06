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
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Replace default serve command with Windows-safe custom command.
        $this->app->extend('command.serve', fn () => $this->app->make(ServeCommand::class));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(
            \SocialiteProviders\Manager\SocialiteWasCalled::class,
            \SocialiteProviders\Microsoft\MicrosoftExtendSocialite::class.'@handle'
        );

        View::composer('*', function ($view) {
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

            $user = Auth::user();

            $selectedCompany = strtoupper(trim((string) request()->query('company', '')));

            if ($selectedCompany === '' && $companies->isNotEmpty()) {
                $selectedCompany = strtoupper((string) ($companies->first()->d365_id ?? ''));
            }

            $view->with('globalCompanyOptions', $companies);
            $view->with('globalSelectedCompany', $selectedCompany);
            $isSuperAdmin = $user?->isSuperAdmin() ?? false;
            $view->with('authIsSuperAdmin', $isSuperAdmin);
            $view->with('authShowMastersSettingsNav', $user !== null);

            if ($user) {
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
                $view->with('canModulesGeneral', $canItemIssue || $canPr || $canGrn);
            } else {
                $view->with('canItemIssue', false);
                $view->with('canPr', false);
                $view->with('canGrn', false);
                $view->with('canModulesGeneral', false);
            }
        });
    }
}
