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
use App\Models\Rank;
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
use Illuminate\Validation\ValidationException;

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
        $cadres = Cadre::orderBy('name')->get();
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
        $salaryScales = \App\Models\SalaryScale::all(); // Get all salary scales
        $states = State::all();
        $lgas = Lga::all();
        $wards = Ward::all();
        $appointmentTypes = AppointmentType::all();
        $ranks = Rank::all();

        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'accessed',
            'description' => 'Accessed create employee form',
            'action_timestamp' => now(),
            'entity_type' => 'Employee',
            'entity_id' => auth()->id(),
        ]);

        return view('employees.create', compact('departments', 'cadres', 'gradeLevels', 'salaryScales', 'states', 'lgas', 'wards', 'appointmentTypes', 'ranks'));
    }

    
    public function store(Request $request)
    {
        // Log the values
        \Illuminate\Support\Facades\Log::info('Submitted grade_level_id: ' . $request->input('grade_level_id'));
        \Illuminate\Support\Facades\Log::info('Submitted step_id: ' . $request->input('step_id'));
        \Illuminate\Support\Facades\Log::info('Submitted step_level: ' . $request->input('step_level'));
        \Illuminate\Support\Facades\Log::info('All request data: ' . json_encode($request->all()));

        try {
            $validated = $request->validate([
                'first_name' => 'required|string|max:50',
                'surname' => 'required|string|max:50',
                'middle_name' => 'nullable|string|max:50',
                'gender' => 'required|string|max:50',
                'date_of_birth' => 'required|date|before_or_equal:' . now()->subYears(18)->format('Y-m-d'),
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
                'salary_scale_id' => 'required|exists:salary_scales,id',
                'grade_level_id' => 'required|exists:grade_levels,id',
                'step_id' => 'required|exists:steps,id',
                'step_level' => 'required|string|max:50',
                'department_id' => 'required|exists:departments,department_id',
                'expected_next_promotion' => 'nullable|date',
                'expected_retirement_date' => 'required|date',
                'status' => 'required|in:Active,Suspended,Retired,Deceased',
                'highest_certificate' => 'nullable|string|max:100',
                'appointment_type_id' => 'required|exists:appointment_types,id',
                'rank_id' => 'required|exists:ranks,id',
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

            // Additional validation to ensure grade_level belongs to the selected salary_scale
            $gradeLevel = GradeLevel::find($validated['grade_level_id']);
            if (!$gradeLevel) {
                return redirect()->back()->withErrors(['grade_level_id' => 'Please select a valid grade level.'])->withInput();
            }
            
            if ($gradeLevel->salary_scale_id != $validated['salary_scale_id']) {
                return redirect()->back()->withErrors(['grade_level_id' => 'The selected grade level is not valid for the chosen salary scale.'])->withInput();
            }
            
            // Additional validation to ensure step belongs to the selected grade_level
            $step = \App\Models\Step::find($validated['step_id']);
            if (!$step) {
                return redirect()->back()->withErrors(['step_level' => 'Please select a valid step level.'])->withInput();
            }
            
            if ($step->grade_level_id != $validated['grade_level_id']) {
                return redirect()->back()->withErrors(['step_level' => 'The selected step level is not valid for the chosen grade level.'])->withInput();
            }

            if ($request->hasFile('photo')) {
                $validated['photo_path'] = $request->file('photo')->store('photos', 'public');
            } elseif ($request->filled('captured_image')) {
                $imageData = $request->input('captured_image');
                $imageData = str_replace('data:image/jpeg;base64,', '', $imageData);
                $imageData = str_replace(' ', '+', $imageData);
                $imageBinary = base64_decode($imageData);
                $filename = 'captured_' . time() . '_' . uniqid() . '.jpg';
                $path = 'photos/' . $filename;
                \Storage::disk('public')->put($path, $imageBinary);
                $validated['photo_path'] = $path;
            }

            $employee = Employee::create($validated);

            if (isset($validated['kin_name'])) {
                $employee->nextOfKin()->create([
                    'name' => $validated['kin_name'],
                    'relationship' => $validated['kin_relationship'],
                    'mobile_no' => $validated['kin_mobile_no'],
                    'address' => $validated['kin_address'],
                    'occupation' => $validated['kin_occupation'] ?? null,
                    'place_of_work' => $validated['kin_place_of_work'] ?? null,
                ]);
            }

            if (isset($validated['bank_name'])) {
                $employee->bank()->create([
                    'bank_name' => $validated['bank_name'],
                    'bank_code' => $validated['bank_code'],
                    'account_name' => $validated['account_name'],
                    'account_no' => $validated['account_no'],
                ]);
            }

            AuditTrail::create([
                'user_id' => auth()->id(),
                'action' => 'created',
                'description' => "Created new employee: {$employee->first_name} {$employee->surname}",
                'action_timestamp' => now(),
                'entity_type' => 'Employee',
                'entity_id' => $employee->employee_id,
            ]);

            return redirect()->route('employees.index')->with('success', 'Employee created successfully.');
        } catch (ValidationException $e) {
            $errors = $e->validator->errors()->keys();
            \Illuminate\Support\Facades\Log::error('Validation errors: ' . json_encode($errors));
            $step = 1;

            $step1_fields = ['first_name', 'surname', 'gender', 'date_of_birth', 'state_id', 'lga_id', 'nationality', 'reg_no', 'mobile_no'];
            $step2_fields = ['address'];
            $step3_fields = ['date_of_first_appointment', 'cadre_id', 'salary_scale_id', 'grade_level_id', 'step_level', 'department_id', 'rank_id', 'expected_retirement_date'];
            $step4_fields = ['status', 'appointment_type_id'];
            $step5_fields = ['kin_name', 'kin_relationship', 'kin_mobile_no', 'kin_address'];
            $step6_fields = ['bank_name', 'bank_code', 'account_name', 'account_no'];

            foreach ($errors as $error) {
                if (in_array($error, $step1_fields)) {
                    $step = 1;
                    break;
                } elseif (in_array($error, $step2_fields)) {
                    $step = 2;
                    break;
                } elseif (in_array($error, $step3_fields)) {
                    $step = 3;
                    break;
                } elseif (in_array($error, $step4_fields)) {
                    $step = 4;
                    break;
                } elseif (in_array($error, $step5_fields)) {
                    $step = 5;
                    break;
                } elseif (in_array($error, $step6_fields)) {
                    $step = 6;
                    break;
                }
            }

            return redirect()->route('employees.create')->withErrors($e->validator)->withInput()->with('step', $step);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Employee creation error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while creating the employee. Please try again.')->withInput();
        }
    }

    public function show(Employee $employee)
    {
        $employee->load(['department', 'cadre', 'gradeLevel', 'nextOfKin', 'biometricData', 'bank', 'state', 'lga', 'ward', 'rank', 'additions', 'deductions']);

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
        // Load relationships for the employee
        $employee->load(['state', 'lga', 'ward']);
        
        $departments = Department::all();
        $cadres = Cadre::all();
        $gradeLevels = GradeLevel::all();
        $salaryScales = \App\Models\SalaryScale::all();
        $states = State::all();
        $lgas = Lga::all();
        $wards = Ward::all();
        $appointmentTypes = AppointmentType::all();
        $ranks = Rank::all();

        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'accessed',
            'description' => "Accessed edit form for employee: {$employee->first_name} {$employee->surname}",
            'action_timestamp' => now(),
            'entity_type' => 'Employee',
            'entity_id' => $employee->employee_id,
        ]);

        return view('employees.edit', compact('employee', 'departments', 'cadres', 'gradeLevels', 'salaryScales', 'states', 'lgas', 'wards', 'appointmentTypes', 'ranks'));
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
            'salary_scale_id' => 'required|exists:salary_scales,id', // New validation for salary scale
            'grade_level_id' => 'required|exists:grade_levels,id', // Will be validated further below
            'step_id' => 'required|exists:steps,id',
            'department_id' => 'required|exists:departments,department_id',
            'expected_next_promotion' => 'nullable|date',
            'expected_retirement_date' => 'required|date',
            'status' => 'required|in:Active,Suspended,Retired,Deceased',
            'highest_certificate' => 'nullable|string|max:100',
            'grade_level_limit' => 'nullable|integer',
            'appointment_type_id' => 'required|exists:appointment_types,id',
            'rank_id' => 'required|exists:ranks,id',
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
        
        // Additional validation to ensure grade_level belongs to the selected salary_scale
        $gradeLevel = GradeLevel::find($validated['grade_level_id']);
        if (!$gradeLevel || $gradeLevel->salary_scale_id != $validated['salary_scale_id']) {
            return redirect()->back()->withErrors(['grade_level_id' => 'The selected grade level is not valid for the chosen salary scale.']);
        }

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

    public function getRanksByGradeLevel(Request $request)
    {
        $gradeLevelId = $request->input('grade_level_id');
        $gradeLevel = GradeLevel::find($gradeLevelId);
        $ranks = Rank::where('name', $gradeLevel->name)->get();
        return response()->json($ranks);
    }
}