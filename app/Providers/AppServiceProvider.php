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
        // Explicit report-only predicates: operational queries retain their scopes.
        foreach (['whereReportBranch' => 'and', 'orWhereReportBranch' => 'or'] as $method => $boolean) {
            \Illuminate\Database\Query\Builder::macro($method, function (string $column, ...$scope) use ($boolean) {
                $branch = count($scope) ? $scope[0] : \App\Support\ReportBranch::id();
                return $branch === null ? $this->whereRaw('1 = 1', [], $boolean) : $this->where($column, '=', $branch, $boolean);
            });
        }
        Gate::policy(Supplier::class, SrPolicy::class);
        // Transaction Observer logic removed as part of the new global transaction-based architecture
    }
}
