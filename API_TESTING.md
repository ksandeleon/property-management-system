# API Testing Guide - EPMS User Management

Complete guide for testing User, Role, and Permission management endpoints using cURL.

---

## 🔐 Authentication Setup

First, you need to authenticate and get a token (if using Sanctum):

```bash
# Login (adjust endpoint as needed)
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password"
  }'
```

**Save the token from the response and use it in subsequent requests:**

```bash
export TOKEN="your_token_here"
```

For testing purposes, if you're testing locally without Sanctum, you can temporarily disable the auth middleware.

---

## 👥 USER MANAGEMENT ENDPOINTS

### 1. Get All Users

```bash
# Basic listing
curl -X GET http://localhost:8000/api/users \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

# With pagination
curl -X GET "http://localhost:8000/api/users?per_page=10&page=1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

# With search
curl -X GET "http://localhost:8000/api/users?search=john" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

# With filters
curl -X GET "http://localhost:8000/api/users?status=active&department=IT&sort_by=name&sort_order=asc" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "name": "Admin User",
        "email": "admin@example.com",
        "department": "IT",
        "position": "System Administrator",
        "employee_id": "EMP001",
        "status": "active",
        "roles": [...],
        "created_at": "2025-11-19T10:00:00.000000Z"
      }
    ],
    "per_page": 15,
    "total": 3
  }
}
```

---

### 2. Create New User

```bash
curl -X POST http://localhost:8000/api/users \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john.doe@example.com",
    "password": "Password123!",
    "password_confirmation": "Password123!",
    "department": "IT Department",
    "position": "Software Developer",
    "employee_id": "EMP123",
    "phone": "09171234567",
    "status": "active",
    "roles": [2, 3]
  }'
```

**Expected Response:**
```json
{
  "success": true,
  "message": "User created successfully",
  "data": {
    "id": 4,
    "name": "John Doe",
    "email": "john.doe@example.com",
    "department": "IT Department",
    "position": "Software Developer",
    "employee_id": "EMP123",
    "phone": "09171234567",
    "status": "active",
    "roles": [...]
  }
}
```

**Validation Errors (400):**
```json
{
  "message": "The email has already been taken.",
  "errors": {
    "email": ["The email has already been taken."]
  }
}
```

---

### 3. View Single User

```bash
curl -X GET http://localhost:8000/api/users/1 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "department": "IT",
    "position": "System Administrator",
    "employee_id": "EMP001",
    "roles": [...],
    "permissions": [...],
    "accountable_items": [...],
    "assignments": [...]
  }
}
```

---

### 4. Update User

```bash
# Update basic info
curl -X PUT http://localhost:8000/api/users/1 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "department": "HR Department",
    "position": "HR Manager",
    "status": "active"
  }'

# Update with password change
curl -X PUT http://localhost:8000/api/users/1 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "password": "NewPassword123!",
    "password_confirmation": "NewPassword123!"
  }'

# Update roles
curl -X PUT http://localhost:8000/api/users/1 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "roles": [1, 2]
  }'
```

**Expected Response:**
```json
{
  "success": true,
  "message": "User updated successfully",
  "data": {
    "id": 1,
    "name": "Admin User",
    "department": "HR Department",
    "position": "HR Manager",
    "status": "active"
  }
}
```

---

### 5. Delete User (Soft Delete)

```bash
curl -X DELETE http://localhost:8000/api/users/4 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

**Expected Response:**
```json
{
  "success": true,
  "message": "User deleted successfully"
}
```

**Error Response (422):**
```json
{
  "error": "Cannot delete user with active item assignments"
}
```

---

### 6. View Trashed Users

```bash
curl -X GET http://localhost:8000/api/users/trashed \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

---

### 7. Restore Deleted User

```bash
curl -X POST http://localhost:8000/api/users/4/restore \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

**Expected Response:**
```json
{
  "success": true,
  "message": "User restored successfully",
  "data": {
    "id": 4,
    "name": "John Doe",
    "deleted_at": null
  }
}
```

---

### 8. Force Delete User (Permanent)

```bash
curl -X DELETE http://localhost:8000/api/users/4/force \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

**Expected Response:**
```json
{
  "success": true,
  "message": "User permanently deleted"
}
```

