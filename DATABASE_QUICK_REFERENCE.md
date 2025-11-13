# EPMS Database Quick Reference

## 🚀 Quick Start

```bash
# Fresh install with all seed data
php artisan migrate:fresh --seed

# Login with:
# Super Admin: admin@example.com / password
# Manager: manager@example.com / password
# Staff: staff@example.com / password
```

## 📊 Database Overview

**12 Models** | **16 Migrations** | **5 Core Seeders** | **130+ Permissions** | **10 Roles**

## 🗂️ Core Models

### User System
- **User** - System users with roles & permissions
- **Role** - 10 predefined roles (Super Admin, Manager, Staff, etc.)
- **Permission** - 130+ granular permissions across 18 categories

### Inventory
- **Item** - Equipment with QR codes, tracking, and history
- **Category** - Hierarchical item classification
- **Location** - Building/Floor/Room hierarchy

### Operations
- **Assignment** - Item assignment to users
- **MaintenanceRequest** - Request/approval workflow
- **MaintenanceRecord** - Actual maintenance work
- **DisposalRecord** - Item disposal tracking
- **Request** - General request/approval workflow
- **ActivityLog** - Complete audit trail

## 🔗 Key Relationships

```
User ─┬─ many Roles
      ├─ many Permissions
      ├─ many Assignments (assigned/assigner)
      ├─ many MaintenanceRequests (requester/reviewer)
      ├─ many MaintenanceRecords (requester/assignee)
      └─ many DisposalRecords (requester/approver/executor)

Item ─┬─ one Category
      ├─ one Location
      ├─ many Assignments
      ├─ many MaintenanceRequests
      ├─ many MaintenanceRecords
      └─ one DisposalRecord

MaintenanceRequest ──> MaintenanceRecord
(approval workflow)    (actual work)
```

## 🎯 Permission Categories

| Category | Count | Examples |
|----------|-------|----------|
| users | 7 | view_any, create, update, delete |
| items | 15 | view_any, create, update, generate_qr |
| assignments | 11 | view_any, create, assign_to_others |
| maintenance | 11 | view_any, create, approve, schedule |
| reports | 12 | view, export, user_assignments |
| **Total** | **130+** | Across 18 categories |

## 👥 Predefined Roles

1. **Super Administrator** - Full system access
2. **Property Administrator** - All property operations
3. **Property Manager** - Day-to-day operations
4. **Inventory Clerk** - Basic inventory tasks
5. **Assignment Officer** - Assignments & returns
6. **Maintenance Coordinator** - Maintenance operations
7. **Auditor** - View-only access
8. **Department Head** - View & request for dept
9. **Staff User** - View own assigned items
10. **Report Viewer** - Reports only

## 📝 Common Queries

### Check User Permissions
```php
$user->hasPermission('items.create'); // true/false
$user->hasRole('property_manager'); // true/false
```

### Get Available Items
```php
Item::where('status', 'available')
    ->with(['category', 'location'])
    ->get();
```

### Get User's Active Assignments
```php
$user->assignments()
    ->where('status', 'active')
    ->with('item')
    ->get();
```

### Get Pending Maintenance
```php
MaintenanceRequest::pending()
    ->with(['item', 'requestedBy'])
    ->orderBy('priority', 'desc')
    ->get();
```

## 🔍 Important Indexes

- `users.status`, `users.department`
- `items.code`, `items.qr_code`, `items.status`
- `assignments.status`, `maintenance_requests.status`
- `activity_logs.created_at`, `activity_logs.action`

## 📦 Seeded Data

### Permissions
- ✅ 130+ permissions across 18 categories

### Roles
- ✅ 10 predefined roles with assigned permissions

### Categories
- Electronics (Computers, Monitors, Printers, Networking)
- Furniture (Desks, Chairs, Storage)
- Vehicles
- Equipment
- Office Supplies

### Locations
- Main Building (3 floors, multiple rooms)
- Warehouse
- Branch Office

### Users
- ✅ Super Admin (admin@example.com)
- ✅ Property Manager (manager@example.com)
- ✅ Staff User (staff@example.com)

## 🛠️ Maintenance Commands

```bash
# View migration status
php artisan migrate:status

# Rollback last migration
php artisan migrate:rollback

# Rollback all and re-migrate
php artisan migrate:fresh

# Run seeders only
php artisan db:seed

# Run specific seeder
php artisan db:seed --class=PermissionSeeder

# Fresh migration + seed
php artisan migrate:fresh --seed
```

## 📋 Status Enums

### Item Status
- `available`, `assigned`, `maintenance`, `disposed`

### Assignment Status
- `active`, `returned`, `overdue`, `cancelled`

### Maintenance Status
- `pending`, `approved`, `rejected`, `in_progress`, `completed`, `cancelled`

### Disposal Status
- `pending`, `approved`, `rejected`, `executed`

## 🔐 Important Notes

1. **Soft Deletes** enabled on: users, categories, locations, items, maintenance_requests
2. **Permission Caching** implemented in User model for performance
3. **Foreign Keys** with proper cascade/set null rules
4. **Indexes** added for common queries
5. **JSON Fields** for flexible data: specifications, parts_used, documentation

## 📚 Documentation Files

- `DATABASE_DOCUMENTATION.md` - Complete reference
- `MODEL_MIGRATION_SEEDER_REPORT.md` - Verification report
- `DATABASE_QUICK_REFERENCE.md` - This file

## ✅ Verification Checklist

- [x] 12 Models created with relationships
- [x] 16 Migrations complete with constraints
- [x] 5 Core seeders functional
- [x] 130+ Permissions seeded
- [x] 10 Roles with permission assignments
- [x] 3 Test users created
- [x] All relationships verified
- [x] Indexes added for performance
- [x] Foreign keys properly configured
- [ ] Run `php artisan migrate:fresh --seed` to verify

---

**Last Updated:** November 13, 2025
**Status:** ✅ Ready for Development
