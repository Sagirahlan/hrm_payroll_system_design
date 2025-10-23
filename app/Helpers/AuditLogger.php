<?php


namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use App\Models\AuditTrail;
use App\Models\User;
use App\Models\Role;
use Carbon\Carbon;

class AuditLogger
{
    public static function log($action, $description = null, $entityType = null, $entityId = null)
    {
        $user = Auth::user();

        AuditTrail::create([
            'user_id' => $user->id,
            'role_id' => $user->roles->first()?->id ?? null,
            'action' => $action,
            'description' => $description,
            'action_timestamp' => now('Africa/Lagos'),
            'log_data' => [
                'ip' => Request::ip(),
                'agent' => Request::header('User-Agent'),
                'url' => Request::fullUrl(),
                'method' => Request::method(),
            ],
        ]);
    }
}
