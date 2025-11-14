# EPMS Model Relationships - Visual Guide

## Complete Entity Relationship Diagram

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                           EPMS DATABASE MODELS                                  │
└─────────────────────────────────────────────────────────────────────────────────┘

                                   ┌──────────────┐
                                   │  Permission  │
                                   ├──────────────┤
                                   │ id           │
                                   │ name         │
                                   │ category     │
                                   └──────┬───────┘
                                          │
                                          │ Many-to-Many
                                          │
                    ┌─────────────────────┼─────────────────────┐
                    │                     │                     │
                    ▼                     ▼                     │
            ┌──────────────┐      ┌──────────────┐            │
            │     Role     │      │     User     │◄───────────┘
            ├──────────────┤      ├──────────────┤
            │ id           │      │ id           │
            │ name         │      │ name         │
            │ display_name │      │ email        │
            └──────┬───────┘      │ department   │
                   │              │ position     │
                   │              └──────┬───────┘
                   │                     │
                   └──────────┬──────────┘
                              │
                              │ Many-to-Many
                              ▼
                   ┌─────────────────────┐
                   │ (Junction Tables)   │
                   │ - user_roles        │
                   │ - role_permissions  │
                   │ - user_permissions  │
                   └─────────────────────┘


┌──────────────────────────────────────────────────────────────────────────────┐
│                          CORE ITEM ENTITY                                     │
└──────────────────────────────────────────────────────────────────────────────┘

                        ┌────────────────────┐
                        │     Category       │
                        ├────────────────────┤
                        │ id                 │
                        │ name               │
                        │ parent_id   ───────┼──┐ Self-referencing
                        └─────────┬──────────┘  │ (Hierarchical)
                                  │             │
                                  │ One-to-Many ◄┘
                                  ▼
                        ┌────────────────────┐
                        │      Location      │
                        ├────────────────────┤         ┌────────────────┐
                        │ id                 │         │      User      │
                        │ name               │         │ (accountable)  │
                        │ building           │         └────────┬───────┘
                        │ floor, room        │                  │
                        │ parent_id   ───────┼──┐               │
                        └────┬───────┬───────┘  │               │
                             │       │          │               │
               ┌─────────────┘       │          ◄───────────────┘
               │                     │          │
               │ One-to-Many         │          │ One-to-Many
               │                     │          │
               ▼                     ▼          ▼
    ┌──────────────────────────────────────────────────────────────┐
    │                           ITEM (Central Entity)              │
    ├──────────────────────────────────────────────────────────────┤
    │ id                                                           │
    │                                                              │
    │ PROPERTY IDENTIFICATION:                                     │
    │ ├─ property_number (unique)    → "2021-06-086-164"         │
    │ ├─ iar_number                  → "164-2021-054"             │
    │ ├─ fund_cluster                → "164"                       │
    │ └─ qr_code (unique)            → Generated QR                │
    │                                                              │
    │ ITEM DETAILS:                                                │
    │ ├─ description                 → "DESKTOP COMPUTER..."       │
    │ ├─ brand                       → "ACER"                      │
    │ ├─ model                       → "VERITON M4665G"            │
    │ ├─ serial_number               → "SN123456789"              │
    │ └─ category_id          ───────┼──► Category                │
    │                                                              │
    │ ACQUISITION:                                                 │
    │ ├─ acquisition_cost            → ₱78,710.00                 │
    │ ├─ acquisition_date            → 2021-06-04                 │
    │ └─ supplier                    → "Vendor Name"              │
    │                                                              │
    │ ACCOUNTABILITY & LOCATION:                                   │
    │ ├─ accountable_person_id ──────┼──► User (Official custodian)│
    │ ├─ station_id           ───────┼──► Location (Dept/Unit)    │
    │ └─ location_id          ───────┼──► Location (Physical)     │
    │                                                              │
    │ INVENTORY:                                                   │
    │ ├─ inventoried_date            → Last inventory date        │
    │ ├─ estimated_useful_life       → Years                      │
    │ └─ unit_of_measure             → "unit", "set", "piece"    │
    │                                                              │
    │ STATUS:                                                      │
    │ ├─ condition                   → excellent|good|fair|poor   │
    │ ├─ status                      → available|assigned|...     │
    │ └─ current_value               → Depreciated value          │
    │                                                              │
    │ ADDITIONAL:                                                  │
    │ ├─ specifications (JSON)       → Technical specs            │
    │ ├─ remarks                     → Notes                      │
    │ └─ image_path                  → Photo                      │
    └──────┬───┬────┬─────┬──────┬───────┬──────────────────────┘
           │   │    │     │      │       │
           │   │    │     │      │       │ One-to-One
           │   │    │     │      │       ▼
           │   │    │     │      │   ┌────────────────────┐
           │   │    │     │      │   │  DisposalRecord    │
           │   │    │     │      │   ├────────────────────┤
           │   │    │     │      │   │ item_id            │
           │   │    │     │      │   │ requested_by  ─────┼──► User
           │   │    │     │      │   │ approved_by   ─────┼──► User
           │   │    │     │      │   │ executed_by   ─────┼──► User
           │   │    │     │      │   │ method (sale/etc)  │
           │   │    │     │      │   │ status             │
           │   │    │     │      │   └────────────────────┘
           │   │    │     │      │
           │   │    │     │      │ One-to-Many
           │   │    │     │      ▼
           │   │    │     │   ┌────────────────────┐
           │   │    │     │   │  ActivityLog       │
           │   │    │     │   ├────────────────────┤
           │   │    │     │   │ item_id            │
           │   │    │     │   │ user_id       ─────┼──► User
           │   │    │     │   │ action             │
           │   │    │     │   │ old_values (JSON)  │
           │   │    │     │   │ new_values (JSON)  │
           │   │    │     │   └────────────────────┘
           │   │    │     │
           │   │    │     │ One-to-Many
           │   │    │     ▼
           │   │    │   ┌─────────────────────────┐
           │   │    │   │  MaintenanceRecord      │
           │   │    │   ├─────────────────────────┤
           │   │    │   │ item_id                 │
           │   │    │   │ maintenance_request_id ─┼──┐
           │   │    │   │ requested_by       ─────┼──┼──► User
           │   │    │   │ assigned_to        ─────┼──┼──► User
           │   │    │   │ work_performed          │  │
           │   │    │   │ parts_used (JSON)       │  │
           │   │    │   │ actual_cost             │  │
           │   │    │   │ status                  │  │
           │   │    │   └─────────────────────────┘  │
           │   │    │                                 │
           │   │    │ One-to-Many                     │
           │   │    ▼                                 │
           │   │   ┌─────────────────────────┐       │
           │   │   │  MaintenanceRequest     │◄──────┘
           │   │   ├─────────────────────────┤
           │   │   │ item_id                 │
           │   │   │ requested_by       ─────┼──► User
           │   │   │ reviewed_by        ─────┼──► User
           │   │   │ maintenance_record_id   │ (Links to created record)
           │   │   │ priority, type          │
           │   │   │ status (pending/etc)    │
           │   │   └─────────────────────────┘
           │   │
           │   │ One-to-Many
           │   ▼
           │   ┌─────────────────────────┐
           │   │     Assignment          │
           │   ├─────────────────────────┤
           │   │ item_id                 │
           │   │ user_id            ─────┼──► User (Temporary borrower)
           │   │ assigned_by        ─────┼──► User (Who assigned)
           │   │ assigned_at             │
           │   │ expected_return_date    │
           │   │ actual_return_date      │
           │   │ status (active/etc)     │
           │   │ purpose                 │
           │   └─────────────────────────┘
           │
           │ One-to-Many
           ▼
       ┌─────────────────────────┐
       │       Request           │
       ├─────────────────────────┤
       │ user_id            ─────┼──► User
       │ item_id                 │
       │ approved_by        ─────┼──► User
       │ type                    │
       │ status                  │
       └─────────────────────────┘
