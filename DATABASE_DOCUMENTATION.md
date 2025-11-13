# Database Documentation

## Overview
This document provides a comprehensive overview of the database structure for the Equipment Property Management System (EPMS).

## Database Tables Summary

| Table | Model | Migration | Seeder | Status |
|-------|-------|-----------|--------|--------|
| users | User | ✅ | ✅ | Complete |
| permissions | Permission | ✅ | ✅ | Complete |
| roles | Role | ✅ | ✅ | Complete |
| role_permissions | - | ✅ | ✅ | Complete |
| user_roles | - | ✅ | ✅ | Complete |
| user_permissions | - | ✅ | ✅ | Complete |
| categories | Category | ✅ | ✅ | Complete |
| locations | Location | ✅ | ✅ | Complete |
| items | Item | ✅ | ⚠️ | Migration Complete |
| assignments | Assignment | ✅ | ⚠️ | Migration Complete |
| maintenance_requests | MaintenanceRequest | ✅ | ⚠️ | Migration Complete |
| maintenance_records | MaintenanceRecord | ✅ | ⚠️ | Migration Complete |
| disposal_records | DisposalRecord | ✅ | ⚠️ | Migration Complete |
| activity_logs | ActivityLog | ✅ | ⚠️ | Migration Complete |
| requests | Request | ✅ | ⚠️ | Migration Complete |

## Models & Relationships

### 1. User Model
**File:** `app/Models/User.php`

**Fillable Fields:**
- name, email, password, department, position, employee_id, phone, avatar, status

**Relationships:**
- `belongsToMany`: Role (via user_roles)
- `belongsToMany`: Permission (via user_permissions)
- `hasMany`: Assignment, ActivityLog, MaintenanceRequest, MaintenanceRecord, DisposalRecord, Request

**Key Methods:**
- `hasPermission(string $permission): bool`
- `hasRole(string $role): bool`
- `assignRole(Role $role): void`
- `givePermission(Permission $permission): void`

---

### 2. Permission Model
**File:** `app/Models/Permission.php`

**Fillable Fields:**
- name (unique), display_name, description, category

**Relationships:**
- `belongsToMany`: Role (via role_permissions)
- `belongsToMany`: User (via user_permissions)

**Categories:**
- users, roles, permissions, items, categories, locations, assignments
- returns, maintenance, disposals, reports, activity_logs, dashboard
- analytics, settings, system, notifications, requests

---

### 3. Role Model
**File:** `app/Models/Role.php`

**Fillable Fields:**
- name (unique), display_name, description, is_system_role

**Relationships:**
- `belongsToMany`: Permission (via role_permissions)
- `belongsToMany`: User (via user_roles)

**Predefined Roles:**
1. Super Administrator - ALL permissions
2. Property Administrator - Manage all property operations
3. Property Manager - Day-to-day operations
4. Inventory Clerk - Basic inventory tasks
5. Assignment Officer - Handle assignments and returns
6. Maintenance Coordinator - Manage maintenance
7. Auditor - View-only access
8. Department Head - View and request for department
9. Staff User - View own items
10. Report Viewer - View reports only

---

### 4. Category Model
**File:** `app/Models/Category.php`

**Fillable Fields:**
- name, description, parent_id

**Relationships:**
- `belongsTo`: Category (parent)
- `hasMany`: Category (children)
- `hasMany`: Item

**Soft Deletes:** Yes

---

### 5. Location Model
**File:** `app/Models/Location.php`

**Fillable Fields:**
- name, code (unique), description, building, floor, room, parent_id

**Relationships:**
- `belongsTo`: Location (parent)
- `hasMany`: Location (children)
- `hasMany`: Item

**Soft Deletes:** Yes

---

### 6. Item Model
**File:** `app/Models/Item.php`

**Fillable Fields:**
- code (unique), qr_code (unique), name, description, category_id, location_id
- serial_number, model, manufacturer, purchase_date, purchase_cost, current_value
- condition (enum), status (enum), warranty_expiry, specifications (JSON), notes, image_path

**Enums:**
- condition: excellent, good, fair, poor, damaged
- status: available, assigned, maintenance, disposed

