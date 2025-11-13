# EPM System - Database Schema Documentation

## Overview
This document provides a comprehensive overview of the database schema for the Equipment Property Management System (EPMS), a QR Code-based item tracking system with granular permissions.

---

## Database Models & Relationships

### 1. **User** (Authentication & Authorization)
**Purpose**: Core user model with role-based access control

**Fields**:
- `id`, `name`, `email`, `password`
- `department`, `position`, `employee_id`
- `phone`, `avatar`, `status` (active, inactive, suspended)
- `email_verified_at`, `two_factor_secret`, `two_factor_recovery_codes`
- `remember_token`, `two_factor_confirmed_at`
- Soft deletes: `deleted_at`

**Relationships**:
- `belongsToMany` → Role (via `user_roles`)
- `belongsToMany` → Permission (via `user_permissions`)
- `hasMany` → Assignment
- `hasMany` → Assignment (as `assigned_by`)
- `hasMany` → MaintenanceRequest (as `requested_by`)
- `hasMany` → MaintenanceRequest (as `reviewed_by`)
- `hasMany` → MaintenanceRecord (as `requested_by`)
- `hasMany` → MaintenanceRecord (as `assigned_to`)
- `hasMany` → DisposalRecord (as `requested_by`, `approved_by`, `executed_by`)
- `hasMany` → Request
- `hasMany` → ActivityLog

**Key Methods**:
- `hasPermission(string)`, `hasRole(string)`, `hasAnyPermission(array)`, `hasAllPermissions(array)`
- `assignRole(Role)`, `removeRole(Role)`, `givePermission(Permission)`, `revokePermission(Permission)`
- `isActive()`, `isSuperAdmin()`

**Scopes**:
- `active()`, `withRolesAndPermissions()`, `withActiveAssignments()`

**Performance**: Implements permission/role caching to prevent N+1 queries

---

### 2. **Role** (Role-Based Access Control)
**Purpose**: Define user roles with associated permissions

**Fields**:
- `id`, `name`, `display_name`, `description`
- `is_system_role` (boolean - for predefined roles)

**Relationships**:
- `belongsToMany` → Permission (via `role_permissions`)
- `belongsToMany` → User (via `user_roles`)

**Key Methods**:
- `givePermission(Permission)`, `revokePermission(Permission)`, `hasPermission(string)`

**Predefined Roles**:
- Super Administrator, Property Administrator, Property Manager
- Inventory Clerk, Assignment Officer, Maintenance Coordinator
- Auditor, Department Head, Staff User, Report Viewer

---

### 3. **Permission** (Granular Access Control)
**Purpose**: Granular permissions that can be assigned to roles or users directly

**Fields**:
- `id`, `name`, `display_name`, `description`
- `category` (users, items, assignments, reports, maintenance, etc.)

**Relationships**:
- `belongsToMany` → Role (via `role_permissions`)
- `belongsToMany` → User (via `user_permissions`)

**Permission Categories**:
- User Management: `users.*`, `roles.*`, `permissions.*`
- Inventory: `items.*`, `categories.*`, `locations.*`
- Assignments: `assignments.*`, `returns.*`
- Maintenance: `maintenance.*`
- Disposal: `disposals.*`
- Reports: `reports.*`
- Activity Logs: `activity_logs.*`
- System: `dashboard.*`, `settings.*`, `system.*`

---

### 4. **Item** (Core Inventory)
**Purpose**: Physical items/equipment being tracked

**Fields**:
- `id`, `code` (unique), `qr_code` (unique)
- `name`, `description`
- `category_id`, `location_id`
- `serial_number`, `model`, `manufacturer`
- `purchase_date`, `purchase_cost`, `current_value`
- `condition` (excellent, good, fair, poor, damaged)
- `status` (available, assigned, maintenance, disposed)
- `warranty_expiry`, `specifications` (JSON), `notes`, `image_path`
- Soft deletes: `deleted_at`

**Relationships**:
- `belongsTo` → Category
- `belongsTo` → Location
- `hasMany` → Assignment
- `hasOne` → Assignment (current, where status='active')
- `hasMany` → MaintenanceRequest
- `hasMany` → MaintenanceRecord
- `hasOne` → DisposalRecord
- `hasMany` → ActivityLog

**Key Methods**:
- `isAvailable()`, `isAssigned()`, `hasPendingMaintenance()`, `isUnderMaintenance()`

**Attributes**:
- `maintenance_count` - Total maintenance history count

---

### 5. **Category** (Item Classification)
**Purpose**: Hierarchical categorization of items

