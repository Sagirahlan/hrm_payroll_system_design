<?php

namespace App\Http\Controllers;

use App\Models\AuditTrail;
use Illuminate\Http\Request;
use App\Models\User;

class AuditTrailController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditTrail::with(['user.roles']) // Eager load user and roles
            ->orderByDesc('action_timestamp');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($subQuery) use ($search) {
                    $subQuery->where('username', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhere('action', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Date range filter
        if ($request->filled('start_date')) {
            $query->where('action_timestamp', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('action_timestamp', '<=', $request->end_date);
        }

        // User filter
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Action filter
        if ($request->filled('action_filter')) {
            $query->where('action', $request->action_filter);
        }

        $auditLogs = $query->paginate(20)->withQueryString();

        // For filter dropdowns
        $users = User::orderBy('username')->get();
        $actions = AuditTrail::distinct()->pluck('action');

        return view('audit_trails.index', compact('auditLogs', 'users', 'actions'));
    }
}
