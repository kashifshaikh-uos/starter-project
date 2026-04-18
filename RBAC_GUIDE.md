# FinSol AI - Role Based Access Control (RBAC) Guide

## Table of Contents
1. [Privilege Management](#privilege-management)
2. [Role Management](#role-management)
3. [User Role Assignment](#user-role-assignment)
4. [API Endpoints](#api-endpoints)
5. [Examples](#examples)

---

## Privilege Management

### What is a Privilege?
A privilege is a permission that allows a user to perform a specific action (like viewing, creating, updating users). Each privilege is tied to an API route and HTTP method.

### How to Add a New Privilege

#### Step 1: Add via Admin UI (Recommended)

1. **Login as Admin**
   - Go to `http://localhost:3000/`
   - Enter CNIC: `1234567890123`
   - Password: `password`

2. **Navigate to Settings**
   - Click on sidebar "Settings"
   - Select "Privilege Management"
   - Click "Create Privilege"

3. **Fill Privilege Details**
   ```
   Group: Select appropriate group (e.g., "User Management")
   Name: Display name (e.g., "Delete User")
   Slug: Unique identifier (e.g., "delete-user")
   API Route: /api/users/{id}  [with placeholders for dynamic IDs]
   Method: DELETE
   Frontend Route: /users  [where this feature is accessed]
   Show in Menu: false  [unless it's a menu item]
   Is Active: true
   ```

4. **Save**
   - Click "Create" button

#### Step 2: Assign Privilege to Role

1. **Go to Role Management**
   - Settings → Role Management → Select Role

2. **Assign Privilege**
   - Click "Assign Privileges"
   - Search for the privilege (e.g., "delete-user")
   - Click checkbox to select
   - Click "Assign"

3. **Verify**
   - The privilege now appears in role's privilege list

---

## Role Management

### What is a Role?
A role is a collection of privileges. Users are assigned roles, and through roles, they get all permissions.

### Predefined Roles

**Super Admin**
- Slug: `super-admin`
- Privileges: ALL (23 total)
- Has access to: All CRUD operations + role/privilege management

**Example Custom Role: Department Manager**
```
Name: Department Manager
Slug: department-manager
Privileges:
  - User List
  - Create User
  - View User
  - Update User
  - Employee List
  - View Employee
```

### How to Create a New Role

#### Via Admin UI

1. **Navigate to Settings → Role Management**
2. **Click "Create Role"**
3. **Fill Details**
   ```
   Name: Department Manager
   Slug: department-manager (auto-generated from name)
   Description: Can manage department employees
   Is Active: true
   ```
4. **Save**
5. **Assign Privileges** (See section above)

#### Via API

```bash
POST /api/roles
Authorization: Bearer <token>
Content-Type: application/json

{
  "name": "Department Manager",
  "slug": "department-manager",
  "description": "Can manage department employees",
  "is_active": true
}
```

---

## User Role Assignment

### What is User Role Assignment?
This links a user to one or more roles. When a user is assigned a role, they inherit all privileges of that role.

### How to Assign Role to User

#### Via Admin UI

1. **Navigate to Settings → User Management**
2. **Click "Assign Roles" button** (in user row)
3. **Select Roles**
   - Check "Super Admin" or other roles
   - Can assign multiple roles
4. **Save**
5. **Verify**
   - User now has permissions from assigned roles

#### Via API

```bash
POST /api/users/{user_id}/assign-roles
Authorization: Bearer <token>
Content-Type: application/json

{
  "roles": [1, 2, 3]  // Array of role IDs
}
```

### Setting Active Role

Each user has an `active_role_id`. This is the current role being used. When checking privileges, the system checks this active role.

- **Automatic**: Set during first login (to first assigned role)
- **Manual**: User can switch roles if multiple roles assigned

```bash
PUT /api/users/{user_id}
Authorization: Bearer <token>
Content-Type: application/json

{
  "active_role_id": 2
}
```

---

## API Endpoints

### Privilege Endpoints

```
GET    /api/privileges              - List all privileges
POST   /api/privileges              - Create new privilege
GET    /api/privileges/{id}         - Get privilege details
PUT    /api/privileges/{id}         - Update privilege
GET    /api/privilege-groups        - List privilege groups (with ungrouped)
POST   /api/privilege-groups        - Create privilege group
GET    /api/privilege-groups/{id}   - Get privilege group details
PUT    /api/privilege-groups/{id}   - Update privilege group
```

### Role Endpoints

```
GET    /api/roles                   - List all roles
POST   /api/roles                   - Create new role
GET    /api/roles/{id}              - Get role details
PUT    /api/roles/{id}              - Update role
POST   /api/roles/{id}/assign-privileges  - Assign privileges to role
```

### User Endpoints

```
GET    /api/users                   - List all users
POST   /api/users                   - Create new user
GET    /api/users/{id}              - Get user details
PUT    /api/users/{id}              - Update user
POST   /api/users/{id}/assign-roles - Assign roles to user
GET    /api/me                      - Get current user + menu + privileges
```

---

## Examples

### Example 1: Create Complete Admin Setup

**Step 1: Create Privilege Group**
```json
POST /api/privilege-groups
{
  "name": "Inventory Management",
  "slug": "inventory-management"
}
```

**Step 2: Create Privileges**
```json
POST /api/privileges
{
  "privilege_group_id": 5,
  "name": "List Inventory",
  "slug": "inventory-list",
  "api_route": "/api/inventory",
  "method": "GET",
  "is_active": true
}
```

**Step 3: Create Role**
```json
POST /api/roles
{
  "name": "Inventory Manager",
  "slug": "inventory-manager",
  "description": "Manages inventory items"
}
```

**Step 4: Assign Privileges to Role**
```json
POST /api/roles/5/assign-privileges
{
  "privileges": [20, 21, 22]  // Array of privilege IDs
}
```

**Step 5: Create User**
```json
POST /api/users
{
  "name": "Ali Khan",
  "cnic_no": "3310123456789",
  "password": "secure_password"
}
```

**Step 6: Assign Role to User**
```json
POST /api/users/5/assign-roles
{
  "roles": [5]  // Array of role IDs
}
```

---

### Example 2: Check User Permissions

**Get Current User Privileges**
```json
GET /api/me
Authorization: Bearer <token>

Response:
{
  "user": { id: 5, name: "Ali Khan", ... },
  "menu": [...],  // Menu structure
  "privileges": [  // Privilege slugs
    "inventory-list",
    "inventory-create",
    "inventory-update"
  ]
}
```

---

## Privilege Groups

### Predefined Groups

1. **User Management**
   - Privileges: User List, Create User, View User, Update User, Assign Roles

2. **Role Management**
   - Privileges: Role List, Create Role, View Role, Update Role, Assign Privileges

3. **Privilege Management**
   - Privileges: Privilege List, Create Privilege, View Privilege, Update Privilege

4. **API Actions**
   - Privileges: View operations, Update operations, Assign operations

5. **Ungrouped** (Special)
   - Contains privileges with no group assigned
   - ID: 0 (artificial)

---

## Important Notes

### ✅ Best Practices

1. **Use Meaningful Slugs**
   - ✅ Good: `user-list`, `delete-user`, `export-report`
   - ❌ Bad: `users`, `delete`, `export`

2. **Group Related Privileges**
   - Don't leave privileges ungrouped
   - Use appropriate groups for organization

3. **Active Status**
   - Always set `is_active: true` for privileges
   - Inactive privileges are ignored

4. **API Route Naming**
   - Use RESTful convention: `/api/resource` or `/api/resource/{id}`
   - Include {id} for single resource operations

5. **Method Specification**
   - GET: Listing and viewing
   - POST: Creating new records
   - PUT/PATCH: Updating records
   - DELETE: Deleting records
   - null: Matches any method

### ⚠️ Common Issues

**Issue: "Access Denied - no privilege configured"**
- Solution: Create privilege with exact API route and method
- Verify route normalization: `/api/users/123` → `/api/users/{id}`

**Issue: User cannot access despite having role**
- Check: Is role `is_active = true`?
- Check: Is privilege `is_active = true`?
- Check: Is user's `active_role_id` set?
- Check: Is privilege attached to role?

**Issue: Privilege not showing in UI**
- Check: `show_in_menu = true`?
- Check: `parent_id` is set for submenu items?
- Check: Privilege `is_active = true`?

---

## Testing Privileges

### Via cURL

```bash
# Login
curl -X POST http://localhost/finsol_ai/backend/public/api/login \
  -H "Content-Type: application/json" \
  -d '{"cnic_no":"1234567890123","password":"password"}'

# Get token from response
TOKEN="eyJ0eXAiOiJKV1QiLCJhbGc..."

# Test API with token
curl -X GET http://localhost/finsol_ai/backend/public/api/users \
  -H "Authorization: Bearer $TOKEN"
```

### Via Frontend

1. Open DevTools (F12)
2. Go to Network tab
3. Make request through UI
4. Check response for 200 (allowed) or 403 (denied)
5. Verify in `/api/me` endpoint

---

## Summary Flow

```
1. Create Privilege Group
        ↓
2. Create Privilege (with API route & method)
        ↓
3. Create Role
        ↓
4. Assign Privilege to Role
        ↓
5. Create User
        ↓
6. Assign Role to User (sets active_role_id)
        ↓
7. User can now access APIs for their privileges
```

---

**Last Updated:** April 18, 2026
**Version:** 1.0
