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

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
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
            
            if ($pendingChange->change_type === 'delete') {
                $pensioner->delete();
            } else {
                $pensioner->update($pendingChange->data);
            }

            // Update linked Employee staff_no if updated in pensioner
            if (isset($pendingChange->data['staff_no'])) {
                $unlink = isset($pendingChange->data['unlink_employee']) && $pendingChange->data['unlink_employee'];
                
                if ($unlink) {
                    // Create NEW employee record as they are different people
                    $oldEmployee = \App\Models\Employee::where('employee_id', $pensioner->employee_id)->first();
                    $employeeData = $oldEmployee ? $oldEmployee->toArray() : [];
                    unset($employeeData['employee_id'], $employeeData['created_at'], $employeeData['updated_at']);
                    
                    // Fetch "Pensioners" appointment type ID
                    $pensionerTypeId = \Illuminate\Support\Facades\DB::table('appointment_types')
                        ->where('name', 'Pensioners')
                        ->value('id') ?? 4; // Fallback to 4 if not found

                    // Populate from pensioner and pending change
                    $employeeData['first_name'] = $pensioner->first_name;
                    $employeeData['surname'] = $pensioner->surname;
                    $employeeData['middle_name'] = $pensioner->middle_name;
                    $safeEmail = str_replace('@', '.', ($pensioner->email ?? 'pensioner.' . $pensioner->id));
                    $employeeData['email'] = $safeEmail . '.unlinked.' . time() . '@example.com';
                    $employeeData['mobile_no'] = $pensioner->phone_number;
                    $employeeData['date_of_birth'] = $pensioner->date_of_birth;
                    $employeeData['status'] = 'Retired';
                    $employeeData['staff_no'] = $pendingChange->data['staff_no'];
                    $employeeData['appointment_type_id'] = $pensionerTypeId;
                    $employeeData['address'] = $pensioner->address ?? ($oldEmployee->address ?? 'Unknown');

                    $newEmployee = \App\Models\Employee::create($employeeData);
                    $pensioner->update(['employee_id' => $newEmployee->employee_id]);
                    
                    // Use pensioner's own bank details if available
                    if ($pensioner->account_number) {
                        $bankInfo = \Illuminate\Support\Facades\DB::table('bank_list')->find($pensioner->bank_id);
                        \App\Models\Bank::create([
                            'employee_id' => $newEmployee->employee_id,
                            'bank_name' => $bankInfo->bank_name ?? 'Unknown Bank',
                            'bank_code' => $bankInfo->bank_code ?? '000',
                            'account_name' => $pensioner->account_name,
                            'account_no' => $pensioner->account_number,
                        ]);
                    } elseif ($oldEmployee) {
                        // Fallback to old employee's bank record if pensioner has none (unlikely but safe)
                        $oldBank = \App\Models\Bank::where('employee_id', $oldEmployee->employee_id)->first();
                        if ($oldBank) {
                            \App\Models\Bank::create([
                                'employee_id' => $newEmployee->employee_id,
                                'bank_name' => $oldBank->bank_name,
                                'bank_code' => $oldBank->bank_code,
                                'account_name' => $oldBank->account_name,
                                'account_no' => $oldBank->account_no,
                            ]);
                        }
                    }
                } else {
                    // Just update existing employee's staff_no
                    $employee = \App\Models\Employee::where('employee_id', $pensioner->employee_id)->first();
                    if ($employee) {
                        $employee->update(['staff_no' => $pendingChange->data['staff_no']]);
                    } else {
                        // Create employee if missing
                        \App\Models\Employee::create([
                            'first_name' => $pensioner->first_name,
                            'surname' => $pensioner->surname,
                            'middle_name' => $pensioner->middle_name,
                            'email' => $pensioner->email,
                            'mobile_no' => $pensioner->phone_number,
                            'date_of_birth' => $pensioner->date_of_birth,
                            'date_of_first_appointment' => $pensioner->date_of_first_appointment,
                            'status' => 'Retired',
                            'staff_no' => $pendingChange->data['staff_no'],
                        ]);
                    }
                }
            }

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