**Fields**:
- `id`, `name`, `description`
- `parent_id` (self-referencing for nested categories)
- Soft deletes: `deleted_at`

**Relationships**:
- `belongsTo` → Category (parent)
- `hasMany` → Category (children)
- `hasMany` → Item

**Example Structure**:
```
Electronics
├── Computers
│   ├── Laptops
│   └── Desktops
└── Peripherals
    ├── Monitors
    └── Keyboards
```

---

### 6. **Location** (Physical Location Tracking)
**Purpose**: Track where items are physically located

**Fields**:
- `id`, `name`, `code`, `description`
- `building`, `floor`, `room`
- `parent_id` (self-referencing for nested locations)
- Soft deletes: `deleted_at`

**Relationships**:
- `belongsTo` → Location (parent)
- `hasMany` → Location (children)
- `hasMany` → Item

**Example Structure**:
```
Main Building
├── Floor 1
│   ├── Room 101
│   └── Room 102
└── Floor 2
    └── IT Department
```

---

### 7. **Assignment** (Item Assignment/Borrowing)
**Purpose**: Track who has which items

**Fields**:
- `id`, `item_id`, `user_id`, `assigned_by`
- `assigned_at`, `expected_return_date`, `actual_return_date`
- `status` (active, returned, overdue, cancelled)
- `purpose`, `notes`
- `return_condition`, `return_notes`

**Relationships**:
- `belongsTo` → Item
- `belongsTo` → User (assignee)
- `belongsTo` → User (assigned_by)

**Key Methods**:
- `isActive()`, `isOverdue()`

**Business Logic**:
- When assignment is created → Item status changes to 'assigned'
- When returned → Item status changes to 'available'
- System checks for overdue assignments daily

---

### 8. **MaintenanceRequest** (Maintenance Approval Workflow)
**Purpose**: Request and approval workflow for maintenance work

**Fields**:
- `id`, `item_id`, `requested_by`, `reviewed_by`
- `title`, `description`
- `priority` (low, medium, high, urgent)
- `type` (preventive, corrective, inspection, emergency)
- `status` (pending, approved, rejected, in_progress, completed, cancelled)
- `urgency_reason`, `preferred_date`, `estimated_cost`
- `approval_notes`, `rejection_reason`
- `requested_at`, `reviewed_at`
- `maintenance_record_id` (links to actual work)
- Soft deletes: `deleted_at`

**Relationships**:
- `belongsTo` → Item
- `belongsTo` → User (requested_by)
- `belongsTo` → User (reviewed_by)
- `belongsTo` → MaintenanceRecord

**Key Methods**:
- `isPending()`, `isApproved()`, `approve(User, notes)`, `reject(User, reason)`

**Scopes**:
- `pending()`, `urgent()`, `approved()`

**Workflow**:
1. User creates request → `status='pending'`
2. Manager reviews → `approve()` or `reject()`
3. If approved → Creates `MaintenanceRecord`
4. Work completed → Request `status='completed'`

---

### 9. **MaintenanceRecord** (Actual Maintenance Work)
**Purpose**: Record actual maintenance work performed

**Fields**:
- `id`, `item_id`, `maintenance_request_id` (optional)
- `requested_by`, `assigned_to`
- `type` (preventive, corrective, inspection)
- `status` (scheduled, in_progress, completed, cancelled)
- `priority`, `description`
- `scheduled_date`, `started_at`, `completed_at`
- `actual_cost`, `labor_hours`, `parts_used` (JSON)
- `work_performed`, `technician_notes`, `outcome`
- `next_maintenance_date`, `attachments` (JSON)

**Relationships**:
- `belongsTo` → Item
- `belongsTo` → MaintenanceRequest
- `belongsTo` → User (requested_by)
- `belongsTo` → User (assigned_to)

**Key Methods**:
- `complete(workPerformed, outcome, partsUsed)`

**Scopes**:
- `completed()`, `inProgress()`

**Note**: Can be created directly (scheduled maintenance) or from approved MaintenanceRequest

---

### 10. **DisposalRecord** (Item Disposal)
**Purpose**: Track disposal/retirement of items

**Fields**:
- `id`, `item_id`
- `requested_by`, `approved_by`, `executed_by`
- `reason`, `method` (sale, donation, recycle, destroy)
- `status` (pending, approved, rejected, executed)
- `requested_date`, `approved_date`, `executed_date`
- `sale_amount`, `notes`, `documentation` (JSON)

