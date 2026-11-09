<?php

namespace App\Providers;

use App\Models\Supplier;
use App\Policies\SrPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

use App\Repositories\ShomriddhiAdvance\Contracts\ShomriddhiAdvanceRepositoryInterface;
use App\Repositories\ShomriddhiAdvance\EloquentShomriddhiAdvanceRepository;
use App\Repositories\Payroll\Contracts\EmployeeAdvanceRepositoryInterface;
use App\Repositories\Payroll\EloquentEmployeeAdvanceRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Repository Bindings — Interface → Eloquent Implementation
        $this->app->bind(
            ShomriddhiAdvanceRepositoryInterface::class,
            EloquentShomriddhiAdvanceRepository::class
        );
        $this->app->bind(
            EmployeeAdvanceRepositoryInterface::class,
            EloquentEmployeeAdvanceRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Supplier::class, SrPolicy::class);
        // Transaction Observer logic removed as part of the new global transaction-based architecture
    }
}
