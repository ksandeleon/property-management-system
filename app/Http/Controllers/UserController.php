<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        // Check permission
        if (!auth()->user()->hasPermission('users.view_any')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = User::query()->withRolesAndPermissions()->withActiveAssignments();

        // Search filter
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Department filter
        if ($request->has('department')) {
            $query->where('department', $request->department);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 15);
        $users = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        // Check permission
        if (!auth()->user()->hasPermission('users.create')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'department' => ['required', 'string', 'max:100'],
            'position' => ['required', 'string', 'max:100'],
            'employee_id' => ['required', 'string', 'max:50', 'unique:users,employee_id'],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        // Hash password
        $validated['password'] = Hash::make($validated['password']);

        // Create user
        $user = User::create($validated);

        // Assign roles
        if (!empty($validated['roles'])) {
            $roles = Role::whereIn('id', $validated['roles'])->get();
            foreach ($roles as $role) {
                $user->assignRole($role);
            }
        }

        // Load relationships
        $user->load('roles.permissions', 'permissions');

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user,
        ], 201);
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        // Check permission
        if (!auth()->user()->hasPermission('users.view')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $user->load('roles.permissions', 'permissions', 'accountableItems', 'assignments');

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        // Check permission
        if (!auth()->user()->hasPermission('users.update')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => ['sometimes', 'nullable', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'department' => ['sometimes', 'string', 'max:100'],
            'position' => ['sometimes', 'string', 'max:100'],
            'employee_id' => ['sometimes', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['sometimes', Rule::in(['active', 'inactive', 'suspended'])],
            'roles' => ['sometimes', 'nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        // Hash password if provided
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Update user
        $user->update($validated);

        // Update roles if provided
        if (isset($validated['roles'])) {
            // Remove existing roles
            foreach ($user->roles as $role) {
                $user->removeRole($role);
            }
            // Assign new roles
            if (!empty($validated['roles'])) {
                $roles = Role::whereIn('id', $validated['roles'])->get();
                foreach ($roles as $role) {
                    $user->assignRole($role);
                }
            }
        }

        // Reload relationships
        $user->load('roles.permissions', 'permissions');

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user,
        ]);
    }

    /**
     * Remove the specified user (soft delete)
     */
    public function destroy(User $user)
    {
        // Check permission
        if (!auth()->user()->hasPermission('users.delete')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Prevent deleting yourself
        if (auth()->id() === $user->id) {
            return response()->json(['error' => 'Cannot delete yourself'], 422);
        }

        // Check for active assignments
        if ($user->assignments()->where('status', 'active')->exists()) {
            return response()->json([
                'error' => 'Cannot delete user with active item assignments'
            ], 422);
        }

        // Check for active accountable items
        if ($user->activeAccountableItems()->exists()) {
            return response()->json([
                'error' => 'Cannot delete user who is accountable for active items'
            ], 422);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully',
        ]);
    }

    /**
     * Restore soft-deleted user
     */
    public function restore($id)
    {
        // Check permission
        if (!auth()->user()->hasPermission('users.restore')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        return response()->json([
            'success' => true,
            'message' => 'User restored successfully',
            'data' => $user,
        ]);
    }

    /**
     * Permanently delete user
     */
    public function forceDestroy($id)
    {
        // Check permission
        if (!auth()->user()->hasPermission('users.force_delete')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $user = User::onlyTrashed()->findOrFail($id);

        // Safety check
        if ($user->assignments()->exists() || $user->accountableItems()->exists()) {
            return response()->json([
                'error' => 'Cannot permanently delete user with assignment or accountability history'
            ], 422);
        }

        $user->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'User permanently deleted',
        ]);
    }

    /**
     * Get trashed users
     */
    public function trashed()
    {
        // Check permission
        if (!auth()->user()->hasPermission('users.view_any')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $users = User::onlyTrashed()->with('roles')->get();

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }
}
