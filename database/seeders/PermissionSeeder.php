<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // User Management Permissions
            ['name' => 'users.view_any', 'display_name' => 'View All Users', 'description' => 'View list of all users', 'category' => 'users'],
            ['name' => 'users.view', 'display_name' => 'View User', 'description' => 'View individual user details', 'category' => 'users'],
            ['name' => 'users.create', 'display_name' => 'Create User', 'description' => 'Create new user accounts', 'category' => 'users'],
            ['name' => 'users.update', 'display_name' => 'Update User', 'description' => 'Update user information', 'category' => 'users'],
            ['name' => 'users.delete', 'display_name' => 'Delete User', 'description' => 'Delete user accounts', 'category' => 'users'],
            ['name' => 'users.restore', 'display_name' => 'Restore User', 'description' => 'Restore soft-deleted users', 'category' => 'users'],
            ['name' => 'users.force_delete', 'display_name' => 'Force Delete User', 'description' => 'Permanently delete users', 'category' => 'users'],

            // Role Management
            ['name' => 'roles.view_any', 'display_name' => 'View All Roles', 'description' => 'View all roles', 'category' => 'roles'],
            ['name' => 'roles.view', 'display_name' => 'View Role', 'description' => 'View role details', 'category' => 'roles'],
            ['name' => 'roles.create', 'display_name' => 'Create Role', 'description' => 'Create new roles', 'category' => 'roles'],
            ['name' => 'roles.update', 'display_name' => 'Update Role', 'description' => 'Update role details', 'category' => 'roles'],
            ['name' => 'roles.delete', 'display_name' => 'Delete Role', 'description' => 'Delete roles', 'category' => 'roles'],
            ['name' => 'roles.assign', 'display_name' => 'Assign Role', 'description' => 'Assign roles to users', 'category' => 'roles'],
            ['name' => 'roles.revoke', 'display_name' => 'Revoke Role', 'description' => 'Remove roles from users', 'category' => 'roles'],

            // Permission Management
            ['name' => 'permissions.view_any', 'display_name' => 'View All Permissions', 'description' => 'View all permissions', 'category' => 'permissions'],
            ['name' => 'permissions.view', 'display_name' => 'View Permission', 'description' => 'View permission details', 'category' => 'permissions'],
            ['name' => 'permissions.assign', 'display_name' => 'Assign Permission', 'description' => 'Assign permissions to roles/users', 'category' => 'permissions'],
            ['name' => 'permissions.revoke', 'display_name' => 'Revoke Permission', 'description' => 'Remove permissions from roles/users', 'category' => 'permissions'],

            // Item Management
            ['name' => 'items.view_any', 'display_name' => 'View All Items', 'description' => 'View all items in inventory', 'category' => 'items'],
            ['name' => 'items.view', 'display_name' => 'View Item', 'description' => 'View individual item details', 'category' => 'items'],
            ['name' => 'items.create', 'display_name' => 'Create Item', 'description' => 'Add new items to inventory', 'category' => 'items'],
            ['name' => 'items.update', 'display_name' => 'Update Item', 'description' => 'Edit item information', 'category' => 'items'],
            ['name' => 'items.delete', 'display_name' => 'Delete Item', 'description' => 'Soft delete items', 'category' => 'items'],
            ['name' => 'items.restore', 'display_name' => 'Restore Item', 'description' => 'Restore deleted items', 'category' => 'items'],
            ['name' => 'items.force_delete', 'display_name' => 'Force Delete Item', 'description' => 'Permanently delete items', 'category' => 'items'],
            ['name' => 'items.export', 'display_name' => 'Export Items', 'description' => 'Export item lists to Excel/CSV', 'category' => 'items'],
            ['name' => 'items.import', 'display_name' => 'Import Items', 'description' => 'Bulk import items from Excel/CSV', 'category' => 'items'],
            ['name' => 'items.view_cost', 'display_name' => 'View Item Cost', 'description' => 'View purchase costs/financial data', 'category' => 'items'],
            ['name' => 'items.update_cost', 'display_name' => 'Update Item Cost', 'description' => 'Update financial information', 'category' => 'items'],
            ['name' => 'items.view_history', 'display_name' => 'View Item History', 'description' => 'View complete item history', 'category' => 'items'],
            ['name' => 'items.generate_qr', 'display_name' => 'Generate QR Code', 'description' => 'Generate QR codes for items', 'category' => 'items'],
            ['name' => 'items.print_qr', 'display_name' => 'Print QR Code', 'description' => 'Print QR codes', 'category' => 'items'],
            ['name' => 'items.bulk_generate_qr', 'display_name' => 'Bulk Generate QR', 'description' => 'Bulk generate QR codes', 'category' => 'items'],

            // Categories
            ['name' => 'categories.view_any', 'display_name' => 'View All Categories', 'description' => 'View all categories', 'category' => 'categories'],
            ['name' => 'categories.view', 'display_name' => 'View Category', 'description' => 'View category details', 'category' => 'categories'],
            ['name' => 'categories.create', 'display_name' => 'Create Category', 'description' => 'Create new categories', 'category' => 'categories'],
            ['name' => 'categories.update', 'display_name' => 'Update Category', 'description' => 'Update categories', 'category' => 'categories'],
            ['name' => 'categories.delete', 'display_name' => 'Delete Category', 'description' => 'Delete categories', 'category' => 'categories'],

            // Locations
            ['name' => 'locations.view_any', 'display_name' => 'View All Locations', 'description' => 'View all locations', 'category' => 'locations'],
            ['name' => 'locations.view', 'display_name' => 'View Location', 'description' => 'View location details', 'category' => 'locations'],
            ['name' => 'locations.create', 'display_name' => 'Create Location', 'description' => 'Create new locations', 'category' => 'locations'],
            ['name' => 'locations.update', 'display_name' => 'Update Location', 'description' => 'Update locations', 'category' => 'locations'],
            ['name' => 'locations.delete', 'display_name' => 'Delete Location', 'description' => 'Delete locations', 'category' => 'locations'],

            // Assignments
            ['name' => 'assignments.view_any', 'display_name' => 'View All Assignments', 'description' => 'View all assignments', 'category' => 'assignments'],
            ['name' => 'assignments.view', 'display_name' => 'View Assignment', 'description' => 'View specific assignment details', 'category' => 'assignments'],
            ['name' => 'assignments.view_own', 'display_name' => 'View Own Assignments', 'description' => 'View only own assignments', 'category' => 'assignments'],
            ['name' => 'assignments.create', 'display_name' => 'Create Assignment', 'description' => 'Assign items to users', 'category' => 'assignments'],
            ['name' => 'assignments.update', 'display_name' => 'Update Assignment', 'description' => 'Update assignment details', 'category' => 'assignments'],
            ['name' => 'assignments.cancel', 'display_name' => 'Cancel Assignment', 'description' => 'Cancel assignments', 'category' => 'assignments'],
            ['name' => 'assignments.assign_to_self', 'display_name' => 'Assign to Self', 'description' => 'Assign items to themselves', 'category' => 'assignments'],
            ['name' => 'assignments.assign_to_others', 'display_name' => 'Assign to Others', 'description' => 'Assign items to other users', 'category' => 'assignments'],
            ['name' => 'assignments.view_user_items', 'display_name' => 'View User Items', 'description' => 'View items assigned to specific user', 'category' => 'assignments'],
            ['name' => 'assignments.approve', 'display_name' => 'Approve Assignment', 'description' => 'Approve assignment requests', 'category' => 'assignments'],
            ['name' => 'assignments.reject', 'display_name' => 'Reject Assignment', 'description' => 'Reject assignment requests', 'category' => 'assignments'],

            // Returns
            ['name' => 'returns.view_any', 'display_name' => 'View All Returns', 'description' => 'View all return records', 'category' => 'returns'],
            ['name' => 'returns.view', 'display_name' => 'View Return', 'description' => 'View specific return details', 'category' => 'returns'],
            ['name' => 'returns.create', 'display_name' => 'Create Return', 'description' => 'Process item returns', 'category' => 'returns'],
            ['name' => 'returns.update', 'display_name' => 'Update Return', 'description' => 'Update return information', 'category' => 'returns'],
            ['name' => 'returns.approve', 'display_name' => 'Approve Return', 'description' => 'Approve return requests', 'category' => 'returns'],
            ['name' => 'returns.reject', 'display_name' => 'Reject Return', 'description' => 'Reject return requests', 'category' => 'returns'],
            ['name' => 'returns.mark_returned', 'display_name' => 'Mark Returned', 'description' => 'Mark items as returned', 'category' => 'returns'],
            ['name' => 'returns.inspect', 'display_name' => 'Inspect Return', 'description' => 'Perform return inspection', 'category' => 'returns'],
            ['name' => 'returns.note_damage', 'display_name' => 'Note Damage', 'description' => 'Document damage on return', 'category' => 'returns'],

            // Maintenance
            ['name' => 'maintenance.view_any', 'display_name' => 'View All Maintenance', 'description' => 'View all maintenance records', 'category' => 'maintenance'],
            ['name' => 'maintenance.view', 'display_name' => 'View Maintenance', 'description' => 'View specific maintenance record', 'category' => 'maintenance'],
            ['name' => 'maintenance.create', 'display_name' => 'Create Maintenance', 'description' => 'Create maintenance requests', 'category' => 'maintenance'],
            ['name' => 'maintenance.update', 'display_name' => 'Update Maintenance', 'description' => 'Update maintenance records', 'category' => 'maintenance'],
            ['name' => 'maintenance.delete', 'display_name' => 'Delete Maintenance', 'description' => 'Delete maintenance records', 'category' => 'maintenance'],
            ['name' => 'maintenance.schedule', 'display_name' => 'Schedule Maintenance', 'description' => 'Schedule maintenance', 'category' => 'maintenance'],
            ['name' => 'maintenance.complete', 'display_name' => 'Complete Maintenance', 'description' => 'Mark maintenance as completed', 'category' => 'maintenance'],
            ['name' => 'maintenance.assign', 'display_name' => 'Assign Maintenance', 'description' => 'Assign maintenance tasks', 'category' => 'maintenance'],
            ['name' => 'maintenance.view_costs', 'display_name' => 'View Maintenance Costs', 'description' => 'View maintenance costs', 'category' => 'maintenance'],
            ['name' => 'maintenance.approve', 'display_name' => 'Approve Maintenance', 'description' => 'Approve maintenance requests', 'category' => 'maintenance'],
            ['name' => 'maintenance.reject', 'display_name' => 'Reject Maintenance', 'description' => 'Reject maintenance requests', 'category' => 'maintenance'],

            // Disposals
            ['name' => 'disposals.view_any', 'display_name' => 'View All Disposals', 'description' => 'View all disposal records', 'category' => 'disposals'],
            ['name' => 'disposals.view', 'display_name' => 'View Disposal', 'description' => 'View specific disposal', 'category' => 'disposals'],
            ['name' => 'disposals.create', 'display_name' => 'Create Disposal', 'description' => 'Mark items for disposal', 'category' => 'disposals'],
            ['name' => 'disposals.update', 'display_name' => 'Update Disposal', 'description' => 'Update disposal information', 'category' => 'disposals'],
            ['name' => 'disposals.delete', 'display_name' => 'Delete Disposal', 'description' => 'Remove disposal records', 'category' => 'disposals'],
            ['name' => 'disposals.approve', 'display_name' => 'Approve Disposal', 'description' => 'Approve disposal requests', 'category' => 'disposals'],
            ['name' => 'disposals.reject', 'display_name' => 'Reject Disposal', 'description' => 'Reject disposal requests', 'category' => 'disposals'],
            ['name' => 'disposals.execute', 'display_name' => 'Execute Disposal', 'description' => 'Execute approved disposals', 'category' => 'disposals'],
            ['name' => 'disposals.view_reasons', 'display_name' => 'View Disposal Reasons', 'description' => 'View disposal reasons', 'category' => 'disposals'],

            // Reports
            ['name' => 'reports.view', 'display_name' => 'View Reports', 'description' => 'Access reports section', 'category' => 'reports'],
            ['name' => 'reports.user_assignments', 'display_name' => 'User Assignment Reports', 'description' => 'View user assignment reports', 'category' => 'reports'],
            ['name' => 'reports.item_history', 'display_name' => 'Item History Reports', 'description' => 'View item history reports', 'category' => 'reports'],
            ['name' => 'reports.inventory_summary', 'display_name' => 'Inventory Summary', 'description' => 'View inventory summary', 'category' => 'reports'],
            ['name' => 'reports.financial', 'display_name' => 'Financial Reports', 'description' => 'View financial reports', 'category' => 'reports'],
            ['name' => 'reports.disposal', 'display_name' => 'Disposal Reports', 'description' => 'View disposal reports', 'category' => 'reports'],
            ['name' => 'reports.maintenance', 'display_name' => 'Maintenance Reports', 'description' => 'View maintenance reports', 'category' => 'reports'],
            ['name' => 'reports.activity_logs', 'display_name' => 'Activity Log Reports', 'description' => 'View activity log reports', 'category' => 'reports'],
            ['name' => 'reports.utilization', 'display_name' => 'Utilization Reports', 'description' => 'View item utilization reports', 'category' => 'reports'],
            ['name' => 'reports.export', 'display_name' => 'Export Reports', 'description' => 'Export reports to Excel/PDF', 'category' => 'reports'],
            ['name' => 'reports.schedule', 'display_name' => 'Schedule Reports', 'description' => 'Schedule automated reports', 'category' => 'reports'],
            ['name' => 'reports.share', 'display_name' => 'Share Reports', 'description' => 'Share reports with others', 'category' => 'reports'],

            // Activity Logs
            ['name' => 'activity_logs.view_any', 'display_name' => 'View All Activity Logs', 'description' => 'View all activity logs', 'category' => 'activity_logs'],
            ['name' => 'activity_logs.view', 'display_name' => 'View Activity Log', 'description' => 'View specific log entries', 'category' => 'activity_logs'],
            ['name' => 'activity_logs.view_own', 'display_name' => 'View Own Activity', 'description' => 'View only own activity', 'category' => 'activity_logs'],
            ['name' => 'activity_logs.export', 'display_name' => 'Export Activity Logs', 'description' => 'Export activity logs', 'category' => 'activity_logs'],
            ['name' => 'activity_logs.delete', 'display_name' => 'Delete Activity Logs', 'description' => 'Delete old logs', 'category' => 'activity_logs'],
            ['name' => 'activity_logs.view_user_logs', 'display_name' => 'View User Logs', 'description' => 'View logs for specific user', 'category' => 'activity_logs'],
            ['name' => 'activity_logs.view_item_logs', 'display_name' => 'View Item Logs', 'description' => 'View logs for specific item', 'category' => 'activity_logs'],

            // Dashboard
            ['name' => 'dashboard.view', 'display_name' => 'View Dashboard', 'description' => 'Access main dashboard', 'category' => 'dashboard'],
            ['name' => 'dashboard.view_stats', 'display_name' => 'View Statistics', 'description' => 'View statistics', 'category' => 'dashboard'],
            ['name' => 'dashboard.view_charts', 'display_name' => 'View Charts', 'description' => 'View analytics charts', 'category' => 'dashboard'],
            ['name' => 'dashboard.view_alerts', 'display_name' => 'View Alerts', 'description' => 'View system alerts', 'category' => 'dashboard'],
            ['name' => 'dashboard.view_pending', 'display_name' => 'View Pending', 'description' => 'View pending actions', 'category' => 'dashboard'],

            // Analytics
            ['name' => 'analytics.view', 'display_name' => 'View Analytics', 'description' => 'Access analytics section', 'category' => 'analytics'],
            ['name' => 'analytics.advanced', 'display_name' => 'Advanced Analytics', 'description' => 'Access advanced analytics', 'category' => 'analytics'],
            ['name' => 'analytics.export', 'display_name' => 'Export Analytics', 'description' => 'Export analytics data', 'category' => 'analytics'],

            // Settings
            ['name' => 'settings.view', 'display_name' => 'View Settings', 'description' => 'View system settings', 'category' => 'settings'],
            ['name' => 'settings.update', 'display_name' => 'Update Settings', 'description' => 'Update system settings', 'category' => 'settings'],
            ['name' => 'settings.view_security', 'display_name' => 'View Security Settings', 'description' => 'View security settings', 'category' => 'settings'],
            ['name' => 'settings.update_security', 'display_name' => 'Update Security Settings', 'description' => 'Update security settings', 'category' => 'settings'],

            // System
            ['name' => 'system.backup', 'display_name' => 'System Backup', 'description' => 'Create system backups', 'category' => 'system'],
            ['name' => 'system.restore', 'display_name' => 'System Restore', 'description' => 'Restore from backups', 'category' => 'system'],
            ['name' => 'system.view_logs', 'display_name' => 'View System Logs', 'description' => 'View system logs', 'category' => 'system'],
            ['name' => 'system.maintenance', 'display_name' => 'System Maintenance', 'description' => 'Put system in maintenance mode', 'category' => 'system'],

            // Notifications
            ['name' => 'notifications.view', 'display_name' => 'View Notifications', 'description' => 'View notifications', 'category' => 'notifications'],
            ['name' => 'notifications.create', 'display_name' => 'Create Notifications', 'description' => 'Send notifications to users', 'category' => 'notifications'],
            ['name' => 'notifications.delete', 'display_name' => 'Delete Notifications', 'description' => 'Delete notifications', 'category' => 'notifications'],
            ['name' => 'notifications.configure', 'display_name' => 'Configure Notifications', 'description' => 'Configure notification settings', 'category' => 'notifications'],

            // Requests
            ['name' => 'requests.view_any', 'display_name' => 'View All Requests', 'description' => 'View all requests', 'category' => 'requests'],
            ['name' => 'requests.view', 'display_name' => 'View Request', 'description' => 'View specific request', 'category' => 'requests'],
            ['name' => 'requests.create', 'display_name' => 'Create Request', 'description' => 'Create requests', 'category' => 'requests'],
            ['name' => 'requests.update', 'display_name' => 'Update Request', 'description' => 'Update requests', 'category' => 'requests'],
            ['name' => 'requests.delete', 'display_name' => 'Delete Request', 'description' => 'Delete requests', 'category' => 'requests'],
            ['name' => 'requests.approve', 'display_name' => 'Approve Request', 'description' => 'Approve requests', 'category' => 'requests'],
            ['name' => 'requests.reject', 'display_name' => 'Reject Request', 'description' => 'Reject requests', 'category' => 'requests'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        $this->command->info('Permissions seeded successfully!');
    }
}
