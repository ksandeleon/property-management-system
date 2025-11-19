<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    /**
     * Display a listing of permissions
     */
    public function index(Request $request)
    {
        // Check permission
        if (!auth()->user()->hasPermission('permissions.view_any')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = Permission::query();

        // Search filter
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('display_name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'category');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 50);
        $permissions = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $permissions,
        ]);
    }

    /**
     * Display the specified permission
     */
    public function show(Permission $permission)
    {
        // Check permission
        if (!auth()->user()->hasPermission('permissions.view')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $permission->load('roles', 'users');

        return response()->json([
            'success' => true,
            'data' => $permission,
        ]);
    }

    /**
     * Get permissions grouped by category
     */
    public function byCategory()
    {
        // Check permission
        if (!auth()->user()->hasPermission('permissions.view_any')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $permissions = Permission::all()->groupBy('category');

        return response()->json([
            'success' => true,
            'data' => $permissions,
        ]);
    }

    /**
     * Get all unique categories
     */
    public function categories()
    {
        // Check permission
        if (!auth()->user()->hasPermission('permissions.view_any')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $categories = Permission::distinct()->pluck('category');

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Assign permission to user
     */
    public function assignToUser(Request $request, Permission $permission)
    {
        // Check permission
        if (!auth()->user()->hasPermission('permissions.assign')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $user = User::findOrFail($validated['user_id']);
        $user->givePermission($permission);

        return response()->json([
            'success' => true,
            'message' => 'Permission assigned to user successfully',
        ]);
    }

    /**
     * Revoke permission from user
     */
    public function revokeFromUser(Request $request, Permission $permission)
    {
        // Check permission
        if (!auth()->user()->hasPermission('permissions.revoke')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $user = User::findOrFail($validated['user_id']);
        $user->revokePermission($permission);

        return response()->json([
            'success' => true,
            'message' => 'Permission revoked from user successfully',
        ]);
    }

    /**
     * Bulk assign permissions to user
     */
    public function bulkAssignToUser(Request $request)
    {
        // Check permission
        if (!auth()->user()->hasPermission('permissions.assign')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'permission_ids' => ['required', 'array'],
            'permission_ids.*' => ['exists:permissions,id'],
        ]);

        $user = User::findOrFail($validated['user_id']);
        $permissions = Permission::whereIn('id', $validated['permission_ids'])->get();

        foreach ($permissions as $permission) {
            $user->givePermission($permission);
        }

        return response()->json([
            'success' => true,
            'message' => 'Permissions assigned to user successfully',
        ]);
    }

    /**
     * Get user's direct permissions
     */
    public function userPermissions(User $user)
    {
        // Check permission
        if (!auth()->user()->hasPermission('permissions.view_any')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $directPermissions = $user->permissions;
        $rolePermissions = $user->roles()->with('permissions')->get()
            ->pluck('permissions')
            ->flatten()
            ->unique('id');

        return response()->json([
            'success' => true,
            'data' => [
                'direct_permissions' => $directPermissions,
                'role_permissions' => $rolePermissions,
                'all_permissions' => $directPermissions->merge($rolePermissions)->unique('id'),
            ],
        ]);
    }
}
