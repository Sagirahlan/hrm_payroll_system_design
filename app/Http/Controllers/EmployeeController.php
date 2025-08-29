<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\BiometricData;
use App\Models\Cadre;
use App\Models\GradeLevel;
use App\Models\UserRole;
use App\Models\AuditTrail;
use App\Models\NextOfKin;
use App\Models\Bank;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EmployeesExport;
use App\Exports\SingleEmployeeExport;
use Spatie\Permission\Models\Permission;
use App\Imports\EmployeesMultiSheetImport;
use App\Models\State;
use App\Models\Lga;
use App\Models\Ward;
use App\Models\AppointmentType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:manage_employees']);
    }

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
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Pagination with filters
        $perPage = $request->get('per_page', 10);
        $employees = $query->paginate($perPage)->withQueryString();

        // Get filter options
        $departments = Department::orderBy('department_name')->get();
        $cadres = Cadre::orderBy('cadre_name')->get();
        $gradeLevels = GradeLevel::orderBy('name')->get();
        $states = State::orderBy('name')->get();

        // Get unique values for filters
        $statuses = Employee::distinct()->pluck('status')->filter()->sort();
        $genders = Employee::distinct()->pluck('gender')->filter()->sort();
        $appointmentTypes = AppointmentType::orderBy('name')->get();

        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'viewed',
            'description' => 'Viewed employee list with filters: ' . json_encode($request->only(['search', 'department', 'cadre', 'status', 'gender', 'appointment_type_id'])),
            'action_timestamp' => now(),
            'entity_type' => 'Employee',
            'entity_id' => auth()->id(),
        ]);

        return view('employees.index', compact(
            'employees', 
            'departments', 
            'cadres', 
            'gradeLevels', 
            'states',
            'statuses',
            'genders',
            'appointmentTypes'
        ));
    }

    // Add a method to get filtered results via AJAX for better UX
    public function ajaxFilter(Request $request)
    {
        $query = Employee::with(['department', 'cadre', 'gradeLevel']);

        // Apply all the same filters as in index method
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('employee_id', 'like', "%{$search}%")
                  ->orWhere('reg_no', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile_no', 'like', "%{$search}%")
                  ->orWhere(DB::raw("CONCAT_WS(' ', first_name, middle_name, surname)"), 'like', "%{$search}%")
                  ->orWhere(DB::raw("CONCAT_WS(' ', first_name, surname)"), 'like', "%{$search}%");
            });
        }

        if ($request->filled('department')) {
            $query->where('department_id', $request->department);
        }

        if ($request->filled('cadre')) {
            $query->where('cadre_id', $request->cadre);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $employees = $query->paginate(10)->withQueryString();

        return response()->json([
            'html' => view('employees.partials.employee_table', compact('employees'))->render(),
            'pagination' => $employees->links('pagination::bootstrap-5')->render()
        ]);
    }

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

    // Generate filename with current timestamp
    $timestamp = now()->format('Y-m-d_H-i-s');
    
    if ($request->format === 'excel') {
        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'exported',
            'description' => 'Exported filtered employee list as Excel with ' . $employees->count() . ' records',
            'action_timestamp' => now(),
            'entity_type' => 'Employee',
            'entity_id' => auth()->id(),
        ]);

        return Excel::download(new EmployeesExport($employees), "filtered_employees_{$timestamp}.xlsx");
    } else {
        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'exported',
            'description' => 'Exported filtered employee list as PDF with ' . $employees->count() . ' records',
            'action_timestamp' => now(),
            'entity_type' => 'Employee',
            'entity_id' => auth()->id(),
        ]);

        $pdf = Pdf::loadView('employees.pdf', compact('employees'));
        return $pdf->download("filtered_employees_{$timestamp}.pdf");
    }
}
    // Keep all your existing methods (store, show, edit, update, destroy, etc.)
    public function create()
    {
        $departments = Department::all();
        $cadres = Cadre::all();
        $gradeLevels = GradeLevel::all();
        $states = State::all();
        $lgas = Lga::all();
        $wards = Ward::all();
        $appointmentTypes = AppointmentType::all();

        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'accessed',
            'description' => 'Accessed create employee form',
            'action_timestamp' => now(),
            'entity_type' => 'Employee',
            'entity_id' => auth()->id(),
        ]);

        return view('employees.create', compact('departments', 'cadres', 'gradeLevels', 'states', 'lgas', 'wards', 'appointmentTypes'));
    }

    
