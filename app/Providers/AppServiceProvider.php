<?php

namespace App\Providers;

use App\Console\Commands\ServeCommand;
use App\Models\Company;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
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

            $selectedCompany = strtoupper(trim((string) request()->query('company', '')));

            if ($selectedCompany === '' && $companies->isNotEmpty()) {
                $selectedCompany = strtoupper((string) ($companies->first()->d365_id ?? ''));
            }

            $view->with('globalCompanyOptions', $companies);
            $view->with('globalSelectedCompany', $selectedCompany);
        });
    }
}