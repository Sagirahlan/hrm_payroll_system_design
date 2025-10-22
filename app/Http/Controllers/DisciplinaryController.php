<?php
namespace App\Http\Controllers;

use App\Models\DisciplinaryAction;
use App\Models\Employee;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class DisciplinaryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware(['permission:view_disciplinary_actions'], ['only' => ['index', 'show']]);
        $this->middleware(['permission:create_disciplinary_actions'], ['only' => ['create', 'store']]);
        $this->middleware(['permission:edit_disciplinary_actions'], ['only' => ['edit', 'update']]);
        $this->middleware(['permission:delete_disciplinary_actions'], ['only' => ['destroy']]);
    }
    public function index(Request $request)
    {
        $query = DisciplinaryAction::with('employee');

        // Search functionality
        if ($request->has('search') && !empty($request->input('search'))) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('employee', function ($q) use ($search) {
                    $q->whereRaw("LOWER(CONCAT_WS(' ', first_name, middle_name, surname)) LIKE ?", ["%" . strtolower($search) . "%"])
                      ->orWhere('reg_no', 'like', "%{$search}%");
                })
                ->orWhere('action_type', 'like', "%{$search}%")
                ->orWhere('status', 'like', "%{$search}%");
            });
        }

        // Filter by department using department_id
        if ($request->filled('department')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('department_id', $request->department);
            });
        }

        // Filter by status
        if ($request->has('status') && !empty($request->input('status'))) {
            $query->where('status', $request->input('status'));
        }

        $actions = $query->paginate(10);
        $departments = Employee::select('department_id')->distinct()->pluck('department_id');
        $statuses = ['Open', 'Resolved', 'Pending'];

        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'viewed_disciplinary_actions',
            'description' => "Viewed disciplinary actions with query: search='{$request->input('search')}', filter='{$request->input('filter')}'",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'DisciplinaryAction', 'entity_id' => null]),
        ]);

        return view('disciplinary.index', compact('actions', 'departments', 'statuses'));
    }

    public function show(DisciplinaryAction $disciplinary)
    {
        $disciplinary->load('employee');
        $disciplinaryHistory = DisciplinaryAction::where('employee_id', $disciplinary->employee_id)
            ->orderBy('action_date', 'desc')
            ->get();
    
        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'viewed',
            'description' => "Viewed disciplinary action ID: {$disciplinary->action_id}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'DisciplinaryAction', 'entity_id' => $disciplinary->action_id]),
        ]);
    
        return view('disciplinary.show', [
            'action' => $disciplinary,
            'disciplinaryHistory' => $disciplinaryHistory
        ]);
    }

    public function create(Request $request)
    {
        $query = Employee::with('department')
            ->where('status', 'Active')
            ->whereHas('appointmentType', function ($q) {
                $q->where('name', 'Permanent');
            });
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereRaw("LOWER(CONCAT_WS(' ', first_name, middle_name, surname)) LIKE ?", ["%" . strtolower($search) . "%"])
                  ->orWhere('reg_no', 'like', "%{$search}%");
            });
        }
        
        // Filter by department
        if ($request->filled('department')) {
            $query->where('department_id', $request->department);
        }
        
        // Filter by status (though we're already filtering by Active)
        if ($request->filled('employee_status')) {
            $query->where('status', $request->employee_status);
        }
        
        $employees = $query->paginate(10);
        
        // Get departments for filter dropdown
        $departments = \App\Models\Department::all();
        
        return view('disciplinary.create', compact('employees', 'departments'));
    }

    public function edit(DisciplinaryAction $disciplinary)
    {
        if ($disciplinary->employee) {
            $employee = $disciplinary->employee;
            if ($disciplinary->action_type === 'suspended' && strtolower($employee->status) === 'active') {
                $employee->status = 'Suspended';
                $employee->save();

                AuditTrail::create([
                    'user_id' => auth()->id(),
                    'action' => 'updated',
                    'description' => "Employee ID {$employee->employee_id} status set to Suspended due to disciplinary action ID: {$disciplinary->action_id}",
                    'action_timestamp' => now(),
                    'log_data' => json_encode(['entity_type' => 'Employee', 'entity_id' => $employee->employee_id]),
                ]);
            }
            if ($disciplinary->action_type === 'active' && strtolower($employee->status) === 'suspended') {
                $employee->status = 'Active';
                $employee->save();

                AuditTrail::create([
                    'user_id' => auth()->id(),
                    'action' => 'updated',
                    'description' => "Employee ID {$employee->employee_id} status set to Active due to disciplinary action ID: {$disciplinary->action_id}",
                    'action_timestamp' => now(),
                    'log_data' => json_encode(['entity_type' => 'Employee', 'entity_id' => $employee->employee_id]),
                ]);
            }
            if (strtolower($disciplinary->status) === 'resolved' && strtolower($employee->status) === 'suspended') {
                $employee->status = 'Active';
                $employee->save();

                AuditTrail::create([
                    'user_id' => auth()->id(),
                    'action' => 'updated',
                    'description' => "Employee ID {$employee->employee_id} status set to Active due to disciplinary action ID: {$disciplinary->action_id} being resolved",
                    'action_timestamp' => now(),
                    'log_data' => json_encode(['entity_type' => 'Employee', 'entity_id' => $employee->employee_id]),
                ]);
            }
        }

        $employees = Employee::all();
        return view('disciplinary.edit', ['action' => $disciplinary, 'employees' => $employees]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'action_type' => 'required|string|max:100',
            'description' => 'required|string',
            'action_date' => 'required|date',
            'status' => 'required|in:Open,Resolved,Pending',
        ]);

        $action = DisciplinaryAction::create($validated);

        // If the action type is 'suspended', update the employee's status to 'Suspended' if currently 'Active'
        // If the action type is 'active', update the employee's status to 'Active' if currently 'Suspended'
        // If the disciplinary action status is 'Resolved', update the employee's status to 'Active' if currently 'Suspended'
        $actionType = strtolower($validated['action_type']);
        $employee = Employee::find($validated['employee_id']);
        if ($employee) {
            if ($actionType === 'suspended' && strtolower($employee->status) === 'active') {
                $employee->status = 'Suspended';
                $employee->save();

                AuditTrail::create([
                    'user_id' => auth()->id(),
                    'action' => 'updated',
                    'description' => "Employee ID {$employee->employee_id} status set to Suspended due to disciplinary action ID: {$action->action_id}",
                    'action_timestamp' => now(),
                    'log_data' => json_encode(['entity_type' => 'Employee', 'entity_id' => $employee->employee_id]),
                ]);
            }
            if ($actionType === 'active' && strtolower($employee->status) === 'suspended') {
                $employee->status = 'Active';
                $employee->save();

                AuditTrail::create([
                    'user_id' => auth()->id(),
                    'action' => 'updated',
                    'description' => "Employee ID {$employee->employee_id} status set to Active due to disciplinary action ID: {$action->action_id}",
                    'action_timestamp' => now(),
                    'log_data' => json_encode(['entity_type' => 'Employee', 'entity_id' => $employee->employee_id]),
                ]);
            }
            // New logic: If the disciplinary action status is 'Resolved', update employee status to 'Active' if currently 'Suspended'
            if (strtolower($validated['status']) === 'resolved' && strtolower($employee->status) === 'suspended') {
                $employee->status = 'Active';
                $employee->save();

                AuditTrail::create([
                    'user_id' => auth()->id(),
                    'action' => 'updated',
                    'description' => "Employee ID {$employee->employee_id} status set to Active due to disciplinary action ID: {$action->action_id} being resolved",
                    'action_timestamp' => now(),
                    'log_data' => json_encode(['entity_type' => 'Employee', 'entity_id' => $employee->employee_id]),
                ]);
            }
        }

        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'created',
            'description' => "Added disciplinary action for employee ID: {$validated['employee_id']}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'DisciplinaryAction', 'entity_id' => $action->action_id]),
        ]);
        

        return redirect()->route('disciplinary.index')->with('success', 'Disciplinary action recorded.');
    }

    public function update(Request $request, DisciplinaryAction $disciplinary)
    {
        $validated = $request->validate([
            'resolution_date' => 'nullable|date',
            'status' => 'required|in:Open,Resolved,Pending',
        ]);
    
        $disciplinary->update($validated);

        // If the action type is 'suspended', update the employee's status to 'Suspended' if currently 'Active'
        // If the action type is 'active', update the employee's status to 'Active' if currently 'Suspended'
        // If the disciplinary action status is 'Resolved', update the employee's status to 'Active' if currently 'Suspended'
        if ($disciplinary->employee) {
            $employee = $disciplinary->employee;
            if ($disciplinary->action_type === 'suspended' && strtolower($employee->status) === 'active') {
                $employee->status = 'Suspended';
                $employee->save();

                AuditTrail::create([
                    'user_id' => auth()->id(),
                    'action' => 'updated',
                    'description' => "Employee ID {$employee->employee_id} status set to Suspended due to disciplinary action ID: {$disciplinary->action_id}",
                    'action_timestamp' => now(),
                    'log_data' => json_encode(['entity_type' => 'Employee', 'entity_id' => $employee->employee_id]),
                ]);
            }
            if ($disciplinary->action_type === 'active' && strtolower($employee->status) === 'suspended') {
                $employee->status = 'Active';
                $employee->save();

                AuditTrail::create([
                    'user_id' => auth()->id(),
                    'action' => 'updated',
                    'description' => "Employee ID {$employee->employee_id} status set to Active due to disciplinary action ID: {$disciplinary->action_id}",
                    'action_timestamp' => now(),
                    'log_data' => json_encode(['entity_type' => 'Employee', 'entity_id' => $employee->employee_id]),
                ]);
            }
            // New logic: If the disciplinary action status is 'Resolved', update employee status to 'Active' if currently 'Suspended'
            if (strtolower($validated['status']) === 'resolved' && strtolower($employee->status) === 'suspended') {
                $employee->status = 'Active';
                $employee->save();

                AuditTrail::create([
                    'user_id' => auth()->id(),
                    'action' => 'updated',
                    'description' => "Employee ID {$employee->employee_id} status set to Active due to disciplinary action ID: {$disciplinary->action_id} being resolved",
                    'action_timestamp' => now(),
                    'log_data' => json_encode(['entity_type' => 'Employee', 'entity_id' => $employee->employee_id]),
                ]);
            }
        }
    
        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'updated',
            'description' => "Updated disciplinary action ID: {$disciplinary->action_id}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'DisciplinaryAction', 'entity_id' => $disciplinary->action_id]),
        ]);
    
        return redirect()->route('disciplinary.index')->with('success', 'Disciplinary action updated.');
    }

    public function destroy(DisciplinaryAction $disciplinary)
    {
        $disciplinary->delete();
        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'deleted',
            'description' => "Deleted disciplinary action ID: {$disciplinary->action_id}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'DisciplinaryAction', 'entity_id' => $disciplinary->action_id]),
        ]);

        return redirect()->route('disciplinary.index')->with('success', 'Disciplinary action deleted.');
    }

    public function searchEmployees(Request $request)
    {
        $search = $request->input('search');
        $employees = Employee::with('department')
            ->where(function ($query) use ($search) {
                $query->whereRaw("LOWER(CONCAT_WS(' ', first_name, middle_name, surname)) LIKE ?", ["%" . strtolower($search) . "%"])
                      ->orWhere('reg_no', 'like', "%{$search}%");
            })
            ->where('status', '!=', 'Retired')
            ->get();

        return response()->json($employees);
    }

    public function storeSelectedEmployee(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,employee_id'
        ]);

        session(['selected_employee_id' => $request->employee_id]);

        return response()->json(['status' => 'success']);
    }
}
