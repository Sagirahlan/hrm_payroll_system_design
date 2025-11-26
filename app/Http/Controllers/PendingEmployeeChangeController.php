<?php

namespace App\Http\Controllers;

use App\Models\PendingEmployeeChange;
use App\Models\PromotionHistory;
use App\Models\Employee;
use App\Models\NextOfKin;
use App\Models\Bank;
use App\Models\AuditTrail;
use App\Models\State;
use App\Models\Lga;
use App\Models\Ward;
use App\Models\Cadre;
use App\Models\Department;
use App\Models\GradeLevel;
use App\Models\Rank;
use App\Models\Step;
use App\Models\AppointmentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PendingEmployeeChangeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware(['permission:view_pending_employee_changes'], ['only' => ['index', 'show']]);
        $this->middleware(['permission:approve_pending_employee_changes'], ['only' => ['approve']]);
        $this->middleware(['permission:reject_pending_employee_changes'], ['only' => ['reject']]);
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

        // Search by employee name
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                // Split search term into words
                $words = explode(' ', trim($searchTerm));

                // Search in existing employee records
                $q->whereHas('employee', function ($subQuery) use ($searchTerm, $words) {
                    $subQuery->where('first_name', 'like', "%{$searchTerm}%")
                             ->orWhere('surname', 'like', "%{$searchTerm}%")
                             ->orWhere('employee_id', 'like', "%{$searchTerm}%")
                             ->orWhereRaw("CONCAT(first_name, ' ', surname) LIKE ?", ["%{$searchTerm}%"]);

                    // If we have multiple words, search each word separately
                    if (count($words) > 1) {
                        foreach ($words as $word) {
                            if (!empty($word)) {
                                $subQuery->orWhere('first_name', 'like', "%{$word}%")
                                         ->orWhere('surname', 'like', "%{$word}%");
                            }
                        }
                    }
                })
                // Also search in pending change data for create requests
                ->orWhere(function ($subQuery) use ($searchTerm, $words) {
                    $subQuery->where('change_type', 'create')
                             ->whereRaw("JSON_EXTRACT(data, '$.first_name') LIKE ?", ["%{$searchTerm}%"])
                             ->orWhereRaw("JSON_EXTRACT(data, '$.surname') LIKE ?", ["%{$searchTerm}%"])
                             ->orWhereRaw("JSON_EXTRACT(data, '$.employee_id') LIKE ?", ["%{$searchTerm}%"])
                             ->orWhereRaw("CONCAT(JSON_EXTRACT(data, '$.first_name'), ' ', JSON_EXTRACT(data, '$.surname')) LIKE ?", ["%{$searchTerm}%"]);

                    // If we have multiple words, search each word separately
                    if (count($words) > 1) {
                        foreach ($words as $word) {
                            if (!empty($word)) {
                                $subQuery->orWhereRaw("JSON_EXTRACT(data, '$.first_name') LIKE ?", ["%{$word}%"])
                                         ->orWhereRaw("JSON_EXTRACT(data, '$.surname') LIKE ?", ["%{$word}%"]);
                            }
                        }
                    }
                });
            });
        }

        $pendingChanges = $query->orderBy('created_at', 'desc')->paginate(10)->appends($request->except('page'));

        // Also get pending promotions/demotions
        $pendingPromotions = PromotionHistory::with(['employee', 'creator'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pending-changes.index', compact('pendingChanges', 'pendingPromotions'));
    }

    public function show(PendingEmployeeChange $pendingChange)
    {
        $pendingChange->load(['employee', 'requestedBy', 'approvedBy']);

        $displayableNewData = $this->getDisplayableData($pendingChange->data ?? []);
        $displayableOldData = $this->getDisplayableData($pendingChange->previous_data ?? []);

        return view('pending-changes.show', compact('pendingChange', 'displayableNewData', 'displayableOldData'));
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

        switch ($key) {
            case 'state_id':
                return State::find($value)->name ?? $value;
            case 'lga_id':
                return Lga::find($value)->name ?? $value;
            case 'ward_id':
                return Ward::find($value)->ward_name ?? $value;
            case 'cadre_id':
                return Cadre::find($value)->name ?? $value;
            case 'department_id':
                return Department::find($value)->department_name ?? $value;
            case 'grade_level_id':
                return GradeLevel::find($value)->name ?? $value;
            case 'step_id':
                return Step::find($value)->name ?? $value;
            case 'rank_id':
                return Rank::find($value)->title ?? $value;
            case 'appointment_type_id':
                return AppointmentType::find($value)->name ?? $value;
            default:
                return $value;
        }
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
        \Illuminate\Support\Facades\Log::info('Data in applyCreate:', $data);

        // Sanitize years_of_service
        if (isset($data['years_of_service'])) {
            $data['years_of_service'] = intval($data['years_of_service']);
        }

        // Create employee
        $employeeData = collect($data)->except([
            'kin_name', 'kin_relationship', 'kin_mobile_no', 'kin_address', 'kin_occupation', 'kin_place_of_work',
            'bank_name', 'bank_code', 'account_name', 'account_no'
        ])->toArray();

        $employee = Employee::create($employeeData);

        $pendingChange->update(['employee_id' => $employee->employee_id]);

        // Check if this is a permanent employee (not contract) and put on probation
        $appointmentType = null;
        if (isset($data['appointment_type_id'])) {
            $appointmentType = AppointmentType::find($data['appointment_type_id']);
        }

        // If it's a permanent employee, place them on probation for 3 months starting from their date of first appointment
        if ($appointmentType && $appointmentType->name !== 'Contract') {
            $dateOfFirstAppointment = $data['date_of_first_appointment'] ?? now();
            $probationStartDate = \Carbon\Carbon::parse($dateOfFirstAppointment);
            $probationEndDate = $probationStartDate->copy()->addMonths(3);

            $employee->update([
                'on_probation' => true,
                'probation_start_date' => $probationStartDate,
                'probation_end_date' => $probationEndDate,
                'probation_status' => 'pending',
                'status' => 'On Probation', // Change status to indicate probation
                'probation_notes' => 'Automatically placed on 3-month probation starting from date of first appointment (' . $probationStartDate->format('Y-m-d') . ')'
            ]);
        }

        // Create next of kin if kin data exists
        if (isset($data['kin_name'])) {
            NextOfKin::create([
                'employee_id' => $employee->employee_id,
                'name' => $data['kin_name'],
                'relationship' => $data['kin_relationship'] ?? null,
                'mobile_no' => $data['kin_mobile_no'] ?? null,
                'address' => $data['kin_address'] ?? null,
                'occupation' => $data['kin_occupation'] ?? null,
                'place_of_work' => $data['kin_place_of_work'] ?? null,
            ]);
        }

        // Create bank details if bank data exists
        if (isset($data['bank_name'])) {
            Bank::create([
                'employee_id' => $employee->employee_id,
                'bank_name' => $data['bank_name'],
                'bank_code' => $data['bank_code'],
                'account_name' => $data['account_name'],
                'account_no' => $data['account_no'],
            ]);
        }
    }

    private function applyUpdate(PendingEmployeeChange $pendingChange)
    {
        $employee = $pendingChange->employee;
        $data = $pendingChange->data;

        // Sanitize years_of_service
        if (isset($data['years_of_service'])) {
            $data['years_of_service'] = intval($data['years_of_service']);
        }

        // Update employee
        $employeeData = collect($data)->except([
            'kin_name', 'kin_relationship', 'kin_mobile_no', 'kin_address', 'kin_occupation', 'kin_place_of_work',
            'bank_name', 'bank_code', 'account_name', 'account_no'
        ])->toArray();

        $employee->update($employeeData);

        // Update or create next of kin if any kin-related data is provided
        $hasKinData = isset($data['kin_name']) || isset($data['kin_relationship']) ||
                     isset($data['kin_mobile_no']) || isset($data['kin_address']) ||
                     isset($data['kin_occupation']) || isset($data['kin_place_of_work']);

        if ($hasKinData) {
            // Get existing next of kin to preserve required fields that are not being updated
            $existingKin = NextOfKin::where('employee_id', $employee->employee_id)->first();

            $kinData = $existingKin ? $existingKin->toArray() : [];

            if (isset($data['kin_name'])) {
                $kinData['name'] = $data['kin_name'];
            }
            if (isset($data['kin_relationship'])) {
                $kinData['relationship'] = $data['kin_relationship'];
            }
            if (isset($data['kin_mobile_no'])) {
                $kinData['mobile_no'] = $data['kin_mobile_no'];
            }
            if (isset($data['kin_address'])) {
                $kinData['address'] = $data['kin_address'];
            }
            if (isset($data['kin_occupation'])) {
                $kinData['occupation'] = $data['kin_occupation'];
            }
            if (isset($data['kin_place_of_work'])) {
                $kinData['place_of_work'] = $data['kin_place_of_work'];
            }

            // Make sure required fields are not null
            if (isset($kinData['name']) && isset($kinData['mobile_no'])) {
                NextOfKin::updateOrCreate(
                    ['employee_id' => $employee->employee_id],
                    $kinData
                );
            }
        }

        // Update or create bank details if any bank-related data is provided
        $hasBankData = isset($data['bank_name']) || isset($data['bank_code']) ||
                      isset($data['account_name']) || isset($data['account_no']);

        if ($hasBankData) {
            // Get existing bank details to preserve required fields that are not being updated
            $existingBank = Bank::where('employee_id', $employee->employee_id)->first();

            $bankData = $existingBank ? $existingBank->toArray() : [];

            if (isset($data['bank_name'])) {
                $bankData['bank_name'] = $data['bank_name'];
            }
            if (isset($data['bank_code'])) {
                $bankData['bank_code'] = $data['bank_code'];
            }
            if (isset($data['account_name'])) {
                $bankData['account_name'] = $data['account_name'];
            }
            if (isset($data['account_no'])) {
                $bankData['account_no'] = $data['account_no'];
            }

            // Make sure required fields are not null
            if (isset($bankData['account_name']) && isset($bankData['account_no'])) {
                Bank::updateOrCreate(
                    ['employee_id' => $employee->employee_id],
                    $bankData
                );
            }
        }
    }

    private function applyDelete(PendingEmployeeChange $pendingChange)
    {
        $employee = $pendingChange->employee;
        $employee->delete();
    }
}