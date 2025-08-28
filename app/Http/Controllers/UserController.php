<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use App\Models\AuditTrail;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:manage_users']);
    }

    public function index(Request $request)
    {
        Log::info('User Index Search', [
            'search' => $request->input('search'),
            'filter' => $request->input('filter')
        ]);

        $query = User::with(['employee', 'roles']);

        // Search logic
        if ($search = trim($request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('employee', function ($sub) use ($search) {
                      $sub->where('first_name', 'like', "%{$search}%")
                          ->orWhere('surname', 'like', "%{$search}%")
                          ->orWhereRaw("CONCAT(first_name, ' ', surname) LIKE ?", ["%{$search}%"]);
                  });
            });
        }

        // Role filter logic
        if ($filter = $request->input('filter')) {
            $query->whereHas('roles', function ($q) use ($filter) {
                $q->where('name', $filter);
            });
        }

        $users = $query->paginate(10)->withQueryString();
        
        // Get available roles for filter dropdown
        $roles = Role::pluck('name');
        
        // Get employees without users for bulk creation
        $employeesWithoutUsers = Employee::whereNotIn('employee_id', 
            User::whereNotNull('employee_id')->pluck('employee_id')
        )->count();

        Log::info('User Index Results', ['count' => $users->count()]);

        AuditTrail::create([
            'user_id' => auth()->id(),
            'action' => 'viewed',
            'description' => 'Viewed user list',
            'action_timestamp' => now(),
            'entity_type' => 'User',
            'entity_id' => auth()->id(),
        ]);

        return view('users.index', compact('users', 'roles', 'employeesWithoutUsers'));
    }

    public function create()
    {
        try {
            $usedEmployeeIds = User::whereNotNull('employee_id')->pluck('employee_id');
            $employees = Employee::select('employee_id', 'first_name', 'surname', 'email')
                ->whereNotIn('employee_id', $usedEmployeeIds)
                ->get();

            $roles = Role::pluck('name', 'id');

            return view('users.create', compact('employees', 'roles'));
        } catch (\Exception $e) {
            Log::error('Failed to fetch employees', ['error' => $e->getMessage()]);

            return view('users.create', [
                'employees' => collect(),
                'roles' => collect(),
            ])->with('error', 'Unable to load employees: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,employee_id|unique:users,employee_id',
            'username'    => 'required|string|max:255|unique:users',
            'email'       => 'required|email|max:255|unique:users',
            'password'    => 'required|string|min:8|confirmed',
            'role'        => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!Role::where('name', $value)->exists()) {
                        $fail('The selected role is invalid.');
                    }
                }
            ]
        ]);

        try {
            $user = User::create([
                'username'     => $validated['username'],
                'email'        => $validated['email'],
                'password_hash' => bcrypt($validated['password']),
                'employee_id'  => $validated['employee_id'],
            ]);

            $user->assignRole($validated['role']);

            AuditTrail::create([
                'user_id' => auth()->id(),
                'action' => 'created',
                'description' => "Created user: {$user->username} with role: {$validated['role']}",
                'action_timestamp' => now(),
                'entity_type' => 'User',
                'entity_id' => $user->id,
            ]);

            return redirect()->route('users.index')
                ->with('success', "User created successfully with role: {$validated['role']}.");
        } catch (\Exception $e) {
            Log::error('User creation failed', ['error' => $e->getMessage()]);

            return back()->with('error', 'Failed to create user: ' . $e->getMessage());
        }
    }

    public function bulkCreateUsers(Request $request)
    {
        try {
            // Get the employee role
            $employeeRole = Role::where('name', 'employee')->first();
            
            if (!$employeeRole) {
                return back()->with('error', 'Employee role not found. Please create an "employee" role first.');
            }

            // Get employees without user accounts
            $employeesWithoutUsers = Employee::whereNotIn('employee_id', 
                User::whereNotNull('employee_id')->pluck('employee_id')
            )->whereNotNull('email')->take(30)->get();

            if ($employeesWithoutUsers->isEmpty()) {
                return back()->with('info', 'No employees found without user accounts or missing email addresses.');
            }

            $createdCount = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($employeesWithoutUsers as $employee) {
                try {
                    // Generate username from email (part before @)
                    $username = strtolower(explode('@', $employee->email)[0]);
                    
                    // Ensure username is unique
                    $originalUsername = $username;
                    $counter = 1;
                    while (User::where('username', $username)->exists()) {
                        $username = $originalUsername . $counter;
                        $counter++;
                    }

                    // Create user
                    $user = User::create([
                        'username' => $username,
                        'email' => $employee->email,
                        'password_hash' => bcrypt('12345678'),
                        'employee_id' => $employee->employee_id,
                    ]);

                    $user->assignRole($employeeRole->name);

                    // Log audit trail
                    AuditTrail::create([
                        'user_id' => auth()->id(),
                        'action' => 'bulk_created',
                        'description' => "Bulk created user: {$user->username} for employee: {$employee->first_name} {$employee->surname}",
                        'action_timestamp' => now(),
                        'entity_type' => 'User',
                        'entity_id' => $user->id,
                    ]);

                    $createdCount++;
                } catch (\Exception $e) {
                    $errors[] = "Failed to create user for {$employee->first_name} {$employee->surname}: " . $e->getMessage();
                    Log::error('Bulk user creation failed for employee', [
                        'employee_id' => $employee->employee_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();

            $remaining = Employee::whereNotIn('employee_id', 
                User::whereNotNull('employee_id')->pluck('employee_id')
            )->whereNotNull('email')->count();

            $message = "Successfully created {$createdCount} user accounts with default password '12345678'.";
            if($remaining > 0) {
                $message .= " There are {$remaining} more employees without user accounts. You can run this process again.";
            }

            if (!empty($errors)) {
                $message .= " Errors: " . implode(', ', array_slice($errors, 0, 3));
                if (count($errors) > 3) {
                    $message .= " and " . (count($errors) - 3) . " more.";
                }
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Bulk user creation failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Bulk user creation failed: ' . $e->getMessage());
        }
    }

    public function showEmployeesWithoutUsers(Request $request)
    {
        $query = Employee::whereNotIn('employee_id', 
            User::whereNotNull('employee_id')->pluck('employee_id')
        );

        // Search functionality
        if ($search = trim($request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('surname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%")
                  ->orWhereRaw("CONCAT(first_name, ' ', surname) LIKE ?", ["%{$search}%"]);
            });
        }

        // Filter by department if available
        if ($departmentId = $request->input('department_id')) {
            $query->where('department_id', $departmentId);
        }

        // Filter by employees with/without email
        if ($request->input('email_filter') === 'with_email') {
            $query->whereNotNull('email');
        } elseif ($request->input('email_filter') === 'without_email') {
            $query->whereNull('email');
        }

        $employees = $query->paginate(15)->withQueryString();
        
        // Get departments for filter
        $departments = \App\Models\Department::select('department_id', 'department_name')->get();

        return view('users.employees-without-users', compact('employees', 'departments'));
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);

            if (Auth::id() === $user->id) {
                return back()->with('error', 'You cannot delete your own account.');
            }

            $username = $user->username;
            $employeeId = $user->employee_id;

            $user->delete();

            AuditTrail::create([
                'user_id' => auth()->id(),
                'action' => 'deleted',
                'description' => "Deleted user: {$username} with employee ID: {$employeeId}",
                'action_timestamp' => now(),
                'entity_type' => 'User',
                'entity_id' => $id,
            ]);

            return redirect()->route('users.index')->with('success', 'User deleted successfully.');
        } catch (\Exception $e) {
            Log::error('User deletion failed', ['error' => $e->getMessage()]);

            return back()->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }

    public function updateRole(Request $request, $id)
    {
        $request->validate([
            'role_name' => 'required|string|exists:roles,name',
        ]);

        try {
            $user = User::findOrFail($id);

            $user->syncRoles([$request->input('role_name')]);

            AuditTrail::create([
                'user_id' => auth()->id(),
                'action' => 'updated',
                'description' => "Updated role for user: {$user->username} to {$request->input('role_name')}",
                'action_timestamp' => now(),
                'entity_type' => 'User',
                'entity_id' => $user->id,
            ]);

            return redirect()->route('users.index')->with('success', "User role updated to {$request->input('role_name')}.");
        } catch (\Exception $e) {
            Log::error('User role update failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to update user role: ' . $e->getMessage());
        }
    }

    public function resetPassword(Request $request, User $user)
    {
        try {
            Log::info('Resetting password', ['user_id' => $user->id]);

            $user->update([
                'password_hash' => Hash::make('12345678'),
            ]);

            AuditTrail::create([
                'user_id' => auth()->id(),
                'action' => 'password_reset',
                'description' => "Reset password for user: {$user->username}",
                'action_timestamp' => now(),
                'entity_type' => 'User',
                'entity_id' => $user->id,
            ]);

            Log::info('Password reset successfully', ['user_id' => $user->id]);

            return redirect()->route('users.index')->with('success', 'Password reset to default successfully.');
        } catch (\Exception $e) {
            Log::error('Password reset failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to reset password: ' . $e->getMessage());
        }
    }
}