# Equipment Property Management System (EPMS) - System Flow & Architecture

## 📋 Table of Contents
1. [System Overview](#system-overview)
2. [Core Philosophy](#core-philosophy)
3. [Database Architecture](#database-architecture)
4. [Model Relationships](#model-relationships)
5. [System Workflows](#system-workflows)
6. [Permission System](#permission-system)
7. [QR Code Integration](#qr-code-integration)

---

## System Overview

**EPMS** is a comprehensive Equipment Property Management System designed for EARIST university to track, manage, and monitor physical assets using QR code technology with granular role-based permissions.

### Primary Features
- **QR Code-based Item Tracking**: Scan items to view details, assign, or perform actions
- **Granular Permission System**: 130+ permissions across 18 categories
- **Property Tag Alignment**: Matches Philippine government property management standards
- **Complete Audit Trail**: Track all actions via ActivityLog
- **Maintenance Workflow**: Request → Approval → Execution
- **Assignment Management**: Track who has what equipment

---

## Core Philosophy

### 1. **Property Tag Compliance**
Items follow real-world EARIST property tag format:
- **IAR Number**: Inspection and Acceptance Report ID
- **Fund Cluster**: Budget source tracking
- **Property Number**: Unique identifier
- **Accountable Person**: Person responsible for the item
- **Station**: Department/unit location

### 2. **Separation of Concerns**
```
Assignment ≠ Accountable Person
- Accountable Person: Official custodian (from property tag)
- Assignment: Temporary user (can be different from accountable person)

Station ≠ Physical Location
- Station: Organizational unit (MIS, HR, Registrar)
- Physical Location: Physical place (Building A, Room 101)
```

### 3. **Granular Permissions**
Instead of rigid roles, the system uses:
- **130+ specific permissions** (view_items, create_items, approve_maintenance, etc.)
- **Mix-and-match approach**: Create custom roles with needed permissions
- **Direct permission assignment**: Give users specific permissions outside their role

---

## Database Architecture

### Core Entities

```
┌─────────────────────────────────────────────────────────────┐
│                     CORE ENTITIES                           │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  User ←──────┐                                             │
│  │           │                                             │
│  │           │                                             │
│  │      Permission                                         │
│  │           │                                             │
│  │           │                                             │
│  └──────→ Role                                             │
│                                                             │
│  Category ←── Item ──→ Location                            │
│                │                                            │
│                ├──→ Assignment                             │
│                ├──→ MaintenanceRequest ──→ MaintenanceRecord│
│                ├──→ DisposalRecord                         │
│                └──→ ActivityLog                            │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

### Database Tables Overview

| Table | Purpose | Key Fields |
|-------|---------|------------|
| **users** | System users/staff | name, email, department, position |
| **permissions** | Atomic actions | name, category, description |
| **roles** | Permission groups | name, display_name, is_system_role |
| **categories** | Equipment types | name, parent_id (hierarchical) |
| **locations** | Physical/organizational places | name, building, floor, room, parent_id |
| **items** | Equipment/property | property_number, iar_number, accountable_person_id, station_id |
| **assignments** | Temporary item loans | item_id, user_id, expected_return_date |
| **maintenance_requests** | Maintenance approval workflow | item_id, requested_by, status |
| **maintenance_records** | Actual maintenance work | item_id, assigned_to, work_performed |
| **disposal_records** | Item disposal tracking | item_id, method, status |
| **requests** | General requests | type, item_id, status |
| **activity_logs** | Audit trail | user_id, item_id, action, old_values, new_values |

---

## Model Relationships

### 1. Item Model (Central Entity)

```php
Item {
    // Property Identification
    property_number: "2021-06-086-164"     // Unique ID
    iar_number: "164-2021-054"             // IAR #
    fund_cluster: "164"                    // Budget source
    qr_code: "generated_unique_code"       // QR tracking
    
    // Item Details
    description: "DESKTOP COMPUTER ACER VERITON M4665G"
    brand: "ACER"
    model: "VERITON M4665G"
    serial_number: "SN123456789"
    category_id: → Category
    
    // Acquisition
    acquisition_cost: 78710.00
    acquisition_date: "2021-06-04"
    supplier: "Vendor Name"
    
    // Accountability & Location
    accountable_person_id: → User         // Official custodian
    station_id: → Location                // Dept/Unit (MIS, HR)
    location_id: → Location               // Physical place (Bldg A, Rm 101)
    
    // Status
    condition: "excellent|good|fair|poor|damaged"
    status: "available|assigned|maintenance|disposed"
    
    // Relationships
    → category()              // BelongsTo Category
    → accountablePerson()     // BelongsTo User (accountable_person_id)
    → station()              // BelongsTo Location (station_id)
    → location()             // BelongsTo Location (location_id)
    → assignments()          // HasMany Assignment
    → currentAssignment()    // HasOne Assignment (active)
    → maintenanceRequests()  // HasMany MaintenanceRequest
    → maintenanceRecords()   // HasMany MaintenanceRecord
    → disposalRecord()       // HasOne DisposalRecord
    → activityLogs()         // HasMany ActivityLog
}
```

**Key Item Methods:**
```php
// Status Checks
isAvailable(): bool
isAssigned(): bool
isUnderMaintenance(): bool
hasPendingMaintenance(): bool

// Calculations
calculateDepreciation(): float
getAgeInYearsAttribute(): int
needsInventoryUpdate(): bool

// Helpers
getFullPropertyNumberAttribute(): string
```

### 2. User Model

```php
User {
    name, email, password
    department, position, employee_id
    
    // Relationships
    → roles()                           // BelongsToMany Role
    → permissions()                     // BelongsToMany Permission
    → assignments()                     // HasMany Assignment (items assigned TO this user)
    → assignmentsAsAssigner()          // HasMany Assignment (assigned BY this user)
    → accountableItems()               // HasMany Item (NEW - items user is accountable for)
    → activeAccountableItems()         // Filtered accountable items
    → maintenanceRequests()            // HasMany MaintenanceRequest (requested by)
    → maintenanceRequestsReviewed()    // HasMany MaintenanceRequest (reviewed by)
    → maintenanceRecordsRequested()    // HasMany MaintenanceRecord
    → maintenanceRecordsAssigned()     // HasMany MaintenanceRecord (assigned to)
    → disposalRecordsRequested()       // HasMany DisposalRecord
    → disposalRecordsApproved()        // HasMany DisposalRecord
    → disposalRecordsExecuted()        // HasMany DisposalRecord
    → activityLogs()                   // HasMany ActivityLog
}
```

**Permission Methods:**
```php
hasPermission(string $permission): bool
hasAnyPermission(array $permissions): bool
hasAllPermissions(array $permissions): bool
hasRole(string $role): bool
assignRole(Role $role): void
removeRole(Role $role): void
givePermission(Permission $permission): void
revokePermission(Permission $permission): void
```

### 3. Location Model

```php
Location {
    name: "MIS Department" or "Building A - Room 101"
    code: "MIS" or "A-101"
    building, floor, room
    parent_id: → Location (hierarchical)
    
    // Relationships
    → parent()           // BelongsTo Location
    → children()         // HasMany Location
    → items()           // HasMany Item (location_id - physical location)
    → stationItems()    // HasMany Item (station_id - organizational unit) NEW
    → allItems()        // Combined query
}
```

**Usage:**
```php
// Organizational Locations (Stations)
MIS Department
  └─ IT Support Unit
  └─ Systems Development Unit

// Physical Locations
Building A
  └─ 1st Floor
      └─ Room 101
      └─ Room 102
```

### 4. Assignment Model

```php
Assignment {
    item_id: → Item
    user_id: → User (who is using the item)
    assigned_by: → User (who assigned it)
    assigned_at: datetime
    expected_return_date: date
    actual_return_date: datetime
    status: "active|returned|overdue|cancelled"
    purpose: "Training", "Project Work", etc.
    
    // Relationships
    → item()         // BelongsTo Item
    → user()         // BelongsTo User
    → assignedBy()   // BelongsTo User
    
    // Methods
    isActive(): bool
    isOverdue(): bool
}
```

**Important Distinction:**
```
Assignment.user_id       ≠  Item.accountable_person_id
(Temporary borrower)         (Official custodian)

Example:
Item: Desktop Computer
  accountable_person_id: Dr. Jesus Paguigan (MIS Director - Official)
  Assignment.user_id: John Doe (Staff - Temporary user)
```

### 5. MaintenanceRequest Model

```php
MaintenanceRequest {
    // Request/Approval Workflow
    item_id: → Item
    requested_by: → User
    reviewed_by: → User
    title: "Keyboard Replacement"
    description: "Keys not working"
    priority: "low|medium|high|urgent"
    type: "preventive|corrective|inspection|emergency"
    status: "pending|approved|rejected|in_progress|completed|cancelled"
    maintenance_record_id: → MaintenanceRecord (links to actual work)
    
    // Relationships
    → item()
    → requestedBy()
    → reviewedBy()
    → maintenanceRecord()
    
    // Methods
    approve(User $reviewer, ?string $notes): void
    reject(User $reviewer, string $reason): void
}
```

### 6. MaintenanceRecord Model

```php
MaintenanceRecord {
    // Actual Work Performed
    item_id: → Item
    maintenance_request_id: → MaintenanceRequest (optional)
    requested_by: → User
    assigned_to: → User (technician)
    type: "preventive|corrective|inspection"
    status: "scheduled|in_progress|completed|cancelled"
    work_performed: "Replaced keyboard, cleaned CPU"
    parts_used: [{part: "Keyboard", cost: 500}]
    actual_cost: 500.00
    labor_hours: 2.5
    outcome: "successful|partial|failed"
    
    // Relationships
    → item()
    → maintenanceRequest()
    → requestedBy()
    → assignedTo()
    
    // Methods
    complete(string $work, string $outcome, array $parts): void
}
```

### 7. DisposalRecord Model

```php
DisposalRecord {
    item_id: → Item
    requested_by: → User
    approved_by: → User
    executed_by: → User
    reason: "Obsolete", "Beyond repair"
    method: "sale|donation|recycle|destroy"
    status: "pending|approved|rejected|executed"
    sale_amount: 1000.00
    
    // Relationships
    → item()
    → requestedBy()
    → approvedBy()
    → executedBy()
}
```

### 8. ActivityLog Model

```php
ActivityLog {
    user_id: → User (who did the action)
    item_id: → Item (what was affected)
    action: "created|updated|assigned|returned|maintained"
    description: "Assigned Desktop Computer to John Doe"
    model_type: "App\Models\Item"
    model_id: 123
    old_values: {"status": "available"}
    new_values: {"status": "assigned"}
    ip_address, user_agent
    
    // Relationships
    → user()
    → item()
    → model() // Polymorphic
}
```

---

## System Workflows

### Workflow 1: Item Lifecycle

```
┌─────────────────────────────────────────────────────────────┐
│                   ITEM LIFECYCLE FLOW                       │
└─────────────────────────────────────────────────────────────┘

1. ACQUISITION
   ┌─────────────────────────────────────┐
   │ Admin creates Item                  │
   │ - Input acquisition details         │
   │ - Set IAR#, Property#, Fund         │
   │ - Assign accountable_person_id      │
   │ - Assign station_id (dept)          │
   │ - Set location_id (physical)        │
   │ - Generate QR Code                  │
   │ Status: "available"                 │
   └──────────────┬──────────────────────┘
                  │
                  ▼
2. ASSIGNMENT (Optional - Temporary Use)
   ┌─────────────────────────────────────┐
   │ Manager assigns to User             │
   │ - Create Assignment record          │
   │ - Set expected_return_date          │
   │ - Update Item status: "assigned"    │
   │ - Log activity                      │
   └──────────────┬──────────────────────┘
                  │
                  ▼
3. MAINTENANCE (When needed)
   ┌─────────────────────────────────────┐
   │ User creates MaintenanceRequest     │
   │ Status: "pending"                   │
   └──────────────┬──────────────────────┘
                  │
                  ▼
   ┌─────────────────────────────────────┐
   │ Manager reviews request             │
   │ - Approve → Create MaintenanceRecord│
   │ - Reject → Send reason              │
   └──────────────┬──────────────────────┘
                  │
                  ▼
   ┌─────────────────────────────────────┐
   │ Technician performs work            │
   │ - Update MaintenanceRecord          │
   │ - Record parts_used, cost           │
   │ - Complete record                   │
   │ - Update Item status: "available"   │
   └──────────────┬──────────────────────┘
                  │
                  ▼
4. INVENTORY UPDATE
   ┌─────────────────────────────────────┐
   │ Staff scans QR code                 │
   │ - Update inventoried_date           │
   │ - Update condition                  │
   │ - Update current_value              │
   └──────────────┬──────────────────────┘
                  │
                  ▼
5. DISPOSAL (End of Life)
   ┌─────────────────────────────────────┐
   │ User creates DisposalRecord         │
   │ Status: "pending"                   │
   └──────────────┬──────────────────────┘
                  │
                  ▼
   ┌─────────────────────────────────────┐
   │ Admin approves disposal             │
   │ - Update status: "approved"         │
   └──────────────┬──────────────────────┘
                  │
                  ▼
   ┌─────────────────────────────────────┐
   │ Execute disposal                    │
   │ - Update Item status: "disposed"    │
   │ - Record sale_amount (if sold)      │
   │ - Complete disposal record          │
   └─────────────────────────────────────┘
```

### Workflow 2: QR Code Scanning Flow

```
┌─────────────────────────────────────────────────────────────┐
│                   QR CODE SCAN FLOW                         │
└─────────────────────────────────────────────────────────────┘

User scans QR Code
        │
        ▼
┌─────────────────────────┐
│ System finds Item by    │
│ qr_code field           │
└──────────┬──────────────┘
           │
           ▼
┌─────────────────────────────────────┐
│ Display Item Details:               │
│ - Property Number                   │
│ - Description, Brand, Model         │
│ - Accountable Person (with photo)   │
│ - Station (Department)              │
│ - Physical Location                 │
│ - Current Status & Condition        │
│ - Current Assignment (if any)       │
│ - Maintenance History               │
└──────────┬──────────────────────────┘
           │
           ▼
┌─────────────────────────────────────┐
│ Available Actions (based on         │
│ user's permissions):                │
│                                     │
│ IF hasPermission('assign_items')    │
│   → Show "Assign to User" button    │
│                                     │
│ IF hasPermission('create_maintenance')│
│   → Show "Request Maintenance"      │
│                                     │
│ IF hasPermission('update_items')    │
│   → Show "Update Inventory"         │
│   → Show "Edit Details"             │
│                                     │
│ IF hasPermission('view_history')    │
│   → Show "View Full History"        │
└─────────────────────────────────────┘
```

### Workflow 3: Maintenance Request Flow

```
┌─────────────────────────────────────────────────────────────┐
│               MAINTENANCE REQUEST WORKFLOW                   │
└─────────────────────────────────────────────────────────────┘

1. REQUEST CREATION
   ┌──────────────────────────────────┐
   │ User/Staff notices issue         │
   │ Permission: create_maintenance   │
   └────────────┬─────────────────────┘
                │
                ▼
   ┌──────────────────────────────────┐
   │ Create MaintenanceRequest        │
   │ - Select Item (via QR/search)    │
   │ - Title: "Keyboard not working"  │
   │ - Description: Details           │
   │ - Priority: high                 │
   │ - Type: corrective               │
   │ Status: "pending"                │
   └────────────┬─────────────────────┘
                │
                ▼
2. REVIEW & APPROVAL
   ┌──────────────────────────────────┐
   │ Manager views pending requests   │
   │ Permission: approve_maintenance  │
   └────────────┬─────────────────────┘
                │
                ├─── APPROVE ───┐
                │               │
                │               ▼
                │     ┌──────────────────────────────┐
                │     │ Update request:              │
                │     │ - status: "approved"         │
                │     │ - reviewed_by: manager_id    │
                │     │ - reviewed_at: now()         │
                │     └──────────┬───────────────────┘
                │                │
                │                ▼
                │     ┌──────────────────────────────┐
                │     │ Create MaintenanceRecord     │
                │     │ - assigned_to: technician    │
                │     │ - status: "scheduled"        │
                │     │ - Link to request            │
                │     └──────────┬───────────────────┘
                │                │
                └─── REJECT ───┐ │
                               │ │
                               ▼ │
                ┌──────────────────────────────┐
                │ Update request:              │
                │ - status: "rejected"         │
                │ - rejection_reason: "..."    │
                └──────────────────────────────┘
                                                │
                                                ▼
3. WORK EXECUTION
   ┌──────────────────────────────────┐
   │ Technician performs maintenance  │
   │ Permission: execute_maintenance  │
   └────────────┬─────────────────────┘
                │
                ▼
   ┌──────────────────────────────────┐
   │ Update MaintenanceRecord         │
   │ - started_at: now()              │
   │ - status: "in_progress"          │
   │ - Update Item.status:            │
   │   "maintenance"                  │
   └────────────┬─────────────────────┘
                │
                ▼
   ┌──────────────────────────────────┐
   │ Complete work                    │
   │ - work_performed: "..."          │
   │ - parts_used: [{...}]            │
   │ - actual_cost: 500.00            │
   │ - labor_hours: 2.5               │
   │ - outcome: "successful"          │
   │ - completed_at: now()            │
   │ - status: "completed"            │
   └────────────┬─────────────────────┘
                │
                ▼
   ┌──────────────────────────────────┐
   │ Update Item                      │
   │ - status: "available"            │
   │ - Update current_value if needed │
   └────────────┬─────────────────────┘
                │
                ▼
   ┌──────────────────────────────────┐
   │ Update MaintenanceRequest        │
   │ - status: "completed"            │
   └──────────────────────────────────┘
```

### Workflow 4: Assignment Flow

```
┌─────────────────────────────────────────────────────────────┐
│                   ASSIGNMENT WORKFLOW                        │
└─────────────────────────────────────────────────────────────┘

Note: Assignment is TEMPORARY use, different from accountable_person

1. ASSIGN ITEM
   ┌──────────────────────────────────┐
   │ Manager assigns item to user     │
   │ Permission: assign_items         │
   └────────────┬─────────────────────┘
                │
                ▼
   ┌──────────────────────────────────┐
   │ Create Assignment                │
   │ - item_id: selected_item         │
   │ - user_id: borrower              │
   │ - assigned_by: current_user      │
   │ - assigned_at: now()             │
   │ - expected_return_date: date     │
   │ - status: "active"               │
   │ - purpose: "Training"            │
   └────────────┬─────────────────────┘
                │
                ▼
   ┌──────────────────────────────────┐
   │ Update Item                      │
   │ - status: "assigned"             │
   └────────────┬─────────────────────┘
                │
                ▼
   ┌──────────────────────────────────┐
   │ Log Activity                     │
   │ - action: "assigned"             │
   │ - description: "Assigned to..."  │
   └──────────────────────────────────┘

2. RETURN ITEM
   ┌──────────────────────────────────┐
   │ User/Manager returns item        │
   │ Permission: return_items         │
   └────────────┬─────────────────────┘
                │
                ▼
   ┌──────────────────────────────────┐
   │ Update Assignment                │
   │ - actual_return_date: now()      │
   │ - status: "returned"             │
   │ - return_condition: "good"       │
   │ - return_notes: "..."            │
   └────────────┬─────────────────────┘
                │
                ▼
   ┌──────────────────────────────────┐
   │ Update Item                      │
   │ - status: "available"            │
   │ - Update condition if changed    │
   └────────────┬─────────────────────┘
                │
                ▼
   ┌──────────────────────────────────┐
   │ Log Activity                     │
   │ - action: "returned"             │
   └──────────────────────────────────┘
```

---

## Permission System

### Permission Structure

```php
Permission {
    name: "view_items"              // Unique identifier
    display_name: "View Items"      // Human-readable
    category: "Items"               // Grouping
    description: "Can view item list and details"
}
```

### 18 Permission Categories

1. **Dashboard**: Overview access
2. **Items**: Equipment management
3. **Assignments**: Item assignment operations
4. **Maintenance**: Maintenance workflow
5. **Disposal**: Asset disposal
6. **Requests**: General requests
7. **Categories**: Category management
8. **Locations**: Location management
9. **Users**: User management
10. **Roles**: Role configuration
11. **Permissions**: Permission management
12. **Reports**: Report generation
13. **Activity Logs**: Audit trail access
14. **Settings**: System configuration
15. **QR Codes**: QR operations
16. **Notifications**: Alert management
17. **Import/Export**: Data transfer
18. **API**: API access

### Sample Permissions per Category

```
Items (15 permissions):
├─ view_items              → See item list
├─ view_item_details       → See full details
├─ create_items            → Add new items
├─ update_items            → Edit items
├─ delete_items            → Remove items
├─ restore_items           → Restore soft-deleted
├─ assign_items            → Assign to users
├─ return_items            → Process returns
├─ transfer_items          → Transfer between locations
├─ scan_items              → QR scan access
├─ generate_qr_codes       → Generate QR
├─ update_inventory        → Update inventory dates
├─ view_item_history       → View activity log
├─ export_items            → Export data
└─ import_items            → Import data

Maintenance (8 permissions):
├─ view_maintenance
├─ create_maintenance
├─ update_maintenance
├─ delete_maintenance
├─ approve_maintenance     → Manager approval
├─ execute_maintenance     → Perform work
├─ complete_maintenance
└─ cancel_maintenance

Users (8 permissions):
├─ view_users
├─ create_users
├─ update_users
├─ delete_users
├─ restore_users
├─ assign_roles            → Assign roles to users
├─ manage_permissions      → Give direct permissions
└─ deactivate_users

Reports (6 permissions):
├─ view_reports
├─ generate_item_reports
├─ generate_maintenance_reports
├─ generate_assignment_reports
├─ generate_disposal_reports
└─ export_reports
```

### 10 Predefined Roles

```php
1. Super Administrator
   - Has ALL 130+ permissions
   - Can manage everything
   - System role (cannot be deleted)

2. System Administrator
   - All permissions except role/permission management
   - Can manage users, items, locations

3. Asset Manager
   - Full item management
   - Approve maintenance/disposal
   - View all reports
   - Assign items

4. Department Manager
   - Manage items in their department
   - Approve requests from their staff
   - View department reports

5. Maintenance Manager
   - Manage maintenance workflow
   - Assign work to technicians
   - Approve maintenance requests

6. Technician
   - Execute maintenance work
   - View assigned tasks
   - Update maintenance records

7. Inventory Staff
   - Create/update items
   - Update inventory dates
   - Generate QR codes
   - Process assignments

8. Staff
   - View items
   - Request maintenance
   - Request assignments
   - Scan QR codes

9. Viewer/Auditor
   - Read-only access
   - View items, reports, logs
   - No create/update/delete

10. Guest
    - Minimal access
    - View basic item information
```

### Permission Usage in Code

```php
// Check single permission
if (auth()->user()->hasPermission('create_items')) {
    // Show create button
}

// Check multiple permissions (ANY)
if (auth()->user()->hasAnyPermission(['view_items', 'view_item_details'])) {
    // Show item list
}

// Check multiple permissions (ALL)
if (auth()->user()->hasAllPermissions(['approve_maintenance', 'view_maintenance'])) {
    // Show approval interface
}

// Check role
if (auth()->user()->hasRole('super_administrator')) {
    // Show admin panel
}

// Controller middleware
public function __construct()
{
    $this->middleware('permission:create_items')->only(['create', 'store']);
    $this->middleware('permission:update_items')->only(['edit', 'update']);
    $this->middleware('permission:delete_items')->only('destroy');
}
```

### Creating Custom Roles

```php
// Super Admin creates a "Property Officer" role
$role = Role::create([
    'name' => 'property_officer',
    'display_name' => 'Property Officer',
    'description' => 'Handles property tagging and inventory',
]);

// Assign specific permissions
$permissions = Permission::whereIn('name', [
    'view_items',
    'create_items',
    'update_items',
    'update_inventory',
    'generate_qr_codes',
    'scan_items',
    'view_reports',
    'generate_item_reports',
])->get();

$role->permissions()->attach($permissions);

// Assign role to user
$user->assignRole($role);

// OR give direct permission to user (outside their role)
$permission = Permission::where('name', 'approve_disposal')->first();
$user->givePermission($permission);
```

---

## QR Code Integration

### QR Code Fields

```php
Item {
    qr_code: "unique_generated_code"  // Stored in database
}
```

### QR Code Generation Flow

```
1. Admin creates new Item
        │
        ▼
2. System generates unique QR code
   - Hash: property_number + timestamp
   - Store in Item.qr_code field
        │
        ▼
3. Generate QR image
   - Library: SimpleSoftwareIO/simple-qrcode
   - Format: PNG/SVG
   - Size: 300x300px
        │
        ▼
4. Display on property tag
   - Print property tag with QR code
   - Include: IAR#, Property#, Description, QR image
```

### QR Code Scanning Flow

```
1. User opens mobile app/scanner
        │
        ▼
2. Scan QR code
        │
        ▼
3. Extract qr_code value
        │
        ▼
4. API call: GET /api/items/scan/{qr_code}
        │
        ▼
5. System finds Item::where('qr_code', $qr_code)
        │
        ▼
6. Load relationships:
   - accountablePerson
   - station
   - location
   - category
   - currentAssignment
   - maintenanceRequests (pending)
        │
        ▼
7. Return item details + available actions
```

### QR Code API Endpoints

```php
// Scan QR code
GET /api/items/scan/{qr_code}
Response: {
    "item": {
        "id": 1,
        "property_number": "2021-06-086-164",
        "description": "DESKTOP COMPUTER ACER VERITON M4665G",
        "accountable_person": {
            "name": "Dr. Jesus Paguigan",
            "department": "MIS"
        },
        "station": {
            "name": "MIS Department"
        },
        "location": {
            "name": "Building A - Room 101"
        },
        "status": "assigned",
        "current_assignment": {
            "user": "John Doe",
            "assigned_at": "2025-01-15",
            "expected_return_date": "2025-02-15"
        }
    },
    "available_actions": [
        "view_details",
        "update_inventory",
        "request_maintenance"
    ]
}

// Generate QR code for item
POST /api/items/{id}/generate-qr
Response: {
    "qr_code": "generated_unique_code",
    "qr_image_url": "/storage/qr-codes/item-1-qr.png"
}
```

---

## Model Cohesion Analysis

### ✅ Cohesive Relationships

All models are **properly aligned** with the updated Item model:

| Model | Status | Notes |
|-------|--------|-------|
| **Item** | ✅ Updated | Matches property tag format |
| **User** | ✅ Updated | Added `accountableItems()` relationship |
| **Location** | ✅ Updated | Added `stationItems()` for station_id |
| **Assignment** | ✅ Compatible | Works with Item.status changes |
| **MaintenanceRequest** | ✅ Compatible | Properly links to Item |
| **MaintenanceRecord** | ✅ Compatible | Tracks maintenance work |
| **DisposalRecord** | ✅ Compatible | Handles disposal workflow |
| **ActivityLog** | ✅ Compatible | Logs all item changes |
| **Category** | ✅ Compatible | No changes needed |
| **Role** | ✅ Compatible | No changes needed |
| **Permission** | ✅ Compatible | No changes needed |
| **Request** | ✅ Compatible | Generic request system |

### Key Improvements Made

1. **User Model**: Added `accountableItems()` and `activeAccountableItems()` relationships
2. **Location Model**: Added `stationItems()` to track items by station_id
3. **Item Model**: Complete overhaul to match property tag structure
4. **Database Migration**: Updated items table schema

### No Breaking Changes

The updates maintain **backward compatibility**:
- Existing relationships still work
- Added new relationships don't interfere with existing ones
- Status values remain the same
- Foreign key constraints properly configured

---

## Summary

### System Flow Overview

```
┌─────────────────────────────────────────────────────────────┐
│                   SYSTEM ARCHITECTURE                        │
└─────────────────────────────────────────────────────────────┘

USER AUTHENTICATION (Laravel Fortify + 2FA)
        │
        ├─→ Check Permissions (Granular)
        │
        ▼
┌─────────────────────────────────────────────────────────────┐
│  ACTIONS BASED ON PERMISSIONS                               │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  Dashboard → View summary, stats, notifications             │
│                                                             │
│  Items → Scan QR → View/Edit/Assign/Maintain               │
│                                                             │
│  Assignments → Track temporary item usage                   │
│                                                             │
│  Maintenance → Request → Approve → Execute → Complete       │
│                                                             │
│  Disposal → Request → Approve → Execute                     │
│                                                             │
│  Reports → Generate various reports                         │
│                                                             │
│  Activity Logs → View audit trail                          │
│                                                             │
└─────────────────────────────────────────────────────────────┘
        │
        ▼
ALL ACTIONS LOGGED IN ACTIVITY_LOGS
```

### Key Principles

1. **Property Tag Compliance**: System matches real-world EARIST property tags
2. **QR-First Approach**: Every item has QR code for mobile scanning
3. **Granular Permissions**: 130+ permissions for precise access control
4. **Complete Audit Trail**: All actions logged in ActivityLog
5. **Workflow-Based**: Requests require approvals before execution
6. **Separation of Concerns**: Clear distinction between accountability and temporary assignment

---

**Version**: 1.0  
**Last Updated**: November 14, 2025  
**System**: Equipment Property Management System (EPMS)  
**Institution**: EARIST University