**Error Response (422):**
```json
{
  "error": "Cannot permanently delete user with assignment or accountability history"
}
```

---

## 🎭 ROLE MANAGEMENT ENDPOINTS

### 1. Get All Roles

```bash
# Basic listing
curl -X GET http://localhost:8000/api/roles \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

# With search
curl -X GET "http://localhost:8000/api/roles?search=admin" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "name": "super_administrator",
        "display_name": "Super Administrator",
        "description": "Full system access",
        "is_system_role": true,
        "users_count": 1,
        "permissions": [...]
      }
    ]
  }
}
```

---

### 2. Create New Role

```bash
curl -X POST http://localhost:8000/api/roles \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "inventory_manager",
    "display_name": "Inventory Manager",
    "description": "Manages inventory and items",
    "is_system_role": false,
    "permissions": [1, 2, 3, 15, 16, 17]
  }'
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Role created successfully",
  "data": {
    "id": 11,
    "name": "inventory_manager",
    "display_name": "Inventory Manager",
    "description": "Manages inventory and items",
    "is_system_role": false,
    "permissions": [...]
  }
}
```

---

### 3. View Single Role

```bash
curl -X GET http://localhost:8000/api/roles/1 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "super_administrator",
    "display_name": "Super Administrator",
    "permissions": [...],
    "users": [...]
  }
}
```

---

### 4. Update Role

```bash
curl -X PUT http://localhost:8000/api/roles/11 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "display_name": "Senior Inventory Manager",
    "description": "Senior level inventory management",
    "permissions": [1, 2, 3, 15, 16, 17, 18, 19]
  }'
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Role updated successfully",
  "data": {
    "id": 11,
    "display_name": "Senior Inventory Manager",
    "permissions": [...]
  }
}
```

**Error for System Roles:**
```json
{
  "error": "Cannot modify system roles"
}
```

---

### 5. Delete Role

```bash
curl -X DELETE http://localhost:8000/api/roles/11 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Role deleted successfully"
}
```

**Error Responses:**
```json
{
  "error": "Cannot delete system roles"
}
```
```json
{
  "error": "Cannot delete role that is assigned to users"
}
```

---

### 6. Assign Role to User

```bash
curl -X POST http://localhost:8000/api/roles/2/assign-user \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "user_id": 3
  }'
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Role assigned to user successfully"
}
```

---

### 7. Revoke Role from User

```bash
curl -X POST http://localhost:8000/api/roles/2/revoke-user \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "user_id": 3
  }'
```

---

### 8. Assign Permission to Role

```bash
curl -X POST http://localhost:8000/api/roles/11/assign-permission \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "permission_id": 25
  }'
```

---

### 9. Revoke Permission from Role

```bash
curl -X POST http://localhost:8000/api/roles/11/revoke-permission \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "permission_id": 25
  }'
```

---

## 🔑 PERMISSION MANAGEMENT ENDPOINTS

### 1. Get All Permissions

```bash
# Basic listing
curl -X GET http://localhost:8000/api/permissions \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

# With search
curl -X GET "http://localhost:8000/api/permissions?search=user" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

# Filter by category
curl -X GET "http://localhost:8000/api/permissions?category=Users" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

# Custom pagination
curl -X GET "http://localhost:8000/api/permissions?per_page=50" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "name": "users.view_any",
        "display_name": "View Users",
        "description": "View list of all users",
        "category": "Users"
      }
    ]
  }
}
```

---

### 2. View Single Permission

```bash
curl -X GET http://localhost:8000/api/permissions/1 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "users.view_any",
    "display_name": "View Users",
    "description": "View list of all users",
    "category": "Users",
    "roles": [...],
    "users": [...]
  }
}
```

---

### 3. Get Permission Categories

```bash
curl -X GET http://localhost:8000/api/permissions/categories \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

**Expected Response:**
```json
{
  "success": true,
  "data": [
    "Users",
    "Roles",
    "Permissions",
    "Items",
    "Assignments",
    "Maintenance",
    "Disposals",
    "Reports"
  ]
}
```

---

### 4. Get Permissions Grouped by Category

```bash
curl -X GET http://localhost:8000/api/permissions/by-category \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "Users": [
      {
        "id": 1,
        "name": "users.view_any",
        "display_name": "View Users"
      },
      {
        "id": 2,
        "name": "users.view",
        "display_name": "View User Details"
      }
    ],
    "Items": [...]
  }
}
```

---

### 5. Assign Permission to User

```bash
curl -X POST http://localhost:8000/api/permissions/15/assign-user \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "user_id": 3
  }'
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Permission assigned to user successfully"
}
```

---

### 6. Revoke Permission from User

```bash
curl -X POST http://localhost:8000/api/permissions/15/revoke-user \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "user_id": 3
  }'