public function store(Request $request)
    {
        $validated = $request->validate([
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
            'reg_no' => 'required|string|max:50',
            'mobile_no' => 'required|string|max:15',
            'email' => 'nullable|email|max:100',
            'address' => 'required|string',
            'date_of_first_appointment' => 'required|date',
            'cadre_id' => 'required|exists:cadres,cadre_id',
            'grade_level_id' => 'required|exists:grade_levels,id', // from form input
            'department_id' => 'required|exists:departments,department_id',
            'expected_next_promotion' => 'nullable|date',
            'expected_retirement_date' => 'required|date',
            'status' => 'required|in:Active,Suspended,Retired,Deceased',
            'highest_certificate' => 'nullable|string|max:100',
            'grade_level_limit' => 'nullable|integer',
            'appointment_type_id' => 'required|exists:appointment_types,id',
            'photo' => 'nullable|image|max:2048',

            // NOK
            'kin_name' => 'required|string|max:100',
            'kin_relationship' => 'required|string|max:50',
            'kin_mobile_no' => 'required|string|max:20',
            'kin_address' => 'required|string',
            'kin_occupation'     => 'nullable|string|max:100',
            'kin_place_of_work'  => 'nullable|string|max:150',

            // Bank
            'bank_name' => 'required|string|max:100',
            'bank_code' => 'required|string|max:20',
            'account_name' => 'required|string|max:100',
            'account_no' => 'required|string|max:20',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $request->file('photo')->store('photos', 'public');
        } elseif ($request->filled('captured_image')) {
            // Handle captured image from camera
            $imageData = $request->input('captured_image');
            $imageData = str_replace('data:image/jpeg;base64,', '', $imageData);
            $imageData = str_replace(' ', '+', $imageData);
            $imageBinary = base64_decode($imageData);
            
            // Generate a unique filename
            $filename = 'captured_' . time() . '_' . uniqid() . '.jpg';
            $path = 'photos/' . $filename;
            
            // Store the image
            \Storage::disk('public')->put($path, $imageBinary);
            $validated['photo_path'] = $path;
        }
        
        // Instead of creating employee directly, save as pending
        $pendingChange = \App\Models\PendingEmployeeChange::create([
            'employee_id' => 0, // Placeholder for new employees
            'requested_by' => auth()->id(),
            'change_type' => 'create',
            'data' => $validated,
            'previous_data' => [], // No previous data for new employees
            'reason' => $request->input('change_reason', 'New employee creation')
        ]);

        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'requested_create',
            'description' => "Requested creation of new employee: {$validated['first_name']} {$validated['surname']}. Changes: " . $pendingChange->change_description,
            'action_timestamp' => now(),
            'entity_type' => 'Employee',
            'entity_id' => 0, // Placeholder
        ]);

        return redirect()->route('employees.index')->with('success', 'Employee creation requested. Changes are pending approval.');
    }

    public function show(Employee $employee)
    {
        $employee->load(['department', 'cadre', 'gradeLevel', 'nextOfKin', 'biometricData', 'bank', 'state', 'lga']);

        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'viewed',
            'description' => "Viewed employee: {$employee->first_name} {$employee->surname}",
            'action_timestamp' => now(),
            'entity_type' => 'Employee',
            'entity_id' => $employee->employee_id,
        ]);

        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $departments = Department::all();
        $cadres = Cadre::all();
        $gradeLevels = GradeLevel::all();
        $states = State::all();
        $lgas = Lga::where('state_id', $employee->state_id)->get();
        $appointmentTypes = AppointmentType::all();

        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'accessed',
            'description' => "Accessed edit form for employee: {$employee->first_name} {$employee->surname}",
            'action_timestamp' => now(),
            'entity_type' => 'Employee',
            'entity_id' => $employee->employee_id,
        ]);

        return view('employees.edit', compact('employee', 'departments', 'cadres', 'gradeLevels', 'states', 'lgas', 'appointmentTypes'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
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
            'address' => 'required|string',
            'date_of_first_appointment' => 'required|date',
            'cadre_id' => 'required|exists:cadres,cadre_id',
            'grade_level_id' => 'required|exists:grade_levels,id',
            'department_id' => 'required|exists:departments,department_id',
            'expected_next_promotion' => 'nullable|date',
            'expected_retirement_date' => 'required|date',
            'status' => 'required|in:Active,Suspended,Retired,Deceased',
            'highest_certificate' => 'nullable|string|max:100',
            'grade_level_limit' => 'nullable|integer',
            'appointment_type_id' => 'required|exists:appointment_types,id',
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
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $request->file('photo')->store('photos', 'public');
        } elseif ($request->filled('captured_image')) {
            // Handle captured image from camera
            $imageData = $request->input('captured_image');
            $imageData = str_replace('data:image/jpeg;base64,', '', $imageData);
            $imageData = str_replace(' ', '+', $imageData);
            $imageBinary = base64_decode($imageData);
            
            // Generate a unique filename
            $filename = 'captured_' . time() . '_' . uniqid() . '.jpg';
            $path = 'photos/' . $filename;
            
            // Store the image
            \Storage::disk('public')->put($path, $imageBinary);
            $validated['photo_path'] = $path;
        }

        // Get current employee data for comparison
        $currentData = $employee->toArray();
        
        // Get current related data
        $currentKin = $employee->nextOfKin ? $employee->nextOfKin->toArray() : [];
        $currentBank = $employee->bank ? $employee->bank->toArray() : [];
        
        // Merge all current data
        $previousData = array_merge($currentData, $currentKin, $currentBank);
        
        // Instead of applying changes directly, save them as pending
        $pendingChange = \App\Models\PendingEmployeeChange::create([
            'employee_id' => $employee->employee_id,
            'requested_by' => auth()->id(),
            'change_type' => 'update',
            'data' => $validated,
            'previous_data' => $previousData,
            'reason' => $request->input('change_reason', 'Employee update')
        ]);

        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'requested_update',
            'description' => "Requested update for employee: {$employee->first_name} {$employee->surname}. Changes: " . $pendingChange->change_description,
            'action_timestamp' => now(),
            'entity_type' => 'Employee',
            'entity_id' => $employee->employee_id,
        ]);

        return redirect()->route('employees.index')->with('success', 'Employee update requested. Changes are pending approval.');
    }

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

        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'requested_delete',
            'description' => "Requested deletion of employee: {$employee->first_name} {$employee->surname}. Changes: " . $pendingChange->change_description,
            'action_timestamp' => now(),
            'entity_type' => 'Employee',
            'entity_id' => $employee->employee_id,
        ]);

        return redirect()->route('employees.index')->with('success', 'Employee deletion requested. Changes are pending approval.');
    }

    public function exportPdf()
    {
        $employees = Employee::with(['department', 'cadre', 'gradeLevel'])->get();
        $pdf = Pdf::loadView('employees.pdf', compact('employees'));

        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'exported',
            'description' => 'Exported employee list as PDF',
            'action_timestamp' => now(),
            'entity_type' => 'Employee',
            'entity_id' => auth()->id(),
        ]);

        return $pdf->download('employees_report.pdf');
    }

    public function exportExcel()
    {
        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'exported',
            'description' => 'Exported employee list as Excel',
            'action_timestamp' => now(),
            'entity_type' => 'Employee',
            'entity_id' => auth()->id(),
        ]);

        return Excel::download(new EmployeesExport, 'employees_report.xlsx');
    }

    public function exportSingle($employeeId)
    {
        return Excel::download(new SingleEmployeeExport($employeeId), 'employee_' . $employeeId . '_details.xlsx');
    }

    public function importEmployees(Request $request)
    {
        $file = $request->file('import_file');
        Excel::import(new EmployeesMultiSheetImport, $file);

        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'imported',
            'description' => 'Imported employee data from Excel',
            'action_timestamp' => now(),
            'entity_type' => 'Employee',
            'entity_id' => auth()->id(),
        ]);

        return redirect()->route('employees.index')->with('success', 'Employees imported successfully.');
    }

    public function getLgasByState(Request $request)
    {
        $stateId = $request->input('state_id');
        $lgas = Lga::where('state_id', $stateId)
                   ->select('id', 'name')  // Only select the fields we need
                   ->get();
        return response()->json($lgas);
    }

    public function getWardsByLga(Request $request)
    {
        $lgaId = $request->input('lga_id');
        $wards = Ward::where('lga_id', $lgaId)
                     ->select('ward_id', 'ward_name')  // Only select the fields we need
                     ->get();
        return response()->json($wards);
    }
}