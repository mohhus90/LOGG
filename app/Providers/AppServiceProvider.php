<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Models\Employee;
use App\Observers\EmployeeObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.default');
        Paginator::defaultSimpleView('vendor.pagination.default');

        Employee::observe(EmployeeObserver::class);
    }
}