```

## Relationship Summary Table

| From Model | Relationship Type | To Model | Foreign Key | Purpose |
|------------|------------------|----------|-------------|---------|
| **Item** | BelongsTo | Category | category_id | Item categorization |
| **Item** | BelongsTo | User | accountable_person_id | Official custodian |
| **Item** | BelongsTo | Location | station_id | Department/Unit |
| **Item** | BelongsTo | Location | location_id | Physical location |
| **Item** | HasMany | Assignment | - | Temporary assignments |
| **Item** | HasOne | Assignment (active) | - | Current assignment |
| **Item** | HasMany | MaintenanceRequest | - | Maintenance requests |
| **Item** | HasMany | MaintenanceRecord | - | Maintenance history |
| **Item** | HasOne | DisposalRecord | - | Disposal tracking |
| **Item** | HasMany | ActivityLog | - | Audit trail |
| **User** | BelongsToMany | Role | user_roles | User's roles |
| **User** | BelongsToMany | Permission | user_permissions | Direct permissions |
| **User** | HasMany | Item | accountable_person_id | Accountable items |
| **User** | HasMany | Assignment | user_id | Borrowed items |
| **User** | HasMany | Assignment | assigned_by | Assignments created |
| **User** | HasMany | MaintenanceRequest | requested_by | Requests created |
| **User** | HasMany | MaintenanceRequest | reviewed_by | Requests reviewed |
| **User** | HasMany | MaintenanceRecord | requested_by | Records requested |
| **User** | HasMany | MaintenanceRecord | assigned_to | Work assigned |
| **User** | HasMany | DisposalRecord | requested_by | Disposals requested |
| **User** | HasMany | DisposalRecord | approved_by | Disposals approved |
| **User** | HasMany | DisposalRecord | executed_by | Disposals executed |
| **User** | HasMany | ActivityLog | - | User's actions |
| **Location** | BelongsTo | Location | parent_id | Hierarchical |
| **Location** | HasMany | Location | parent_id | Children locations |
| **Location** | HasMany | Item | location_id | Items at location |
| **Location** | HasMany | Item | station_id | Items at station |
| **Category** | BelongsTo | Category | parent_id | Hierarchical |
| **Category** | HasMany | Category | parent_id | Children categories |
| **Category** | HasMany | Item | - | Items in category |
| **Role** | BelongsToMany | Permission | role_permissions | Role's permissions |
| **Role** | BelongsToMany | User | user_roles | Users with role |
| **Assignment** | BelongsTo | Item | - | Assigned item |
| **Assignment** | BelongsTo | User | user_id | Borrower |
| **Assignment** | BelongsTo | User | assigned_by | Assigner |
| **MaintenanceRequest** | BelongsTo | Item | - | Item needing maintenance |
| **MaintenanceRequest** | BelongsTo | User | requested_by | Requester |
| **MaintenanceRequest** | BelongsTo | User | reviewed_by | Reviewer |
| **MaintenanceRequest** | BelongsTo | MaintenanceRecord | - | Created record |
| **MaintenanceRecord** | BelongsTo | Item | - | Item maintained |
| **MaintenanceRecord** | BelongsTo | MaintenanceRequest | - | Source request |
| **MaintenanceRecord** | BelongsTo | User | requested_by | Requester |
| **MaintenanceRecord** | BelongsTo | User | assigned_to | Technician |
| **DisposalRecord** | BelongsTo | Item | - | Disposed item |
| **DisposalRecord** | BelongsTo | User | requested_by | Requester |
| **DisposalRecord** | BelongsTo | User | approved_by | Approver |
| **DisposalRecord** | BelongsTo | User | executed_by | Executor |
| **Request** | BelongsTo | User | user_id | Requester |
| **Request** | BelongsTo | Item | - | Related item |
| **Request** | BelongsTo | User | approved_by | Approver |
| **ActivityLog** | BelongsTo | User | - | Actor |
| **ActivityLog** | BelongsTo | Item | - | Affected item |
| **ActivityLog** | MorphTo | * | model_type, model_id | Any model |

## Key Relationships Explained

### 1. Item ↔ User (Accountable Person)

```php
// Item Model
public function accountablePerson()
{
    return $this->belongsTo(User::class, 'accountable_person_id');
}

