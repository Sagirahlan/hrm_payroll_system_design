<?php
namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use App\Models\AuditTrail;
use Illuminate\Support\Facades\Auth;

class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware(['permission:view_departments'], ['only' => ['index']]);
        $this->middleware(['permission:create_departments'], ['only' => ['create', 'store']]);
        $this->middleware(['permission:edit_departments'], ['only' => ['edit', 'update']]);
        $this->middleware(['permission:delete_departments'], ['only' => ['destroy']]);
    }

    public function index()
    {
        $departments = Department::paginate(10);
        return view('departments.index', compact('departments'));
    }

    public function create()
    {
        return view('departments.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_name' => 'required|string|max:100|unique:departments',
            'description' => 'nullable|string',
        ]);

        $department = Department::create($validated);

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'created',
            'description' => "Created department: {$department->department_name}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Department', 'entity_id' => $department->department_id]),
        ]);

        return redirect()->route('departments.index')->with('success', 'Department created successfully.');
    }

    public function edit(Department $department)
    {
        return view('departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'department_name' => 'required|string|max:100|unique:departments,department_name,' . $department->department_id . ',department_id',
            'description' => 'nullable|string',
        ]);

        $department->update($validated);

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'updated',
            'description' => "Updated department: {$department->department_name}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Department', 'entity_id' => $department->department_id]),
        ]);

        return redirect()->route('departments.index')->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        if ($department->employees()->count() > 0) {
            return redirect()->route('departments.index')->with('error', 'Cannot delete department with assigned employees.');
        }

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'deleted',
            'description' => "Deleted department: {$department->department_name}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Department', 'entity_id' => $department->department_id]),
        ]);

        $department->delete();
        return redirect()->route('departments.index')->with('success', 'Department deleted successfully.');
    }
}