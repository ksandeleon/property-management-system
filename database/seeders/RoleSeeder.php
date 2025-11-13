<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Super Administrator - ALL permissions
        $superAdmin = Role::firstOrCreate(
            ['name' => 'super_administrator'],
            [
                'display_name' => 'Super Administrator',
                'description' => 'Full system access with all permissions',
                'is_system_role' => true,
            ]
        );
        $superAdmin->permissions()->sync(Permission::all());

        // 2. Property Administrator
        $propertyAdmin = Role::firstOrCreate(
            ['name' => 'property_administrator'],
            [
                'display_name' => 'Property Administrator',
                'description' => 'Manage all property operations',
                'is_system_role' => true,
            ]
        );
        $propertyAdminPerms = Permission::whereIn('category', [
            'items', 'assignments', 'returns', 'maintenance',
            'disposals', 'reports', 'activity_logs', 'categories',
            'locations', 'dashboard'
        ])->pluck('id');
        $propertyAdmin->permissions()->sync($propertyAdminPerms);

        // 3. Property Manager
        $propertyManager = Role::firstOrCreate(
            ['name' => 'property_manager'],
            [
                'display_name' => 'Property Manager',
                'description' => 'Day-to-day property operations',
                'is_system_role' => true,
            ]
        );
        $managerPerms = Permission::whereIn('name', [
            'items.view_any', 'items.view', 'items.create', 'items.update',
            'items.generate_qr', 'items.print_qr', 'items.view_history',
            'assignments.view_any', 'assignments.create', 'assignments.assign_to_others',
            'returns.view_any', 'returns.create', 'returns.mark_returned',
            'maintenance.view_any', 'maintenance.create', 'maintenance.schedule',
            'reports.view', 'reports.user_assignments', 'reports.item_history', 'reports.inventory_summary',
            'activity_logs.view_any',
            'dashboard.view', 'dashboard.view_stats', 'dashboard.view_charts'
        ])->pluck('id');
        $propertyManager->permissions()->sync($managerPerms);

        // 4. Inventory Clerk
        $inventoryClerk = Role::firstOrCreate(
            ['name' => 'inventory_clerk'],
            [
                'display_name' => 'Inventory Clerk',
                'description' => 'Handle basic inventory tasks',
                'is_system_role' => true,
            ]
        );
        $clerkPerms = Permission::whereIn('name', [
            'items.view_any', 'items.view', 'items.create', 'items.update',
            'items.generate_qr', 'items.print_qr',
            'categories.view_any', 'locations.view_any',
            'assignments.view_any', 'assignments.create',
            'returns.view_any', 'returns.create', 'returns.mark_returned',
            'reports.view', 'reports.inventory_summary',
            'dashboard.view'
        ])->pluck('id');
        $inventoryClerk->permissions()->sync($clerkPerms);

        // 5. Assignment Officer
        $assignmentOfficer = Role::firstOrCreate(
            ['name' => 'assignment_officer'],
            [
                'display_name' => 'Assignment Officer',
                'description' => 'Handle item assignments and returns',
                'is_system_role' => true,
            ]
        );
        $officerPerms = Permission::whereIn('name', [
            'items.view_any', 'items.view',
            'assignments.view_any', 'assignments.view', 'assignments.create', 'assignments.assign_to_others',
            'returns.view_any', 'returns.view', 'returns.create', 'returns.mark_returned', 'returns.inspect',
            'reports.view', 'reports.user_assignments',
            'dashboard.view', 'dashboard.view_pending'
        ])->pluck('id');
        $assignmentOfficer->permissions()->sync($officerPerms);

        // 6. Maintenance Coordinator
        $maintenanceCoord = Role::firstOrCreate(
            ['name' => 'maintenance_coordinator'],
            [
                'display_name' => 'Maintenance Coordinator',
                'description' => 'Manage maintenance operations',
                'is_system_role' => true,
            ]
        );
        $maintenancePerms = Permission::whereIn('name', [
            'items.view_any', 'items.view',
            'maintenance.view_any', 'maintenance.view', 'maintenance.create',
            'maintenance.update', 'maintenance.schedule', 'maintenance.complete', 'maintenance.assign',
            'reports.view', 'reports.maintenance',
            'dashboard.view'
        ])->pluck('id');
        $maintenanceCoord->permissions()->sync($maintenancePerms);

        // 7. Auditor
        $auditor = Role::firstOrCreate(
            ['name' => 'auditor'],
            [
                'display_name' => 'Auditor',
                'description' => 'View-only access for auditing',
                'is_system_role' => true,
            ]
        );
        $auditorPerms = Permission::where('name', 'like', '%.view%')
            ->orWhere('name', 'like', 'reports.%')
            ->orWhere('name', 'like', 'activity_logs.%')
            ->orWhere('name', 'like', 'dashboard.view%')
            ->pluck('id');
        $auditor->permissions()->sync($auditorPerms);

        // 8. Department Head
        $deptHead = Role::firstOrCreate(
            ['name' => 'department_head'],
            [
                'display_name' => 'Department Head',
                'description' => 'View and request for their department',
                'is_system_role' => true,
            ]
        );
        $deptPerms = Permission::whereIn('name', [
            'items.view_any', 'items.view',
            'assignments.view_any', 'assignments.view',
            'requests.view', 'requests.create',
            'reports.view', 'reports.user_assignments',
            'dashboard.view'
        ])->pluck('id');
        $deptHead->permissions()->sync($deptPerms);

        // 9. Staff User
        $staff = Role::firstOrCreate(
            ['name' => 'staff_user'],
            [
                'display_name' => 'Staff User',
                'description' => 'View own assigned items',
                'is_system_role' => true,
            ]
        );
        $staffPerms = Permission::whereIn('name', [
            'items.view',
            'assignments.view_own',
            'returns.create',
            'requests.create',
            'notifications.view',
            'dashboard.view'
        ])->pluck('id');
        $staff->permissions()->sync($staffPerms);

        // 10. Report Viewer
        $reportViewer = Role::firstOrCreate(
            ['name' => 'report_viewer'],
            [
                'display_name' => 'Report Viewer',
                'description' => 'View reports only',
                'is_system_role' => true,
            ]
        );
        $reportPerms = Permission::where('name', 'like', 'reports.%')
            ->orWhere('name', 'like', 'dashboard.view%')
            ->orWhereIn('name', ['items.view_any', 'items.view'])
            ->pluck('id');
        $reportViewer->permissions()->sync($reportPerms);

        $this->command->info('Roles seeded successfully with permissions!');
    }
}