// User Model
public function accountableItems()
{
    return $this->hasMany(Item::class, 'accountable_person_id');
}

// Usage
$item = Item::find(1);
echo $item->accountablePerson->name; // "Dr. Jesus Paguigan"

$user = User::find(5);
$items = $user->accountableItems; // All items user is accountable for
```

**Purpose**: Track the **official custodian** from the property tag. This is different from temporary assignment.

---

### 2. Item ↔ Location (Dual Purpose)

```php
// Item Model
public function station()  // Department/Unit
{
    return $this->belongsTo(Location::class, 'station_id');
}

public function location()  // Physical Place
{
    return $this->belongsTo(Location::class, 'location_id');
}

// Location Model
public function stationItems()
{
    return $this->hasMany(Item::class, 'station_id');
}

public function items()
{
    return $this->hasMany(Item::class, 'location_id');
}

// Usage
$item = Item::find(1);
echo $item->station->name;   // "MIS Department"
echo $item->location->name;  // "Building A - Room 101"

$misLocation = Location::where('code', 'MIS')->first();
$misItems = $misLocation->stationItems; // All items assigned to MIS
```

**Purpose**: Separate organizational assignment (station) from physical storage (location).

---

### 3. Item ↔ Assignment (Temporary Use)

```php
// Item Model
public function assignments()
{
    return $this->hasMany(Assignment::class);
}

public function currentAssignment()
{
    return $this->hasOne(Assignment::class)
        ->where('status', 'active')
        ->latest();
}

// Assignment Model
public function item()
{
    return $this->belongsTo(Item::class);
}

public function user()  // Borrower
{
    return $this->belongsTo(User::class);
}

