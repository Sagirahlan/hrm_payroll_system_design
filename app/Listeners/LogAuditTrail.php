<?php
namespace App\Listeners;

use App\Events\AuditTrailLogged;
use App\Models\AuditTrail;
use Illuminate\Support\Facades\Auth;

class LogAuditTrail
{
    public function handle(AuditTrailLogged $event)
    {
        // Get the current user's role ID if available
        $roleId = null;
        if (Auth::check()) {
            $user = Auth::user();
            $roleId = $user->roles->first()?->id;
        }

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