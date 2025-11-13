<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable, SoftDeletes;


    protected $fillable = [
        'name',
        'email',
        'password',
        'department',
        'position',
        'employee_id',
        'phone',
        'avatar',
        'status',
    ];

    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    //Relationships
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withTimestamps();
    }


    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions')
            ->withTimestamps();
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function assignmentsAsAssigner()
    {
        return $this->hasMany(Assignment::class, 'assigned_by');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class, 'requested_by');
    }

    public function maintenanceRequestsReviewed()
    {
        return $this->hasMany(MaintenanceRequest::class, 'reviewed_by');
    }

    public function maintenanceRecordsRequested()
    {
        return $this->hasMany(MaintenanceRecord::class, 'requested_by');
    }

    public function maintenanceRecordsAssigned()
    {
        return $this->hasMany(MaintenanceRecord::class, 'assigned_to');
    }

    public function disposalRecordsRequested()
    {
        return $this->hasMany(DisposalRecord::class, 'requested_by');
    }

    public function disposalRecordsApproved()
    {
        return $this->hasMany(DisposalRecord::class, 'approved_by');
    }

    public function disposalRecordsExecuted()
    {
        return $this->hasMany(DisposalRecord::class, 'executed_by');
    }

    public function requests()
    {
        return $this->hasMany(Request::class);
    }

    public function requestsApproved()
    {
        return $this->hasMany(Request::class, 'approved_by');
    }

    // Permission checking methods (Optimized to prevent N+1 queries)
    private $cachedPermissions;
    private $cachedRoles;

    public function hasPermission(string $permission): bool
    {
        if (!isset($this->cachedPermissions)) {
            $this->loadPermissionsCache();
        }

        return in_array($permission, $this->cachedPermissions);
    }

    private function loadPermissionsCache(): void
    {
        // Get direct permissions
        $directPermissions = $this->permissions()
            ->pluck('name')
            ->toArray();

        // Get role-based permissions
        $rolePermissions = $this->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('name')
            ->unique()
            ->toArray();

        $this->cachedPermissions = array_unique(array_merge($directPermissions, $rolePermissions));
    }

    private function loadRolesCache(): void
    {
        $this->cachedRoles = $this->roles()
            ->pluck('name')
            ->toArray();
    }

    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }

    public function hasRole(string $role): bool
    {
        if (!isset($this->cachedRoles)) {
            $this->loadRolesCache();
        }

        return in_array($role, $this->cachedRoles);
    }

    public function assignRole(Role $role): void
    {
        $this->roles()->syncWithoutDetaching($role);
        unset($this->cachedRoles, $this->cachedPermissions);
    }

    public function removeRole(Role $role): void
    {
        $this->roles()->detach($role);
        unset($this->cachedRoles, $this->cachedPermissions);
    }

    public function givePermission(Permission $permission): void
    {
        $this->permissions()->syncWithoutDetaching($permission);
        unset($this->cachedPermissions);
    }

    public function revokePermission(Permission $permission): void
    {
        $this->permissions()->detach($permission);
        unset($this->cachedPermissions);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_administrator');
    }

    // Scopes for efficient queries
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeWithRolesAndPermissions($query)
    {
        return $query->with(['roles.permissions', 'permissions']);
    }

    public function scopeWithActiveAssignments($query)
    {
        return $query->withCount(['assignments as active_assignments_count' => function ($query) {
            $query->where('status', 'active');
        }]);
    }
}