```

---

### 7. Bulk Assign Permissions to User

```bash
curl -X POST http://localhost:8000/api/permissions/bulk-assign-user \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "user_id": 3,
    "permission_ids": [15, 16, 17, 18, 19, 20]
  }'
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Permissions assigned to user successfully"
}
```

---

### 8. Get User's Permissions

```bash
curl -X GET http://localhost:8000/api/permissions/user/3 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "direct_permissions": [
      {
        "id": 15,
        "name": "items.view_any"
      }
    ],
    "role_permissions": [
      {
        "id": 1,
        "name": "users.view_any"
      }
    ],
    "all_permissions": [...]
  }
}
```

---

## 🧪 Complete Test Workflow

### Test Sequence 1: User CRUD

```bash
# 1. Create user
curl -X POST http://localhost:8000/api/users \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","email":"test@example.com","password":"Password123!","password_confirmation":"Password123!","department":"IT","position":"Developer","employee_id":"TEST001","status":"active"}'

# 2. View user (use ID from response)
curl -X GET http://localhost:8000/api/users/5 \
  -H "Authorization: Bearer $TOKEN"

# 3. Update user
curl -X PUT http://localhost:8000/api/users/5 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"department":"HR"}'

# 4. Delete user
curl -X DELETE http://localhost:8000/api/users/5 \
  -H "Authorization: Bearer $TOKEN"

# 5. View trashed
curl -X GET http://localhost:8000/api/users/trashed \
  -H "Authorization: Bearer $TOKEN"

# 6. Restore user
curl -X POST http://localhost:8000/api/users/5/restore \
  -H "Authorization: Bearer $TOKEN"

# 7. Force delete
curl -X DELETE http://localhost:8000/api/users/5/force \
  -H "Authorization: Bearer $TOKEN"
```

---

### Test Sequence 2: Role & Permission Assignment

```bash
# 1. Create role
curl -X POST http://localhost:8000/api/roles \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"test_role","display_name":"Test Role","permissions":[1,2,3]}'

# 2. Assign role to user
curl -X POST http://localhost:8000/api/roles/11/assign-user \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"user_id":3}'

# 3. Assign direct permission to user
curl -X POST http://localhost:8000/api/permissions/15/assign-user \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"user_id":3}'

# 4. View user's all permissions
curl -X GET http://localhost:8000/api/permissions/user/3 \
  -H "Authorization: Bearer $TOKEN"
```

---

## 🚨 Common HTTP Status Codes

| Code | Meaning | When It Occurs |
|------|---------|----------------|
| 200 | OK | Successful GET/PUT request |
| 201 | Created | Successful POST (create) |
| 403 | Forbidden | User lacks permission |
| 404 | Not Found | Resource doesn't exist |
| 422 | Unprocessable | Validation failed or business rule violated |
| 500 | Server Error | Application error |

---

## 💡 Tips

1. **Save your token:**
   ```bash
   export TOKEN="your_actual_token"
   ```

2. **Pretty print JSON:**
   ```bash
   curl ... | jq '.'
   ```

3. **Save response to file:**
   ```bash
   curl ... > response.json
   ```

4. **View headers:**
   ```bash
   curl -i ...
   ```

5. **Test with Postman/Insomnia:**
   Import these cURL commands directly into Postman or Insomnia for easier testing.

---

## ✅ Testing Checklist

- [ ] User CRUD operations
- [ ] User soft delete & restore
- [ ] User force delete
- [ ] Search & filter users
- [ ] Role CRUD operations
- [ ] Assign/revoke roles to users
- [ ] Permission listing
- [ ] Assign/revoke permissions to users
- [ ] Assign/revoke permissions to roles
- [ ] View user's all permissions
- [ ] Test permission checks (403 responses)
- [ ] Test validation errors (422 responses)

---

**Happy Testing!** 🚀