**Relationships**:
- `belongsTo` → Item
- `belongsTo` → User (requested_by, approved_by, executed_by)

**Workflow**:
1. Request disposal → `status='pending'`
2. Approval → `status='approved'`
3. Execute disposal → `status='executed'`, Item `status='disposed'`

---

### 11. **Request** (General Request/Approval)
**Purpose**: Generic request system for various workflows

**Fields**:
- `id`, `user_id`, `approved_by`
- `type` (item_assignment, item_return, disposal, maintenance)
- `status` (pending, approved, rejected, cancelled)
- `item_id`, `requested_date`, `approved_date`
- `reason`, `notes`, `response_notes`

**Relationships**:
- `belongsTo` → User
- `belongsTo` → Item
- `belongsTo` → User (approved_by)

---

### 12. **ActivityLog** (Audit Trail)
**Purpose**: Comprehensive audit trail of all system actions

**Fields**:
- `id`, `user_id`, `item_id`
- `action` (created, updated, deleted, assigned, returned, etc.)
- `description`, `model_type`, `model_id`
- `old_values` (JSON), `new_values` (JSON)
- `ip_address`, `user_agent`

**Relationships**:
- `belongsTo` → User
- `belongsTo` → Item
- `morphTo` → model (polymorphic)

**Use Cases**:
- Track who changed what and when
- Compliance and auditing
- Debugging and troubleshooting
- Security monitoring

---

## Pivot Tables

### user_roles
- Links Users to Roles (many-to-many)
- Fields: `id`, `user_id`, `role_id`, `timestamps`

### role_permissions
- Links Roles to Permissions (many-to-many)
- Fields: `id`, `role_id`, `permission_id`, `timestamps`

### user_permissions
- Direct user permissions (many-to-many)
- Fields: `id`, `user_id`, `permission_id`, `timestamps`
- Used for: Granting specific permissions beyond role permissions

---

## Database Indexes

### Performance Optimization
```sql
-- Items
INDEX (code), UNIQUE INDEX (qr_code)
INDEX (status)
INDEX (category_id, status)
INDEX (location_id, status)

-- Assignments
INDEX (status)
INDEX (item_id, status)
INDEX (user_id, status)
INDEX (expected_return_date)

-- Maintenance Requests
INDEX (status)
INDEX (priority)
INDEX (item_id, status)

-- Maintenance Records
INDEX (status)
INDEX (item_id, status)
INDEX (scheduled_date)

-- Activity Logs
INDEX (user_id, created_at)
INDEX (item_id, created_at)
INDEX (action)
```

---

## Key Workflows

### 1. Item Assignment Workflow
```
User requests item → Assignment created (status='active')
                  → Item status='assigned'
                  → ActivityLog created

User returns item → Assignment status='returned'
                  → Item status='available'
                  → ActivityLog created
```

### 2. Maintenance Workflow (With Approval)
```
User creates MaintenanceRequest (status='pending')
                              ↓
Manager approves (status='approved')
                              ↓
Coordinator creates MaintenanceRecord (status='scheduled')
                              ↓
Technician starts work (status='in_progress')
                              ↓
Work completed (status='completed')
                              ↓
MaintenanceRequest status='completed'
```

### 3. Maintenance Workflow (Direct/Scheduled)
```
Coordinator creates MaintenanceRecord directly
                              ↓
Technician completes work
(No approval needed for scheduled preventive maintenance)
```

### 4. Disposal Workflow
```
User creates DisposalRecord (status='pending')
                          ↓
Manager approves (status='approved')
                          ↓
Admin executes (status='executed', item status='disposed')
```

---

## Permission System Architecture

### Permission Resolution Order
1. Check if user has direct permission (user_permissions table)
2. Check if any of user's roles have the permission (role_permissions table)
3. Return true if found in either, false otherwise

### Performance Optimization
- Permissions are cached per request to prevent N+1 queries
- Cache is cleared when roles/permissions are modified
- Use eager loading: `User::withRolesAndPermissions()->find($id)`

### Super Admin Bypass
```php
if ($user->isSuperAdmin()) {
    return true; // Bypass all permission checks
}
```

---

## Data Integrity Rules

### Foreign Key Constraints
- **CASCADE**: When parent is deleted, child records are deleted
  - User deletion → Cascades to assignments, requests, logs
  - Item deletion → Cascades to assignments, maintenance records

- **RESTRICT**: Prevents deletion if child records exist
  - Cannot delete Category if items exist in it
  - Cannot delete Location if items are stored there

