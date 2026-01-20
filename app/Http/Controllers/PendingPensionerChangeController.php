<?php

namespace App\Http\Controllers;

use App\Models\PendingPensionerChange;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PendingPensionerChangeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware(['permission:view_pensioner_changes'], ['only' => ['index', 'show']]);
        $this->middleware(['permission:approve_pensioner_changes'], ['only' => ['approve', 'reject']]);
    }

    public function index(Request $request)
    {
        $query = PendingPensionerChange::with(['pensioner', 'requestedBy']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by change type
        if ($request->filled('change_type')) {
            $query->where('change_type', $request->change_type);
        }

        // Search by pensioner name
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->whereHas('pensioner', function ($subQuery) use ($searchTerm) {
                $subQuery->where('full_name', 'like', "%{$searchTerm}%")
                         ->orWhere('surname', 'like', "%{$searchTerm}%")
                         ->orWhere('employee_id', 'like', "%{$searchTerm}%");
            });
        }

        $pendingChanges = $query->orderBy('created_at', 'desc')->paginate(10)->appends($request->except('page'));

        return view('pending-pensioner-changes.index', compact('pendingChanges'));
    }

    public function show(PendingPensionerChange $pendingChange)
    {
        $pendingChange->load(['pensioner', 'requestedBy', 'approvedBy']);

        $displayableNewData = $this->getDisplayableData($pendingChange->data ?? []);
        $displayableOldData = $this->getDisplayableData($pendingChange->previous_data ?? []);

        return view('pending-pensioner-changes.show', compact('pendingChange', 'displayableNewData', 'displayableOldData'));
    }

    private function getDisplayableData(array $data): array
    {
        $displayableData = [];
        foreach ($data as $key => $value) {
            $displayableData[$key] = $this->getDisplayValue($key, $value);
        }
        return $displayableData;
    }

    private function getDisplayValue($key, $value)
    {
        if (is_null($value)) {
            return 'N/A';
        }

        // Convert foreign key IDs to names if needed
        // For now we'll just return the value
        return $value;
    }

    public function approve(PendingPensionerChange $pendingChange, Request $request)
    {
        $request->validate([
            'approval_notes' => 'nullable|string|max:1000'
        ]);

        return DB::transaction(function () use ($pendingChange, $request) {
            // Update the pending change status
            $pendingChange->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'approval_notes' => $request->approval_notes
            ]);

            // Apply the changes to the pensioner
            $pensioner = $pendingChange->pensioner;
            $pensioner->update($pendingChange->data);

            // Add audit trail for approval
            AuditTrail::create([
                'user_id' => auth()->id(),
                'action' => 'approved_pensioner_change',
                'description' => "Approved pensioner change for: " . $pendingChange->pensioner_name .
                    ". Changes: " . $pendingChange->change_description .
                    ". Notes: " . ($request->approval_notes ?? 'None'),
                'action_timestamp' => now(),
                'entity_type' => 'PendingPensionerChange',
                'entity_id' => $pendingChange->id,
            ]);

            return redirect()->route('pending-pensioner-changes.index')
                ->with('success', 'Pensioner change approved successfully.');
        });
    }

    public function reject(PendingPensionerChange $pendingChange, Request $request)
    {
        $request->validate([
            'approval_notes' => 'required|string|max:1000'
        ]);

        $pendingChange->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'approval_notes' => $request->approval_notes
        ]);

        // Add audit trail for rejection
        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'rejected_pensioner_change',
            'description' => "Rejected pensioner change for: " . $pendingChange->pensioner_name .
                ". Changes: " . $pendingChange->change_description .
                ". Reason: " . $request->approval_notes,
            'action_timestamp' => now(),
            'entity_type' => 'PendingPensionerChange',
            'entity_id' => $pendingChange->id,
        ]);

        return redirect()->route('pending-pensioner-changes.index')
            ->with('success', 'Pensioner change rejected.');
    }
}