public function assignedBy()  // Assigner
{
    return $this->belongsTo(User::class, 'assigned_by');
}

// Usage
$item = Item::find(1);
if ($item->currentAssignment) {
    echo "Currently assigned to: " . $item->currentAssignment->user->name;
}

$user = User::find(10);
$borrowed = $user->assignments()->where('status', 'active')->get();
```

**Purpose**: Track **temporary borrowing**, different from accountable_person.

---

### 4. Item ↔ Maintenance (Two-Step Workflow)

```php
// Step 1: Request
$request = MaintenanceRequest::create([
    'item_id' => $item->id,
    'requested_by' => auth()->id(),
    'title' => 'Keyboard Replacement',
    'status' => 'pending',
]);

// Step 2: Approve & Create Record
if ($manager->hasPermission('approve_maintenance')) {
    $request->approve($manager, 'Approved for maintenance');
    
    $record = MaintenanceRecord::create([
        'item_id' => $item->id,
        'maintenance_request_id' => $request->id,
        'assigned_to' => $technician->id,
        'status' => 'scheduled',
    ]);
    
    $request->update(['maintenance_record_id' => $record->id]);
}

// Step 3: Execute Work
$record->update([
    'status' => 'in_progress',
    'started_at' => now(),
]);

$item->update(['status' => 'maintenance']);

// Step 4: Complete
$record->complete(
    'Replaced keyboard and cleaned',
    'successful',
    [['part' => 'Keyboard', 'cost' => 500]]
);

$item->update(['status' => 'available']);
```

**Purpose**: Separation of approval workflow (MaintenanceRequest) from actual work (MaintenanceRecord).

---

### 5. User ↔ Role ↔ Permission (Granular Access)

```php
// User has roles (Many-to-Many)
$user->roles()->attach($role);

// Role has permissions (Many-to-Many)
$role->permissions()->attach($permission);

// User can have direct permissions (Many-to-Many)
$user->permissions()->attach($permission);

// Check permission
if ($user->hasPermission('create_items')) {
    // User can create items either through:
    // 1. Direct permission assignment, OR
    // 2. One of their roles has this permission
}

// Example: Custom role creation
$propertyOfficer = Role::create([
    'name' => 'property_officer',
    'display_name' => 'Property Officer',
]);

$permissions = Permission::whereIn('name', [
    'view_items', 'create_items', 'update_items',
    'generate_qr_codes', 'scan_items',
])->get();

$propertyOfficer->permissions()->attach($permissions);

$user->assignRole($propertyOfficer);

// Now user has all those permissions
```

**Purpose**: Flexible, granular permission system instead of rigid roles.

---

## Common Queries

### Get all items with full details

```php
$items = Item::with([
    'category',
    'accountablePerson',
    'station',
    'location',
    'currentAssignment.user',
    'maintenanceRequests' => function($query) {
        $query->where('status', 'pending');
    }
])->get();
```

### Get user's accountable items

```php
$user = User::find(5);
$accountableItems = $user->accountableItems()
    ->with(['station', 'location', 'category'])
    ->whereNotIn('status', ['disposed'])
    ->get();
```

### Get items needing maintenance

```php
$needsMaintenance = Item::whereHas('maintenanceRequests', function($query) {
    $query->where('status', 'pending');
})->get();
```

### Get items by station

```php
$misLocation = Location::where('code', 'MIS')->first();
$misItems = $misLocation->stationItems()
    ->with('accountablePerson')
    ->where('status', '!=', 'disposed')
    ->get();
```

### Get maintenance history for item

```php
$item = Item::with([
    'maintenanceRequests.requestedBy',
    'maintenanceRequests.reviewedBy',
    'maintenanceRecords.assignedTo',
])->find(1);

foreach ($item->maintenanceRecords as $record) {
    echo "Work: {$record->work_performed} by {$record->assignedTo->name}";
}
```

---

## Model Cohesion Checklist

✅ **All models updated and cohesive**

| Check | Status | Notes |
|-------|--------|-------|
| Item model matches property tag | ✅ | All fields aligned |
| User has accountable items relationship | ✅ | Added accountableItems() |
| Location supports both station & physical | ✅ | Added stationItems() |
| Assignment works with new Item structure | ✅ | No changes needed |
| Maintenance workflow intact | ✅ | Request → Record flow works |
| Disposal tracking functional | ✅ | No changes needed |
| Activity logging complete | ✅ | Logs all changes |
| Permission system granular | ✅ | 130+ permissions |
| Foreign keys properly set | ✅ | All constraints correct |
| Indexes for performance | ✅ | 14 indexes on items table |

---

**Version**: 1.0  
**Last Updated**: November 14, 2025
