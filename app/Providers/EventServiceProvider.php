<?php
namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\AuditTrailLogged;
use App\Listeners\LogAuditTrail;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        AuditTrailLogged::class => [
            LogAuditTrail::class,
        ],
    ];

    public function boot()
    {
        //
    }
}