**Relationships:**
- `belongsTo`: Category, Location
- `hasMany`: Assignment, MaintenanceRequest, MaintenanceRecord, ActivityLog
- `hasOne`: DisposalRecord, currentAssignment

**Key Methods:**
- `isAvailable(): bool`
- `isAssigned(): bool`
- `hasPendingMaintenance(): bool`

**Soft Deletes:** Yes

---

### 7. Assignment Model
**File:** `app/Models/Assignment.php`

**Fillable Fields:**
- item_id, user_id, assigned_by, assigned_at, expected_return_date, actual_return_date
- status (enum), purpose, notes, return_condition (enum), return_notes

**Enums:**
- status: active, returned, overdue, cancelled
- return_condition: excellent, good, fair, poor, damaged

**Relationships:**
- `belongsTo`: Item, User, assignedBy (User)

**Key Methods:**
- `isActive(): bool`
- `isOverdue(): bool`

---

### 8. MaintenanceRequest Model
**File:** `app/Models/MaintenanceRequest.php`

**Purpose:** Handle REQUEST/APPROVAL workflow for maintenance

**Fillable Fields:**
- item_id, requested_by, reviewed_by, title, description
- priority (enum), type (enum), status (enum)
- urgency_reason, preferred_date, estimated_cost
- approval_notes, rejection_reason, requested_at, reviewed_at, maintenance_record_id

**Enums:**
- priority: low, medium, high, urgent
- type: preventive, corrective, inspection, emergency
- status: pending, approved, rejected, in_progress, completed, cancelled

**Relationships:**
- `belongsTo`: Item, requestedBy (User), reviewedBy (User), MaintenanceRecord

**Key Methods:**
- `approve(User $reviewer, ?string $notes): void`
- `reject(User $reviewer, string $reason): void`

**Soft Deletes:** Yes

---

### 9. MaintenanceRecord Model
**File:** `app/Models/MaintenanceRecord.php`

**Purpose:** Record ACTUAL maintenance work performed

**Fillable Fields:**
- item_id, maintenance_request_id, requested_by, assigned_to
- type (enum), status (enum), priority (enum), description
- scheduled_date, started_at, completed_at, actual_cost, labor_hours
- parts_used (JSON), work_performed, technician_notes, outcome (enum)
- next_maintenance_date, attachments (JSON)

**Enums:**
- type: preventive, corrective, inspection
- status: scheduled, in_progress, completed, cancelled
- priority: low, medium, high, urgent
- outcome: successful, partial, failed

**Relationships:**
- `belongsTo`: Item, MaintenanceRequest, requestedBy (User), assignedTo (User)

**Key Methods:**
- `complete(string $workPerformed, string $outcome, array $partsUsed): void`

---

### 10. DisposalRecord Model
**File:** `app/Models/DisposalRecord.php`

**Fillable Fields:**
- item_id, requested_by, approved_by, executed_by
- reason, method (enum), status (enum)
- requested_date, approved_date, executed_date
- sale_amount, notes, documentation (JSON)

**Enums:**
- method: sale, donation, recycle, destroy
- status: pending, approved, rejected, executed

**Relationships:**
- `belongsTo`: Item, requestedBy (User), approvedBy (User), executedBy (User)

---

### 11. ActivityLog Model
**File:** `app/Models/ActivityLog.php`

**Fillable Fields:**
- user_id, item_id, action, description
- model_type, model_id (polymorphic)
- old_values (JSON), new_values (JSON)
- ip_address, user_agent

**Relationships:**
- `belongsTo`: User, Item
- `morphTo`: model (polymorphic)

---

### 12. Request Model
**File:** `app/Models/Request.php`

**Purpose:** General request/approval workflow

**Fillable Fields:**
- user_id, item_id, approved_by, type (enum), status (enum)
- requested_date, approved_date, reason, notes, response_notes

**Enums:**
- type: item_assignment, item_return, disposal, maintenance
- status: pending, approved, rejected, cancelled

**Relationships:**
- `belongsTo`: User, Item, approvedBy (User)

---

## Entity Relationship Diagram