- **SET NULL**: When parent is deleted, foreign key becomes null
  - Reviewed_by user deleted → reviewed_by becomes null
  - Maintains historical records without referential integrity errors

### Soft Deletes
Models with soft deletes: `User`, `Item`, `Category`, `Location`, `MaintenanceRequest`

Benefits:
- Data recovery capability
- Historical reporting accuracy
- Audit trail preservation

---

## JSON Fields

### Item.specifications
```json
{
  "cpu": "Intel i7",
  "ram": "16GB",
  "storage": "512GB SSD",
  "screen_size": "15.6 inches"
}
```

### MaintenanceRecord.parts_used
```json
[
  {"part": "Power Button", "quantity": 1, "cost": 50.00},
  {"part": "Thermal Paste", "quantity": 1, "cost": 15.00}
]
```

### ActivityLog.old_values / new_values
```json
{
  "old_values": {"status": "available", "location_id": 1},
  "new_values": {"status": "assigned", "location_id": 2}
}
```

---

## Migration Order

Execute migrations in this order to satisfy foreign key dependencies:

1. `create_users_table` (already exists)
2. `create_permissions_table`
3. `create_roles_table`
4. `create_role_permissions_table` (pivot)
5. `create_user_roles_table` (pivot)
6. `create_user_permissions_table` (pivot)
7. `create_categories_table`
8. `create_locations_table`
9. `create_items_table`
10. `create_assignments_table`
11. `create_maintenance_requests_table`
12. `create_maintenance_records_table`
13. `create_disposal_records_table`
14. `create_requests_table`
15. `create_activity_logs_table`

---

## Query Optimization Tips

### Prevent N+1 Queries
```php
// ❌ BAD - N+1 queries
$users = User::all();
foreach ($users as $user) {
    echo $user->roles->count(); // Query for each user
}

// ✅ GOOD - Eager loading
$users = User::with('roles')->get();
foreach ($users as $user) {
    echo $user->roles->count(); // No additional queries
}
```

### Use Scopes
```php
// ✅ Efficient filtered queries
$activeUsers = User::active()->withActiveAssignments()->get();
$urgentRequests = MaintenanceRequest::urgent()->pending()->get();
```

### Selective Field Loading
```php
// ✅ Only load needed fields
$items = Item::select('id', 'name', 'code', 'status')->get();
```

---

## Security Considerations

### 1. Mass Assignment Protection
All models use `$fillable` to prevent mass assignment vulnerabilities

### 2. Soft Deletes
Enables data recovery and prevents accidental data loss

### 3. Activity Logging
All critical actions are logged with user, IP, and timestamp

### 4. Permission Checks
Every controller action should verify permissions:
```php
if (!auth()->user()->hasPermission('items.create')) {
    abort(403);
}
```

### 5. Input Validation
Use Form Requests for validation before database operations

---

## Backup & Recovery

### Recommended Backup Strategy
- **Daily**: Full database backup
- **Hourly**: Incremental backup of activity_logs
- **Real-time**: Replication for critical data

### Important Tables for Backup
1. `users` - User accounts
2. `items` - Core inventory data
3. `assignments` - Current assignments
4. `activity_logs` - Audit trail
5. `permissions`, `roles` - Access control

---

## Database Statistics (Estimated)

| Model | Estimated Records (Year 1) | Growth Rate |
|-------|---------------------------|-------------|
| User | 50-200 | Low |
| Item | 1,000-10,000 | Medium |
| Assignment | 5,000-50,000 | High |
| MaintenanceRequest | 500-5,000 | Medium |
| MaintenanceRecord | 1,000-10,000 | Medium |
| ActivityLog | 50,000-500,000 | Very High |

### Storage Recommendations
- SSD storage for database
- Regular archiving of old activity logs (>1 year)
- Optimize images stored in `items.image_path`

---

## Future Enhancements

### Potential Additions
1. **Notifications Table** - For in-app notifications
2. **Reports Table** - Save generated reports
3. **Attachments Table** - Centralized file management
4. **Comments Table** - Comments on items/requests
5. **Tags Table** - Flexible tagging system
6. **Vendors Table** - Track suppliers/vendors
7. **Contracts Table** - Maintenance contracts

---

## Conclusion

This database schema provides:
- ✅ Comprehensive item tracking with QR codes
- ✅ Granular permission system (100+ permissions)
- ✅ Full audit trail via activity logs
- ✅ Flexible approval workflows
- ✅ Scalable architecture
- ✅ Data integrity and security
- ✅ Performance optimization

**Status**: Production Ready 🚀
