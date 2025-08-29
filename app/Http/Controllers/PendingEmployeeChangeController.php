<?php

namespace App\Http\Controllers;

use App\Models\PendingEmployeeChange;
use App\Models\Employee;
use App\Models\NextOfKin;
use App\Models\Bank;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PendingEmployeeChangeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:approve_employee_changes']);
    }

    public function index(Request $request)
    {
        $query = PendingEmployeeChange::with(['employee', 'requestedBy']);
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by change type
        if ($request->filled('change_type')) {
            $query->where('change_type', $request->change_type);
        }
        
        $pendingChanges = $query->orderBy('created_at', 'desc')->paginate(10);
        
        return view('pending-changes.index', compact('pendingChanges'));
    }
    
    public function show(PendingEmployeeChange $pendingChange)
    {
        $pendingChange->load(['employee', 'requestedBy', 'approvedBy']);
        return view('pending-changes.show', compact('pendingChange'));
    }
    
    public function approve(PendingEmployeeChange $pendingChange, Request $request)
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
            
            // Apply the actual changes based on change type
            switch ($pendingChange->change_type) {
                case 'create':
                    $this->applyCreate($pendingChange);
                    break;
                    
                case 'update':
                    $this->applyUpdate($pendingChange);
                    break;
                    
                case 'delete':
                    $this->applyDelete($pendingChange);
                    break;
            }
            
            // Add audit trail for approval
            AuditTrail::create([
                'user_id' => auth()->id(),
                'action' => 'approved_pending_change',
                'description' => "Approved pending {$pendingChange->change_type} for employee: " . $pendingChange->employee_name .
                    ". Changes: " . $pendingChange->change_description .
                    ". Notes: " . ($request->approval_notes ?? 'None'),
                'action_timestamp' => now(),
                'entity_type' => 'PendingEmployeeChange',
                'entity_id' => $pendingChange->id,
            ]);
            
            return redirect()->route('pending-changes.index')
                ->with('success', 'Employee change approved successfully.');
        });
    }
    
    public function reject(PendingEmployeeChange $pendingChange, Request $request)
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
            'action' => 'rejected_pending_change',
            'description' => "Rejected pending {$pendingChange->change_type} for employee: " . $pendingChange->employee_name .
                ". Changes: " . $pendingChange->change_description .
                ". Reason: " . $request->approval_notes,
            'action_timestamp' => now(),
            'entity_type' => 'PendingEmployeeChange',
            'entity_id' => $pendingChange->id,
        ]);
        
        return redirect()->route('pending-changes.index')
            ->with('success', 'Employee change rejected.');
    }
    
    private function applyCreate(PendingEmployeeChange $pendingChange)
    {
        $data = $pendingChange->data;
        
        // Create employee
        $employeeData = collect($data)->except([
            'kin_name', 'kin_relationship', 'kin_mobile_no', 'kin_address', 'kin_occupation', 'kin_place_of_work',
            'bank_name', 'bank_code', 'account_name', 'account_no'
        ])->toArray();
        
        $employee = Employee::create($employeeData);
        
        // Create next of kin
        NextOfKin::create([
            'employee_id' => $employee->employee_id,
            'name' => $data['kin_name'],
            'relationship' => $data['kin_relationship'],
            'mobile_no' => $data['kin_mobile_no'],
            'address' => $data['kin_address'],
            'occupation' => $data['kin_occupation'] ?? null,
            'place_of_work' => $data['kin_place_of_work'] ?? null,
        ]);
        
        // Create bank details
        Bank::create([
            'employee_id' => $employee->employee_id,
            'bank_name' => $data['bank_name'],
            'bank_code' => $data['bank_code'],
            'account_name' => $data['account_name'],
            'account_no' => $data['account_no'],
        ]);
    }
    
    private function applyUpdate(PendingEmployeeChange $pendingChange)
    {
        $employee = $pendingChange->employee;
        $data = $pendingChange->data;
        
        // Update employee
        $employeeData = collect($data)->except([
            'kin_name', 'kin_relationship', 'kin_mobile_no', 'kin_address', 'kin_occupation', 'kin_place_of_work',
            'bank_name', 'bank_code', 'account_name', 'account_no'
        ])->toArray();
        
        $employee->update($employeeData);
        
        // Update or create next of kin
        NextOfKin::updateOrCreate(
            ['employee_id' => $employee->employee_id],
            [
                'name' => $data['kin_name'],
                'relationship' => $data['kin_relationship'],
                'mobile_no' => $data['kin_mobile_no'],
                'address' => $data['kin_address'],
                'occupation' => $data['kin_occupation'] ?? null,
                'place_of_work' => $data['kin_place_of_work'] ?? null,
            ]
        );
        
        // Update or create bank details
        Bank::updateOrCreate(
            ['employee_id' => $employee->employee_id],
            [
                'bank_name' => $data['bank_name'],
                'bank_code' => $data['bank_code'],
                'account_name' => $data['account_name'],
                'account_no' => $data['account_no'],
            ]
        );
    }
    
    private function applyDelete(PendingEmployeeChange $pendingChange)
    {
        $employee = $pendingChange->employee;
        $employee->delete();
    }
}