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
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class EmployeeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware(['permission:view_employees'], ['only' => ['index', 'ajaxFilter', 'exportFiltered', 'show', 'exportPdf', 'exportExcel', 'exportSingle', 'getLgasByState', 'getWardsByLga', 'getRanksByGradeLevel']]);
        $this->middleware(['permission:create_employees'], ['only' => ['create', 'store', 'importEmployees']]);
        $this->middleware(['permission:edit_employees'], ['only' => ['edit', 'update']]);
        $this->middleware(['permission:delete_employees'], ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $query = Employee::with(['department', 'cadre', 'gradeLevel']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('employee_id', 'like', "%{$search}%")
                  ->orWhere('staff_no', 'like', "%{$search}%")
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

        // Probation status filter
        if ($request->filled('probation_status')) {
            $probationStatus = $request->probation_status;
            if ($probationStatus === 'pending') {
                $query->where('on_probation', true)->where('probation_status', 'pending');
            } elseif ($probationStatus === 'approved') {
                $query->where('probation_status', 'approved');
            } elseif ($probationStatus === 'rejected') {
                $query->where('probation_status', 'rejected');
            }
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
            'log_data' => json_encode(['entity_type' => 'Employee', 'entity_id' => null, 'filters' => $request->only(['search', 'department', 'cadre', 'status', 'gender', 'appointment_type_id'])]),
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
                  ->orWhere('staff_no', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile_no', 'like', "%{$search}%")
                  ->orWhere('nin', 'like', "%{$search}%")
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
              ->orWhere('staff_no', 'like', "%{$search}%")
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
            'log_data' => json_encode(['entity_type' => 'Employee', 'entity_id' => null, 'format' => 'Excel', 'count' => $employees->count(), 'filters' => $request->only(['search', 'department', 'cadre', 'status', 'gender', 'appointment_type_id'])]),
        ]);

        return Excel::download(new EmployeesExport($employees), "filtered_employees_{$timestamp}.xlsx");
    } else {
        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'exported',
            'description' => 'Exported filtered employee list as PDF with ' . $employees->count() . ' records',
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Employee', 'entity_id' => null, 'format' => 'PDF', 'count' => $employees->count(), 'filters' => $request->only(['search', 'department', 'cadre', 'status', 'gender', 'appointment_type_id'])]),
        ]);

        $pdf = Pdf::loadView('employees.pdf', compact('employees'));
        return $pdf->download("filtered_employees_{$timestamp}.pdf");
    }
}

    /**
     * Export employees to CSV format
     */
    public function exportCsv(Request $request)
    {
        // Apply same filters as exportFiltered method
        $query = Employee::with([
            'department',
            'gradeLevel',
            'step',
            'appointmentType',
            'state',
            'lga',
            'bank'
        ]);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('employee_id', 'like', "%{$search}%")
                  ->orWhere('staff_no', 'like', "%{$search}%")
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

        // Probation status filter
        if ($request->filled('probation_status')) {
            $probationStatus = $request->probation_status;
            if ($probationStatus === 'pending') {
                $query->where('on_probation', true)->where('probation_status', 'pending');
            } elseif ($probationStatus === 'approved') {
                $query->where('probation_status', 'approved');
            } elseif ($probationStatus === 'rejected') {
                $query->where('probation_status', 'rejected');
            }
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

        // Create CSV content
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="employees_' . now()->format('Y_m_d_H_i_s') . '.csv"',
        ];

        $callback = function() use ($employees) {
            $file = fopen('php://output', 'w');

            // Add BOM for proper UTF-8 encoding in Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Add headers
            fputcsv($file, [
                'Employee ID',
                'Registration Number',
                'First Name',
                'Middle Name',
                'Surname',
                'Gender',
                'Date of Birth',
                'Email',
                'Mobile Number',
                'Department',
                'Grade Level',
                'Step',
                'Appointment Type',
                'Date of First Appointment',
                'Years of Service',
                'Status',
                'State',
                'LGA',
                'Bank Name',
                'Account Number',
                'Account Name'
            ]);

            // Add data rows
            foreach ($employees as $employee) {
                fputcsv($file, [
                    $employee->employee_id,
                    $employee->staff_no,
                    $employee->first_name,
                    $employee->middle_name,
                    $employee->surname,
                    $employee->gender,
                    $employee->date_of_birth,
                    $employee->email,
                    $employee->mobile_no,
                    $employee->department->department_name ?? '',
                    $employee->gradeLevel->name ?? '',
                    $employee->step->name ?? '',
                    $employee->appointmentType->name ?? '',
                    $employee->date_of_first_appointment,
                    $employee->years_of_service,
                    $employee->status,
                    $employee->state->state_name ?? '',
                    $employee->lga->lga_name ?? '',
                    $employee->bank->bank_name ?? '',
                    $employee->bank->account_no ?? '',
                    $employee->bank->account_name ?? ''
                ]);
            }

            fclose($file);
        };

        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'exported',
            'description' => 'Exported employee list as CSV with ' . $employees->count() . ' records',
            'action_timestamp' => now(),
            'log_data' => json_encode([
                'entity_type' => 'Employee',
                'entity_id' => null,
                'format' => 'CSV',
                'count' => $employees->count(),
                'filters' => $request->only([
                    'search',
                    'department',
                    'cadre',
                    'status',
                    'gender',
                    'appointment_type_id'
                ])
            ]),
        ]);

        return response()->stream($callback, 200, $headers);
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
        $banks = \App\Models\BankList::where('is_active', true)->orderBy('bank_name')->get(); // Get all active banks

        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'accessed',
            'description' => 'Accessed create employee form',
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Employee', 'entity_id' => null]),
        ]);

        return view('employees.create', compact('departments', 'cadres', 'gradeLevels', 'salaryScales', 'states', 'lgas', 'wards', 'appointmentTypes', 'ranks', 'banks'));
    }


    public function store(Request $request)
    {
        try {
            $appointmentType = AppointmentType::find($request->input('appointment_type_id'));

            $validationRules = [
                'first_name' => 'required|string|max:50',
                'surname' => 'required|string|max:50',
                'middle_name' => 'nullable|string|max:50',
                'gender' => 'required|string|max:50',
                'date_of_birth' => 'required|date|before:' . Carbon::now()->subYears(18)->format('Y-m-d'),
                'state_id' => 'required|exists:states,state_id',
                'lga_id' => 'required|exists:lgas,id',
                'ward_id' => 'nullable|exists:wards,ward_id',
                'nationality' => 'required|string|max:50',
                'nin' => 'required|string|max:50',
                'staff_no' => 'required|string|max:50',
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

            // Check if appointment type is contract using the new method
            $tempEmployee = new Employee();
            $tempEmployee->appointment_type_id = $request->input('appointment_type_id');
            $tempEmployee->load('appointmentType');

            if ($tempEmployee->isContractEmployee()) {
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

            AuditTrail::create([
                'user_id' => auth()->id(),
                'action' => 'requested_creation',
                'description' => "Requested creation of new employee: {$validated['first_name']} {$validated['surname']}",
                'action_timestamp' => now(),
                'log_data' => json_encode(['entity_type' => 'Employee', 'entity_id' => null, 'requested_data' => $validated]),
            ]);

            return redirect()->route('employees.index')->with('success', 'Employee creation request submitted for approval.');
        } catch (ValidationException $e) {
            $errors = $e->validator->errors()->keys();
            \Illuminate\Support\Facades\Log::error('Validation errors: ' . json_encode($errors));
            $step = 1;

            $step1_fields = ['first_name', 'surname', 'gender', 'date_of_birth', 'state_id', 'lga_id', 'nationality', 'staff_no', 'mobile_no'];
            $step2_fields = ['address'];
            $step3_fields = ['date_of_first_appointment', 'cadre_id', 'salary_scale_id', 'grade_level_id', 'step_id', 'step_level', 'department_id', 'rank_id', 'expected_retirement_date', 'contract_start_date', 'contract_end_date', 'amount', 'appointment_type_id'];
            $step4_fields = ['status'];
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
            'log_data' => json_encode(['entity_type' => 'Employee', 'entity_id' => $employee->employee_id]),
        ]);

        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        // Check if the employee is retired and prevent editing
        if ($employee->status === 'Retired' || ($employee->retirement && $employee->retirement->status === 'confirmed')) {
            return redirect()->route('employees.index')
                ->with('error', 'Editing is not allowed for retired employees.');
        }

        // Load relationships for the employee
        $employee->load(['state', 'lga', 'ward', 'bank']);

        $departments = Department::all();
        $cadres = Cadre::all();
        $gradeLevels = GradeLevel::all();
        $salaryScales = \App\Models\SalaryScale::all(); // Get all salary scales
        $states = State::all();
        $lgas = Lga::all();
        $wards = Ward::all();
        $appointmentTypes = AppointmentType::all();
        $ranks = Rank::all();
        $banks = \App\Models\BankList::where('is_active', true)->orderBy('bank_name')->get(); // Get all active banks

        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'accessed',
            'description' => "Accessed edit form for employee: {$employee->first_name} {$employee->surname}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Employee', 'entity_id' => $employee->employee_id]),
        ]);

        return view('employees.edit', compact('employee', 'departments', 'cadres', 'gradeLevels', 'salaryScales', 'states', 'lgas', 'wards', 'appointmentTypes', 'ranks', 'banks'));
    }

    public function update(Request $request, Employee $employee)
    {
        // Check if the employee is retired and prevent editing
        if ($employee->status === 'Retired' || ($employee->retirement && $employee->retirement->status === 'confirmed')) {
            return redirect()->route('employees.index')
                ->with('error', 'Editing is not allowed for retired employees.');
        }

        try {
            $employee->load(['nextOfKin', 'bank']);
            $appointmentType = AppointmentType::find($request->input('appointment_type_id'));

            $validationRules = [
                'first_name' => 'required|string|max:50',
                'surname' => 'required|string|max:50',
                'middle_name' => 'nullable|string|max:50',
                'gender' => 'required|string|max:50',
                'date_of_birth' => 'required|date|before:' . Carbon::now()->subYears(18)->format('Y-m-d'),
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

            $tempEmployee = new Employee();
            $tempEmployee->appointment_type_id = $request->input('appointment_type_id');
            $tempEmployee->load('appointmentType');

            if ($tempEmployee->isContractEmployee()) {
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
                return redirect()->route('employees.index')->with('info', 'No changes were made to the employee.');
            }

            $pendingChange = \App\Models\PendingEmployeeChange::create([
                'employee_id' => $employee->employee_id,
                'requested_by' => auth()->id(),
                'change_type' => 'update',
                'data' => $changedData,
                'previous_data' => $previousData,
                'reason' => $request->input('change_reason', 'Employee update')
            ]);

            AuditTrail::create([
                'user_id' => auth()->id(),
                'action' => 'requested_update',
                'description' => "Requested update for employee: {$employee->first_name} {$employee->surname}",
                'action_timestamp' => now(),
                'log_data' => json_encode(['entity_type' => 'Employee', 'entity_id' => $employee->employee_id, 'requested_data' => $changedData, 'previous_data' => $previousData]),
            ]);

            return redirect()->route('employees.index')->with('success', 'Employee update request submitted for approval.');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        }
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
            'log_data' => json_encode(['entity_type' => 'Employee', 'entity_id' => $employee->employee_id, 'reason' => request()->input('delete_reason')]),
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
            'log_data' => json_encode(['entity_type' => 'Employee', 'entity_id' => null, 'format' => 'PDF']),
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
            'log_data' => json_encode(['entity_type' => 'Employee', 'entity_id' => null, 'format' => 'Excel']),
        ]);

        return Excel::download(new EmployeesExport, 'employees_report.xlsx');
    }

    public function exportSingle($employeeId)
    {
        $employee = Employee::findOrFail($employeeId);
        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'exported',
            'description' => "Exported single employee details for: {$employee->first_name} {$employee->surname}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Employee', 'entity_id' => $employee->employee_id, 'format' => 'Excel']),
        ]);
        return Excel::download(new SingleEmployeeExport($employeeId), 'employee_' . $employeeId . '_details.xlsx');
    }

    public function importEmployees(Request $request)
    {
        try {
            $request->validate([
                'import_file' => 'required|file|mimes:xlsx,xls,csv',
            ]);

            $file = $request->file('import_file');

            // Determine the number of sheets in the Excel file
            $fileExtension = $file->getClientOriginalExtension();

            if (in_array($fileExtension, ['xlsx', 'xls'])) {
                // Use PhpSpreadsheet to read the file and get the number of sheets
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                $reader->setReadDataOnly(true); // Read only cell values, not formulas
                $spreadsheet = $reader->load($file->getPathname());
                $sheetCount = $spreadsheet->getSheetCount();

                // Create a dynamic import based on the number of sheets
                $import = new class($sheetCount) implements \Maatwebsite\Excel\Concerns\WithMultipleSheets {
                    private $sheetCount;

                    public function __construct($sheetCount) {
                        $this->sheetCount = $sheetCount;
                    }

                    public function sheets(): array
                    {
                        $sheets = [
                            0 => new \App\Imports\EmployeeImport(),      // Always import first sheet (Employees)
                        ];

                        // Import Next of Kin sheet if it exists
                        if ($this->sheetCount >= 2) {
                            $sheets[1] = new \App\Imports\NextOfKinImport();
                        }

                        // Import Bank Details sheet if it exists
                        if ($this->sheetCount >= 3) {
                            $sheets[2] = new \App\Imports\BankDetailImport();
                        }

                        return $sheets;
                    }
                };

                Excel::import($import, $file);
            } else {
                // For CSV files, use single sheet import
                Excel::import(new \App\Imports\EmployeeImport(), $file);
            }

            AuditTrail::create([
                'user_id' => auth()->id(),
                'action' => 'imported',
                'description' => 'Imported employee data from Excel',
                'action_timestamp' => now(),
                'log_data' => json_encode(['entity_type' => 'Employee', 'entity_id' => null, 'file_name' => $file->getClientOriginalName()]),
            ]);

            return redirect()->route('employees.index')->with('success', 'Employees imported successfully.');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();

            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = 'Row ' . $failure->row() . ': ' . implode(', ', $failure->errors());
            }

            return redirect()->back()->with('error', 'Import failed with validation errors: ' . implode('; ', $errors));
        } catch (\Exception $e) {
            \Log::error('Employee import error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred during import: ' . $e->getMessage());
        }
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