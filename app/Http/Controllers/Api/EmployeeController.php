<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Cadre;
use App\Models\GradeLevel;
use App\Models\State;
use App\Models\Lga;
use App\Models\Ward;
use App\Models\AppointmentType;
use App\Models\Rank;
use App\Models\AuditTrail;
use App\Models\SalaryScale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class EmployeeController extends Controller
{
    public function __construct()
    {
        // Permissions are handled at the route level
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Employee::with(['department', 'cadre', 'gradeLevel']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('employee_id', 'like', "%{$search}%")
                  ->orWhere('reg_no', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile_no', 'like', "%{$search}%")
                  ->orWhere('nin', 'like', "%{$search}%")
                  ->orWhere(DB::raw("CONCAT_WS(' ', first_name, middle_name, surname)"), 'like', "%{$search}%")
                  ->orWhere(DB::raw("CONCAT_WS(' ', first_name, surname)"), 'like', "%{$search}%");
            });
        }

        // Department filter
        if ($request->filled('department')) {
            $query->where('department_id', $request->department);
        }

        // Cadre filter
        if ($request->filled('cadre')) {
            $query->where('cadre_id', $request->cadre);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Gender filter
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        // Appointment type filter
        if ($request->filled('appointment_type_id')) {
            $query->where('appointment_type_id', $request->appointment_type_id);
        }

        // State of origin filter
        if ($request->filled('state_of_origin')) {
            $state = State::where('name', $request->state_of_origin)->first();
            if ($state) {
                $query->where('state_id', $state->state_id);
            }
        }

        // Age range filter
        if ($request->filled('age_from') || $request->filled('age_to')) {
            $today = Carbon::now();
            
            if ($request->filled('age_from')) {
                $dateFrom = $today->copy()->subYears($request->age_from)->endOfYear();
                $query->where('date_of_birth', '<=', $dateFrom);
            }
            
            if ($request->filled('age_to')) {
                $dateTo = $today->copy()->subYears($request->age_to)->startOfYear();
                $query->where('date_of_birth', '>=', $dateTo);
            }
        }

        // Date of appointment range filter
        if ($request->filled('appointment_from')) {
            $query->where('date_of_first_appointment', '>=', $request->appointment_from);
        }

        if ($request->filled('appointment_to')) {
            $query->where('date_of_first_appointment', '<=', $request->appointment_to);
        }

        // Retirement date range filter
        if ($request->filled('retirement_from')) {
            $query->where('expected_retirement_date', '>=', $request->retirement_from);
        }

        if ($request->filled('retirement_to')) {
            $query->where('expected_retirement_date', '<=', $request->retirement_to);
        }

        // Salary scale filter
        if ($request->filled('grade_level_id')) {
            $query->where('grade_level_id', $request->grade_level_id);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSorts = ['first_name', 'surname', 'employee_id', 'date_of_first_appointment', 'expected_retirement_date', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination with filters
        $perPage = $request->get('per_page', 10);
        $employees = $query->paginate($perPage);

        // Get filter options
        $departments = Department::orderBy('department_name')->get();
        $cadres = Cadre::orderBy('name')->get();
        $gradeLevels = GradeLevel::orderBy('name')->get();
        $states = State::orderBy('name')->get();

        // Get unique values for filters
        $statuses = Employee::distinct()->pluck('status')->filter()->sort();
        $genders = Employee::distinct()->pluck('gender')->filter()->sort();
        $appointmentTypes = AppointmentType::orderBy('name')->get();

        // Log audit trail
        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'viewed',
            'description' => 'Viewed employee list with filters: ' . json_encode($request->only(['search', 'department', 'cadre', 'status', 'gender', 'appointment_type_id'])),
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Employee', 'entity_id' => null, 'filters' => $request->only(['search', 'department', 'cadre', 'status', 'gender', 'appointment_type_id'])]),
        ]);

        return response()->json([
            'data' => $employees->items(),
            'pagination' => [
                'current_page' => $employees->currentPage(),
                'last_page' => $employees->lastPage(),
                'per_page' => $employees->perPage(),
                'total' => $employees->total(),
                'from' => $employees->firstItem(),
                'to' => $employees->lastItem(),
            ],
            'filters' => [
                'departments' => $departments,
                'cadres' => $cadres,
                'gradeLevels' => $gradeLevels,
                'states' => $states,
                'statuses' => $statuses,
                'genders' => $genders,
                'appointmentTypes' => $appointmentTypes,
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create()
    {
        $departments = Department::all();
        $cadres = Cadre::all();
        $gradeLevels = GradeLevel::all();
        $salaryScales = SalaryScale::all();
        $states = State::all();
        $lgas = Lga::all();
        $wards = Ward::all();
        $appointmentTypes = AppointmentType::all();
        $ranks = Rank::all();
        $banks = \App\Models\BankList::where('is_active', true)->orderBy('bank_name')->get();

        // Log audit trail
        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'accessed',
            'description' => 'Accessed create employee form',
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Employee', 'entity_id' => null]),
        ]);

        return response()->json([
            'departments' => $departments,
            'cadres' => $cadres,
            'gradeLevels' => $gradeLevels,
            'salaryScales' => $salaryScales,
            'states' => $states,
            'lgas' => $lgas,
            'wards' => $wards,
            'appointmentTypes' => $appointmentTypes,
            'ranks' => $ranks,
            'banks' => $banks,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $appointmentType = AppointmentType::find($request->input('appointment_type_id'));
            $employee = new Employee(); // Create a temporary employee instance to check appointment type
            $employee->appointment_type_id = $request->input('appointment_type_id');
            $employee->load('appointmentType');

            $validationRules = [
                'first_name' => 'required|string|max:50',
                'surname' => 'required|string|max:50',
                'middle_name' => 'nullable|string|max:50',
                'gender' => 'required|string|max:50',
                'date_of_birth' => 'required|date',
                'state_id' => 'required|exists:states,state_id',
                'lga_id' => 'required|exists:lgas,id',
                'ward_id' => 'nullable|exists:wards,ward_id',
                'nationality' => 'required|string|max:50',
                'nin' => 'required|string|max:50',
                'reg_no' => 'required|string|max:50',
                'mobile_no' => 'required|string|max:15',
                'email' => 'nullable|email|max:100',
                'pay_point' => 'required|string|max:100',
                'address' => 'required|string',
                'date_of_first_appointment' => 'required|date',
                'appointment_type_id' => 'required|exists:appointment_types,id',
                'status' => 'required|in:Active,Suspended,Retired,Deceased',
                'highest_certificate' => 'nullable|string|max:100',
                'photo' => 'nullable|image|max:2048',
                'kin_name' => 'required|string|max:100',
                'kin_relationship' => 'required|string|max:50',
                'kin_mobile_no' => 'required|string|max:20',
                'kin_address' => 'required|string',
                'kin_occupation'     => 'nullable|string|max:100',
                'kin_place_of_work'  => 'nullable|string|max:150',
                'bank_name' => 'required|string|max:100',
                'bank_code' => 'required|string|max:20',
                'account_name' => 'required|string|max:100',
                'account_no' => 'required|string|max:20',
                'years_of_service' => 'nullable|string',
            ];

            $validationRules['department_id'] = 'required|exists:departments,department_id';

            if ($employee->isContractEmployee()) {
                $validationRules['contract_start_date'] = 'required|date';
                $validationRules['contract_end_date'] = 'required|date|after:contract_start_date';
                $validationRules['amount'] = 'required|numeric';
                
            } else {
                $validationRules['cadre_id'] = 'required|exists:cadres,cadre_id';
                $validationRules['salary_scale_id'] = 'required|exists:salary_scales,id';
                $validationRules['grade_level_id'] = 'required|exists:grade_levels,id';
                $validationRules['step_id'] = 'required|exists:steps,id';
                $validationRules['step_level'] = 'required|string|max:50';
                $validationRules['expected_next_promotion'] = 'nullable|date';
                $validationRules['expected_retirement_date'] = 'required|date';
                $validationRules['rank_id'] = 'required|exists:ranks,id';
            }

            $validated = $request->validate($validationRules);

            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('photos', 'public');
                $validated['photo_path'] = $path;
            }

            $pendingChange = \App\Models\PendingEmployeeChange::create([
                'employee_id' => null,
                'requested_by' => auth()->id(),
                'change_type' => 'create',
                'data' => $validated,
                'reason' => 'New employee creation'
            ]);

            // Log audit trail
            AuditTrail::create([
                'user_id' => auth()->id(),
                'action' => 'requested_creation',
                'description' => "Requested creation of new employee: {$validated['first_name']} {$validated['surname']}",
                'action_timestamp' => now(),
                'log_data' => json_encode(['entity_type' => 'Employee', 'entity_id' => null, 'requested_data' => $validated]),
            ]);

            return response()->json([
                'message' => 'Employee creation request submitted for approval.',
                'pending_change' => $pendingChange
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation errors occurred.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the employee.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Employee $employee)
    {
        $employee->load(['department', 'cadre', 'gradeLevel.salaryScale', 'step', 'nextOfKin', 'biometricData', 'bank', 'state', 'lga', 'ward', 'rank', 'additions', 'deductions']);

        // Log audit trail
        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'viewed',
            'description' => "Viewed employee: {$employee->first_name} {$employee->surname}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Employee', 'entity_id' => $employee->employee_id]),
        ]);

        return response()->json([
            'data' => $employee
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Employee $employee)
    {
        // Load relationships for the employee
        $employee->load(['state', 'lga', 'ward', 'bank']);
        
        $departments = Department::all();
        $cadres = Cadre::all();
        $gradeLevels = GradeLevel::all();
        $salaryScales = SalaryScale::all();
        $states = State::all();
        $lgas = Lga::all();
        $wards = Ward::all();
        $appointmentTypes = AppointmentType::all();
        $ranks = Rank::all();
        $banks = \App\Models\BankList::where('is_active', true)->orderBy('bank_name')->get();

        // Log audit trail
        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'accessed',
            'description' => "Accessed edit form for employee: {$employee->first_name} {$employee->surname}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Employee', 'entity_id' => $employee->employee_id]),
        ]);

        return response()->json([
            'employee' => $employee,
            'departments' => $departments,
            'cadres' => $cadres,
            'gradeLevels' => $gradeLevels,
            'salaryScales' => $salaryScales,
            'states' => $states,
            'lgas' => $lgas,
            'wards' => $wards,
            'appointmentTypes' => $appointmentTypes,
            'ranks' => $ranks,
            'banks' => $banks,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Employee $employee)
    {
        try {
            $employee->load(['nextOfKin', 'bank']);
            $appointmentType = AppointmentType::find($request->input('appointment_type_id'));

            $validationRules = [
                'first_name' => 'required|string|max:50',
                'surname' => 'required|string|max:50',
                'middle_name' => 'nullable|string|max:50',
                'gender' => 'required|string|max:50',
                'date_of_birth' => 'required|date',
                'state_id' => 'required|exists:states,state_id',
                'lga_id' => 'required|exists:lgas,id',
                'ward_id' => 'nullable|exists:wards,ward_id',
                'nationality' => 'required|string|max:50',
                'nin' => 'nullable|string|max:50',
                'mobile_no' => 'required|string|max:15',
                'email' => 'nullable|email|max:100',
                'pay_point' => 'nullable|string|max:100',
                'address' => 'required|string',
                'date_of_first_appointment' => 'required|date',
                'appointment_type_id' => 'required|exists:appointment_types,id',
                'status' => 'required|in:Active,Suspended,Retired,Deceased',
                'highest_certificate' => 'nullable|string|max:100',
                'photo' => 'nullable|image|max:2048',
                'kin_name' => 'required|string|max:100',
                'kin_relationship' => 'required|string|max:50',
                'kin_mobile_no' => 'required|string|max:20',
                'kin_address' => 'required|string',
                'kin_occupation'     => 'nullable|string|max:100',
                'kin_place_of_work'  => 'nullable|string|max:150',
                'bank_name' => 'required|string|max:100',
                'bank_code' => 'required|string|max:20',
                'account_name' => 'required|string|max:100',
                'account_no' => 'required|string|max:20',
            ];

            $validationRules['department_id'] = 'required|exists:departments,department_id';

            if ($employee->isContractEmployee()) {
                $validationRules['contract_start_date'] = 'required|date';
                $validationRules['contract_end_date'] = 'required|date|after:contract_start_date';
                $validationRules['amount'] = 'required|numeric';
            } else {
                $validationRules['cadre_id'] = 'required|exists:cadres,cadre_id';
                $validationRules['salary_scale_id'] = 'required|exists:salary_scales,id';
                $validationRules['grade_level_id'] = 'required|exists:grade_levels,id';
                $validationRules['step_id'] = 'required|exists:steps,id';
                $validationRules['step_level'] = 'required|string|max:50';
                $validationRules['expected_next_promotion'] = 'nullable|date';
                $validationRules['expected_retirement_date'] = 'required|date';
                $validationRules['rank_id'] = 'required|exists:ranks,id';
            }

            $validated = $request->validate($validationRules);

            $currentData = $employee->toArray();
            $currentData['kin_name'] = $employee->nextOfKin->name ?? null;
            $currentData['kin_relationship'] = $employee->nextOfKin->relationship ?? null;
            $currentData['kin_mobile_no'] = $employee->nextOfKin->mobile_no ?? null;
            $currentData['kin_address'] = $employee->nextOfKin->address ?? null;
            $currentData['kin_occupation'] = $employee->nextOfKin->occupation ?? null;
            $currentData['kin_place_of_work'] = $employee->nextOfKin->place_of_work ?? null;
            $currentData['bank_name'] = $employee->bank->bank_name ?? null;
            $currentData['bank_code'] = $employee->bank->bank_code ?? null;
            $currentData['account_name'] = $employee->bank->account_name ?? null;
            $currentData['account_no'] = $employee->bank->account_no ?? null;

            $changedData = [];
            $previousData = [];

            foreach ($validated as $key => $value) {
                if (array_key_exists($key, $currentData) && $currentData[$key] != $value) {
                    $changedData[$key] = $value;
                    $previousData[$key] = $currentData[$key];
                } elseif (!array_key_exists($key, $currentData) && !is_null($value)) {
                    // New field added or field that was null is now set
                    $changedData[$key] = $value;
                    $previousData[$key] = null;
                }
            }

            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('photos', 'public');
                $changedData['photo_path'] = $path;
                $previousData['photo_path'] = $employee->photo_path;
            }

            if (empty($changedData)) {
                return response()->json([
                    'message' => 'No changes were made to the employee.'
                ]);
            }

            $pendingChange = \App\Models\PendingEmployeeChange::create([
                'employee_id' => $employee->employee_id,
                'requested_by' => auth()->id(),
                'change_type' => 'update',
                'data' => $changedData,
                'previous_data' => $previousData,
                'reason' => $request->input('change_reason', 'Employee update')
            ]);

            // Log audit trail
            AuditTrail::create([
                'user_id' => auth()->id(),
                'action' => 'requested_update',
                'description' => "Requested update for employee: {$employee->first_name} {$employee->surname}",
                'action_timestamp' => now(),
                'log_data' => json_encode(['entity_type' => 'Employee', 'entity_id' => $employee->employee_id, 'requested_data' => $changedData, 'previous_data' => $previousData]),
            ]);

            return response()->json([
                'message' => 'Employee update request submitted for approval.',
                'pending_change' => $pendingChange
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation errors occurred.',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Employee $employee)
    {
        // Instead of deleting directly, save as pending
        $pendingChange = \App\Models\PendingEmployeeChange::create([
            'employee_id' => $employee->employee_id,
            'requested_by' => auth()->id(),
            'change_type' => 'delete',
            'data' => [], // No data needed for deletion
            'reason' => request()->input('delete_reason', 'Employee deletion')
        ]);

        // Log audit trail
        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'requested_delete',
            'description' => "Requested deletion of employee: {$employee->first_name} {$employee->surname}. Changes: " . $pendingChange->change_description,
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Employee', 'entity_id' => $employee->employee_id, 'reason' => request()->input('delete_reason')]),
        ]);

        return response()->json([
            'message' => 'Employee deletion requested. Changes are pending approval.',
            'pending_change' => $pendingChange
        ]);
    }

    /**
     * Export employees as PDF
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function exportPdf()
    {
        $employees = Employee::with(['department', 'cadre', 'gradeLevel'])->get();

        // Log audit trail
        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'exported',
            'description' => 'Exported employee list as PDF',
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Employee', 'entity_id' => null, 'format' => 'PDF']),
        ]);

        // For API, we'll return a response indicating the export, not the PDF file itself
        return response()->json([
            'message' => 'PDF export would be generated here',
            'employee_count' => $employees->count()
        ]);
    }

    /**
     * Export employees as Excel
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function exportExcel()
    {
        // Log audit trail
        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'exported',
            'description' => 'Exported employee list as Excel',
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Employee', 'entity_id' => null, 'format' => 'Excel']),
        ]);

        // For API, we'll return a response indicating the export, not the Excel file itself
        return response()->json([
            'message' => 'Excel export would be generated here'
        ]);
    }

    /**
     * Export single employee
     *
     * @param int $employeeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function exportSingle($employeeId)
    {
        $employee = Employee::findOrFail($employeeId);

        // Log audit trail
        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'exported',
            'description' => "Exported single employee details for: {$employee->first_name} {$employee->surname}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Employee', 'entity_id' => $employee->employee_id, 'format' => 'Excel']),
        ]);

        // For API, we'll return a response indicating the export, not the Excel file itself
        return response()->json([
            'message' => 'Single employee Excel export would be generated here',
            'employee' => $employee
        ]);
    }

    /**
     * Import employees from file
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function importEmployees(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        try {
            $file = $request->file('import_file');
            
            // Import the file
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\EmployeesMultiSheetImport, $file);

            // Log audit trail
            AuditTrail::create([
                'user_id' => auth()->id(),
                'action' => 'imported',
                'description' => 'Imported employee data from Excel',
                'action_timestamp' => now(),
                'log_data' => json_encode(['entity_type' => 'Employee', 'entity_id' => null, 'file_name' => $file->getClientOriginalName()]),
            ]);

            return response()->json([
                'message' => 'Employees imported successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error importing employees.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export filtered employees
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function exportFiltered(Request $request)
    {
        // Apply same filters as index method for export    
        $query = Employee::with(['department', 'cadre', 'gradeLevel']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('employee_id', 'like', "%{$search}%")
                  ->orWhere('reg_no', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile_no', 'like', "%{$search}%")
                  ->orWhere('nin', 'like', "%{$search}%")
                  ->orWhere(DB::raw("CONCAT_WS(' ', first_name, middle_name, surname)"), 'like', "%{$search}%")
                  ->orWhere(DB::raw("CONCAT_WS(' ', first_name, surname)"), 'like', "%{$search}%");
            });
        }

        // Department filter
        if ($request->filled('department')) {
            $query->where('department_id', $request->department);
        }

        // Cadre filter
        if ($request->filled('cadre')) {
            $query->where('cadre_id', $request->cadre);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Gender filter
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        // Appointment type filter
        if ($request->filled('appointment_type_id')) {
            $query->where('appointment_type_id', $request->appointment_type_id);
        }

        // State of origin filter
        if ($request->filled('state_of_origin')) {
            $state = State::where('name', $request->state_of_origin)->first();
            if ($state) {
                $query->where('state_id', $state->state_id);
            }
        }

        // Age range filter
        if ($request->filled('age_from') || $request->filled('age_to')) {
            $today = Carbon::now();
            
            if ($request->filled('age_from')) {
                $dateFrom = $today->copy()->subYears($request->age_from)->endOfYear();
                $query->where('date_of_birth', '<=', $dateFrom);
            }
            
            if ($request->filled('age_to')) {
                $dateTo = $today->copy()->subYears($request->age_to)->startOfYear();
                $query->where('date_of_birth', '>=', $dateTo);
            }
        }

        // Date of appointment range filter
        if ($request->filled('appointment_from')) {
            $query->where('date_of_first_appointment', '>=', $request->appointment_from);
        }

        if ($request->filled('appointment_to')) {
            $query->where('date_of_first_appointment', '<=', $request->appointment_to);
        }

        // Retirement date range filter
        if ($request->filled('retirement_from')) {
            $query->where('expected_retirement_date', '>=', $request->retirement_from);
        }

        if ($request->filled('retirement_to')) {
            $query->where('expected_retirement_date', '<=', $request->retirement_to);
        }

        // Salary scale filter
        if ($request->filled('grade_level_id')) {
            $query->where('grade_level_id', $request->grade_level_id);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSorts = ['first_name', 'surname', 'employee_id', 'date_of_first_appointment', 'expected_retirement_date', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $employees = $query->get();

        // Log audit trail
        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'exported',
            'description' => 'Exported filtered employee list with ' . $employees->count() . ' records',
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Employee', 'entity_id' => null, 'format' => 'Filtered Export', 'count' => $employees->count(), 'filters' => $request->only(['search', 'department', 'cadre', 'status', 'gender', 'appointment_type_id'])]),
        ]);

        // For API, we'll return a response indicating the export
        return response()->json([
            'message' => 'Filtered employee export would be generated here',
            'employee_count' => $employees->count(),
            'filters_applied' => $request->all()
        ]);
    }

    /**
     * Get LGAs by state
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLgasByState(Request $request)
    {
        $stateId = $request->input('state_id');
        $lgas = Lga::where('state_id', $stateId)
                   ->select('id', 'name')
                   ->get();
        return response()->json($lgas);
    }

    /**
     * Get wards by LGA
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWardsByLga(Request $request)
    {
        $lgaId = $request->input('lga_id');
        $wards = Ward::where('lga_id', $lgaId)
                     ->select('ward_id', 'ward_name')
                     ->get();
        return response()->json($wards);
    }

    /**
     * Get ranks by grade level
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRanksByGradeLevel(Request $request)
    {
        $gradeLevelId = $request->input('grade_level_id');
        $gradeLevel = GradeLevel::find($gradeLevelId);
        $ranks = Rank::where('name', $gradeLevel->name)->get();
        return response()->json($ranks);
    }

    /**
     * Get all salary scales
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllSalaryScales()
    {
        $salaryScales = \App\Models\SalaryScale::select('id', 'acronym', 'full_name')->get();
        return response()->json($salaryScales);
    }

    /**
     * Get single salary scale
     *
     * @param int $salaryScaleId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSingleSalaryScale($salaryScaleId)
    {
        $salaryScale = \App\Models\SalaryScale::select('id', 'acronym', 'full_name')->find($salaryScaleId);
        return response()->json($salaryScale);
    }

    /**
     * Get grade levels by salary scale
     *
     * @param int $salaryScaleId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGradeLevelsBySalaryScale($salaryScaleId)
    {
        $gradeLevels = \App\Models\GradeLevel::where('salary_scale_id', $salaryScaleId)->get();
        return response()->json($gradeLevels);
    }

    /**
     * Get retirement info by salary scale
     *
     * @param int $salaryScaleId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRetirementInfo($salaryScaleId)
    {
        $salaryScale = \App\Models\SalaryScale::find($salaryScaleId);
        if ($salaryScale) {
            return response()->json([
                'max_retirement_age' => (int)$salaryScale->max_retirement_age,
                'max_years_of_service' => (int)$salaryScale->max_years_of_service,
            ]);
        }
        return response()->json(null, 404);
    }

    /**
     * Get grade levels with steps
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGradeLevelsWithSteps()
    {
        $gradeLevels = \App\Models\GradeLevel::with('steps')->get();
        return response()->json($gradeLevels);
    }
}