<?php

namespace App\Listeners;

use App\Events\AuditTrailLogged;
use App\Models\AuditTrail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;

class AuditTrailListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\AuditTrailLogged  $event
     * @return void
     */
    public function handle(AuditTrailLogged $event)
    {
        // Get the current user's role ID if available
        $roleId = null;
        if (Auth::check()) {
            $user = Auth::user();
            $roleId = $user->roles->first()?->id;
        }

        // Create the audit trail record
        AuditTrail::create([
            'user_id' => $event->userId,
            'role_id' => $roleId,
            'action' => $event->action,
            'description' => $event->description,
            'action_timestamp' => now(),
            'log_data' => [
                'model_type' => $event->modelType,
                'model_id' => $event->modelId,
            ],
        ]);
    }
}