```
User
├── has many Assignments (as user)
├── has many Assignments (as assigner)
├── has many MaintenanceRequests (as requester)
├── has many MaintenanceRequests (as reviewer)
├── has many MaintenanceRecords (as requester)
├── has many MaintenanceRecords (as assignee)
├── has many DisposalRecords (as requester/approver/executor)
├── has many Requests
├── has many ActivityLogs
├── belongs to many Roles
└── belongs to many Permissions

Item
├── belongs to Category
├── belongs to Location
├── has many Assignments
├── has one current Assignment
├── has many MaintenanceRequests
├── has many MaintenanceRecords
├── has one DisposalRecord
└── has many ActivityLogs

Assignment
├── belongs to Item
├── belongs to User
└── belongs to User (assigned_by)

MaintenanceRequest (Approval Workflow)
├── belongs to Item
├── belongs to User (requested_by)
├── belongs to User (reviewed_by)
└── belongs to MaintenanceRecord

MaintenanceRecord (Actual Work)
├── belongs to Item
├── belongs to MaintenanceRequest
├── belongs to User (requested_by)
└── belongs to User (assigned_to)

DisposalRecord
├── belongs to Item
├── belongs to User (requested_by)
├── belongs to User (approved_by)
└── belongs to User (executed_by)
```

## Seeders

### Execution Order
1. **PermissionSeeder** - Creates 130+ permissions across all categories
2. **RoleSeeder** - Creates 10 predefined roles with permission assignments
3. **CategorySeeder** - Creates equipment categories with hierarchy
4. **LocationSeeder** - Creates location hierarchy (Building > Floor > Room)
5. **DatabaseSeeder** - Orchestrates all seeders and creates test users

### Test Users Created
| Email | Password | Role | Status |
|-------|----------|------|--------|
| admin@example.com | password | Super Administrator | Active |
| manager@example.com | password | Property Manager | Active |
| staff@example.com | password | Staff User | Active |

## Running Migrations & Seeders

```bash
# Run migrations
php artisan migrate

# Run seeders
php artisan db:seed

# Or run both together
php artisan migrate:fresh --seed
```

## Permission Categories

1. **users** - User management (7 permissions)
2. **roles** - Role management (7 permissions)
3. **permissions** - Permission management (4 permissions)
4. **items** - Item/inventory management (15 permissions)
5. **categories** - Category management (5 permissions)
6. **locations** - Location management (5 permissions)
7. **assignments** - Assignment management (11 permissions)
8. **returns** - Return management (9 permissions)
9. **maintenance** - Maintenance management (11 permissions)
10. **disposals** - Disposal management (9 permissions)
11. **reports** - Reporting (12 permissions)
12. **activity_logs** - Activity logging (7 permissions)
13. **dashboard** - Dashboard access (5 permissions)
14. **analytics** - Analytics (3 permissions)
15. **settings** - System settings (4 permissions)
16. **system** - System management (4 permissions)
17. **notifications** - Notifications (4 permissions)
18. **requests** - Request workflow (7 permissions)

**Total:** 130+ granular permissions

## Indexes

### Performance Indexes Added:
- **users**: status, department
- **permissions**: category
- **roles**: is_system_role
- **items**: code, qr_code, status, category_id+status, location_id+status
- **assignments**: status, item_id+status, user_id+status
- **maintenance_requests**: status, priority, item_id+status
- **maintenance_records**: status, item_id+status, scheduled_date
- **disposal_records**: status, item_id+status
- **activity_logs**: user_id, item_id, action, model_type+model_id, created_at
- **requests**: status, type, user_id+status

## JSON Fields

### Items
- **specifications**: Technical specifications and features

### MaintenanceRecord
- **parts_used**: Array of parts used in maintenance
- **attachments**: File paths for maintenance documentation

### DisposalRecord
- **documentation**: Supporting documents for disposal

### ActivityLog
- **old_values**: State before change
- **new_values**: State after change

## Next Steps

1. ✅ All core migrations created
2. ✅ Permission and Role seeders complete
3. ✅ Category and Location seeders complete
4. ⚠️ Item seeder needs to be created (sample items)
5. ⚠️ Assignment seeder optional (for demo data)
6. ⚠️ Maintenance seeders optional (for demo data)

## Notes

- All user-facing tables use soft deletes
- Timestamps are automatic on all tables
- Foreign keys use cascading deletes where appropriate
- Pivot tables include timestamps for audit trail
- Enum fields provide data integrity
- Indexes optimize common query patterns
