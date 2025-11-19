<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    /**
     * Display a listing of roles
     */
    public function index(Request $request)
    {
        // Check permission
        if (!auth()->user()->hasPermission('roles.view_any')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = Role::with('permissions');

        // Search filter
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('display_name', 'like', "%{$search}%");
            });
        }

        // Include user count
        $query->withCount('users');

        $perPage = $request->get('per_page', 15);
        $roles = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $roles,
        ]);
    }

    /**
     * Store a newly created role
     */
    public function store(Request $request)
    {
        // Check permission
        if (!auth()->user()->hasPermission('roles.create')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:roles,name', 'alpha_dash'],
            'display_name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_system_role' => ['boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        // Create role
        $role = Role::create($validated);

        // Assign permissions
        if (!empty($validated['permissions'])) {
            $permissions = Permission::whereIn('id', $validated['permissions'])->get();
            foreach ($permissions as $permission) {
                $role->givePermission($permission);
            }
        }

        // Load relationships
        $role->load('permissions');

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully',
            'data' => $role,
        ], 201);
    }

    /**
     * Display the specified role
     */
    public function show(Role $role)
    {
        // Check permission
        if (!auth()->user()->hasPermission('roles.view')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $role->load('permissions', 'users');

        return response()->json([
            'success' => true,
            'data' => $role,
        ]);
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, Role $role)
    {
        // Check permission
        if (!auth()->user()->hasPermission('roles.update')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Prevent editing system roles
        if ($role->is_system_role && !auth()->user()->isSuperAdmin()) {
            return response()->json([
                'error' => 'Cannot modify system roles'
            ], 403);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:50', Rule::unique('roles')->ignore($role->id), 'alpha_dash'],
            'display_name' => ['sometimes', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'permissions' => ['sometimes', 'nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        // Update role
        $role->update($validated);

        // Update permissions if provided
        if (isset($validated['permissions'])) {
            // Remove all existing permissions
            foreach ($role->permissions as $permission) {
                $role->revokePermission($permission);
            }
            // Assign new permissions
            if (!empty($validated['permissions'])) {
                $permissions = Permission::whereIn('id', $validated['permissions'])->get();
                foreach ($permissions as $permission) {
                    $role->givePermission($permission);
                }
            }
        }

        // Reload relationships
        $role->load('permissions');

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully',
            'data' => $role,
        ]);
    }

    /**
     * Remove the specified role
     */
    public function destroy(Role $role)
    {
        // Check permission
        if (!auth()->user()->hasPermission('roles.delete')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Prevent deleting system roles
        if ($role->is_system_role) {
            return response()->json([
                'error' => 'Cannot delete system roles'
            ], 403);
        }

        // Check if role has users
        if ($role->users()->exists()) {
            return response()->json([
                'error' => 'Cannot delete role that is assigned to users'
            ], 422);
        }

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully',
        ]);
    }

    /**
     * Assign role to user
     */
    public function assignToUser(Request $request, Role $role)
    {
        // Check permission
        if (!auth()->user()->hasPermission('roles.assign')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $user = \App\Models\User::findOrFail($validated['user_id']);
        $user->assignRole($role);

        return response()->json([
            'success' => true,
            'message' => 'Role assigned to user successfully',
        ]);
    }

    /**
     * Revoke role from user
     */
    public function revokeFromUser(Request $request, Role $role)
    {
        // Check permission
        if (!auth()->user()->hasPermission('roles.revoke')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $user = \App\Models\User::findOrFail($validated['user_id']);
        $user->removeRole($role);

        return response()->json([
            'success' => true,
            'message' => 'Role revoked from user successfully',
        ]);
    }

    /**
     * Assign permission to role
     */
    public function assignPermission(Request $request, Role $role)
    {
        // Check permission
        if (!auth()->user()->hasPermission('permissions.assign')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'permission_id' => ['required', 'exists:permissions,id'],
        ]);

        $permission = Permission::findOrFail($validated['permission_id']);
        $role->givePermission($permission);

        return response()->json([
            'success' => true,
            'message' => 'Permission assigned to role successfully',
        ]);
    }

    /**
     * Revoke permission from role
     */
    public function revokePermission(Request $request, Role $role)
    {
        // Check permission
        if (!auth()->user()->hasPermission('permissions.revoke')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'permission_id' => ['required', 'exists:permissions,id'],
        ]);

        $permission = Permission::findOrFail($validated['permission_id']);
        $role->revokePermission($permission);

        return response()->json([
            'success' => true,
            'message' => 'Permission revoked from role successfully',
        ]);
    }
}
