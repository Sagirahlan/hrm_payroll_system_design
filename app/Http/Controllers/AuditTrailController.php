<?php

namespace App\Http\Controllers;

use App\Models\AuditTrail;
use Illuminate\Http\Request;

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

        $auditLogs = $query->paginate(20)->withQueryString();

        return view('audit_trails.index', compact('auditLogs'));
    }
}
