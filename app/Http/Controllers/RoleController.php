<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Log;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:manage_users']);
    }

    public function index()
    {
        $roles = Role::all();
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::pluck('name', 'id');
        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('Role creation request:', $request->all());
        $request->validate([
            'name' => 'required|unique:roles,name',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        try {
            $role = Role::create([
                'name' => $request->name,
                // Spatie's Role model doesn't have a description field by default
                // If you added a description column to the roles table, include it
                // 'description' => $request->description,
            ]);

            if ($request->has('permissions')) {
                // Get permission names by their IDs
                $permissionIds = $request->permissions;
                $permissions = Permission::whereIn('id', $permissionIds)->pluck('name')->toArray();
                \Illuminate\Support\Facades\Log::info('Permissions to sync:', $permissions);
                $role->syncPermissions($permissions);
                \Illuminate\Support\Facades\Log::info('Permissions synced for role:', ['role_id' => $role->id]);
            }

            return redirect()->route('roles.index')->with('success', 'Role created successfully.');
        } catch (\Exception $e) {
            Log::error('Role creation failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to create role: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);
        $permissions = Permission::pluck('name', 'id');
        $rolePermissions = $role->permissions()->pluck('id')->toArray();
        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, $id)
    {
        \Illuminate\Support\Facades\Log::info('Role update request:', $request->all());
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => 'required|unique:roles,name,' . $id,
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        try {
            $role->update([
                'name' => $request->name,
                // Include description if added to roles table
                // 'description' => $request->description,
            ]);

            if ($request->has('permissions')) {
                // Get permission names by their IDs
                $permissionIds = $request->permissions;
                $permissions = Permission::whereIn('id', $permissionIds)->pluck('name')->toArray();
                \Illuminate\Support\Facades\Log::info('Permissions to sync:', $permissions);
                $role->syncPermissions($permissions);
                \Illuminate\Support\Facades\Log::info('Permissions synced for role:', ['role_id' => $role->id]);
            } else {
                $role->syncPermissions([]);
                \Illuminate\Support\Facades\Log::info('No permissions to sync, cleared all permissions for role:', ['role_id' => $role->id]);
            }


            return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
        } catch (\Exception $e) {
            Log::error('Role update failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to update role: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $role = Role::findOrFail($id);
            $role->delete();

            return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Role deletion failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to delete role: ' . $e->getMessage());
        }
    }
}