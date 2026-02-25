<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\PayrollRecord;
use App\Observers\PayrollRecordObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        \Illuminate\Pagination\Paginator::useBootstrapFive();
        
        PayrollRecord::observe(PayrollRecordObserver::class);
    }
}
