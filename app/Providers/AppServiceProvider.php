<?php

namespace App\Providers;

use App\Console\Commands\ServeCommand;
use App\Models\Company;
use App\Services\Rbac\MenuAccessService;
use FilesystemIterator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->extend('command.serve', fn () => $this->app->make(ServeCommand::class));
    }

    public function boot(): void
    {
        $this->registerNestedMigrationPaths();

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
            $isSuperAdmin = $user->isSuperAdmin();
            $isAdmin = $user->isCompanyAdmin();
            $hasFullAccess = $isSuperAdmin || $isAdmin;

            $accessibleCodes = $hasFullAccess
                ? collect()
                : $user->accessibleCompanyD365Codes()
                    ->map(fn (mixed $code) => strtoupper(trim((string) $code)))
                    ->filter(fn (string $code) => $code !== '')
                    ->unique()
                    ->values();

            try {
                if (Schema::hasTable('companies')) {
                    $query = Company::query()
                        ->select(['id', 'd365_id', 'name'])
                        ->whereNotNull('d365_id')
                        ->orderBy('name');

                    if (! $hasFullAccess) {
                        if ($accessibleCodes->isEmpty()) {
                            $query->whereRaw('1 = 0');
                        } else {
                            $query->whereIn(\DB::raw('UPPER(d365_id)'), $accessibleCodes->all());
                        }
                    }

                    $companies = $query->get();
                }
            } catch (Throwable) {
                $companies = collect();
            }

            $selectedCompany = strtoupper(trim((string) request()->query('company', '')));
            if (($selectedCompany === '' || ! $companies->contains(fn ($company) => strtoupper((string) $company->d365_id) === $selectedCompany)) && $companies->isNotEmpty()) {
                $preferred = $companies->first(fn ($company) => strtoupper((string) ($company->d365_id ?? '')) === 'ML')
                    ?? $companies->first(fn ($company) => strtoupper((string) ($company->d365_id ?? '')) === 'PS')
                    ?? $companies->first();
                $selectedCompany = strtoupper((string) ($preferred->d365_id ?? ''));
            }

            $selectedCompanyModel = $selectedCompany !== ''
                ? $companies->first(fn ($company) => strtoupper((string) ($company->d365_id ?? '')) === $selectedCompany)
                : null;

            /** @var MenuAccessService $menuAccessService */
            $menuAccessService = app(MenuAccessService::class);
            $menuVisibility = $menuAccessService->menuVisibilityForUser($user, $selectedCompanyModel);

            $canAccessMasters = $user->canAccessMasters();
            $canItemIssue = (bool) ($menuVisibility['modules.project-management.item-issue'] ?? false);
            $canPr = (bool) ($menuVisibility['modules.procurement.purch-req'] ?? false);
            $canGrn = (bool) ($menuVisibility['modules.procurement.grn'] ?? false);
            $canModulesGeneral = $canItemIssue || $canPr || $canGrn;

            $view->with('globalCompanyOptions', $companies);
            $view->with('globalSelectedCompany', $selectedCompany);
            $view->with('authIsSuperAdmin', $isSuperAdmin);
            $view->with('authCanAccessMasters', $canAccessMasters);
            $view->with('authShowMastersSettingsNav', $canAccessMasters);
            $view->with('canItemIssue', $canItemIssue);
            $view->with('canPr', $canPr);
            $view->with('canGrn', $canGrn);
            $view->with('canModulesGeneral', $canModulesGeneral);
        });
    }

    /**
     * Laravel only loads PHP files directly under database/migrations by default.
     * This project keeps migrations in subfolders (masters/, core/, modules/…); register each
     * directory that actually contains migration files so deploys can run `php artisan migrate`
     * without custom --path flags (fixes missing columns such as warehouses.company_id on Cloud).
     */
    private function registerNestedMigrationPaths(): void
    {
        $base = database_path('migrations');
        if (! is_dir($base)) {
            return;
        }

        $dirs = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.php')) {
                continue;
            }
            if (! preg_match('/^\d{4}_\d{2}_\d{2}_/', $file->getFilename())) {
                continue;
            }
            $dirs[$file->getPath()] = true;
        }

        foreach (array_keys($dirs) as $dir) {
            $this->loadMigrationsFrom($dir);
        }
    }
}
