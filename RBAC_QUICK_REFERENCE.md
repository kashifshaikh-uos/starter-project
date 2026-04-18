# FinSol AI - RBAC Quick Reference

## Quick Links
- **Admin Login**: http://localhost:3000
- **API Base**: http://localhost/finsol_ai/backend/public/api
- **Credentials**: CNIC: `1234567890123` | Password: `password`

---

## 3-Step Complete Workflow

### Step 1️⃣ : CREATE PRIVILEGE (What can be done)

**UI Path**: Settings → Privilege Management → Create Privilege

**Fill These Fields**:
- **Name**: `Delete User` (display name)
- **Slug**: `delete-user` (unique ID)
- **Group**: `User Management`
- **API Route**: `/api/users/{id}` (the endpoint)
- **Method**: `DELETE` (HTTP method)
- **Is Active**: ✅ Check

**Or via API**:
```bash
POST /api/privileges
{
  "name": "Delete User",
  "slug": "delete-user",
  "privilege_group_id": 1,
  "api_route": "/api/users/{id}",
  "method": "DELETE",
  "is_active": true
}
```

---

### Step 2️⃣ : ASSIGN PRIVILEGE TO ROLE (Who can do it)

**UI Path**: Settings → Role Management → Select Role → Assign Privileges

**Steps**:
1. Find the Role (e.g., "Super Admin")
2. Click "Assign Privileges"
3. Search for privilege (e.g., "delete-user")
4. Check the box
5. Click "Assign"

**Or via API**:
```bash
POST /api/roles/{roleId}/assign-privileges
{
  "privileges": [1, 2, 3]
}
```

---

### Step 3️⃣ : ASSIGN ROLE TO USER (Give role to person)

**UI Path**: Settings → User Management → Click User → Assign Roles

**Steps**:
1. Find the User
2. Click "Assign Roles"
3. Check roles to assign (e.g., "Super Admin")
4. Click "Assign"

**Or via API**:
```bash
POST /api/users/{userId}/assign-roles
{
  "roles": [1, 2]
}
```

---

## Common Tasks

### Add New Feature to Admin
```
1. Create Privilege: Settings → Privilege Management → Create
2. Assign to Super Admin: Settings → Role Management → Super Admin → Assign Privileges
3. Done! Admin can now access feature
```

### Create Department Manager Role
```
1. Create Role: Settings → Role Management → Create Role
   Name: "Department Manager"
2. Assign Privileges: Click role → Assign Privileges
   Select: User List, Create User, View User, Update User
3. Done! Now assign users to this role
```

### Create New User with Specific Access
```
1. Create User: Settings → User Management → Create User
2. Assign Role: Click user → Assign Roles
3. Select role (e.g., "Department Manager")
4. Done! User can login and access their privileges
```

---

## API Cheat Sheet

### Privileges
```
GET    /api/privileges              # List all
POST   /api/privileges              # Create
GET    /api/privileges/{id}         # View one
PUT    /api/privileges/{id}         # Edit
```

### Roles
```
GET    /api/roles                   # List all
POST   /api/roles                   # Create new
GET    /api/roles/{id}              # View one
PUT    /api/roles/{id}              # Edit
POST   /api/roles/{id}/assign-privileges  # Add privileges
```

### Users
```
GET    /api/users                   # List all
POST   /api/users                   # Create new
GET    /api/users/{id}              # View one
PUT    /api/users/{id}              # Edit
POST   /api/users/{id}/assign-roles # Add roles
GET    /api/me                      # My info + menu + privileges
```

---

## Important Rules

| What | Rule |
|------|------|
| **Privilege Slug** | Use kebab-case: `delete-user`, `export-report` |
| **API Route** | Use REST: `/api/users/{id}` with `{id}` placeholders |
| **Method** | GET, POST, PUT, DELETE, or leave null (any) |
| **Is Active** | ✅ Always check this, else privilege ignored |
| **Group** | Assign to group for organization |
| **Active Role** | User must have `active_role_id` set to use privileges |

---

## Test Your Setup

**Check if admin has privilege**:
```bash
GET /api/me
Authorization: Bearer <token>

# Look for "delete-user" in "privileges" array
```

**Try an API**:
```bash
DELETE /api/users/5
Authorization: Bearer <token>

# Should return 200 (success) or 403 (denied)
```

---

## Troubleshooting

| Problem | Solution |
|---------|----------|
| **403 "No privilege"** | Privilege doesn't exist for this API route |
| **403 "No permission"** | User doesn't have role with this privilege |
| **Menu not showing** | Check `show_in_menu = true` and `is_active = true` |
| **Can't assign privilege** | Privilege must exist first |
| **Can't login as new user** | User must have role assigned |

---

## Default Users

```
Admin User
├─ CNIC: 1234567890123
├─ Password: password
└─ Role: Super Admin (ALL privileges)
```

---

## Common API Routes (Privilege Examples)

```
User Management
├─ GET    /api/users               → privilege-slug: "user-list"
├─ POST   /api/users               → privilege-slug: "create-user"
├─ GET    /api/users/{id}          → privilege-slug: "view-user"
├─ PUT    /api/users/{id}          → privilege-slug: "update-user"
└─ POST   /api/users/{id}/assign-roles → privilege-slug: "assign-roles"

Role Management
├─ GET    /api/roles               → privilege-slug: "role-list"
├─ POST   /api/roles               → privilege-slug: "create-role"
├─ GET    /api/roles/{id}          → privilege-slug: "view-role"
├─ PUT    /api/roles/{id}          → privilege-slug: "update-role"
└─ POST   /api/roles/{id}/assign-privileges → privilege-slug: "assign-privileges"

Privilege Management
├─ GET    /api/privileges          → privilege-slug: "privilege-list"
├─ POST   /api/privileges          → privilege-slug: "create-privilege"
├─ GET    /api/privileges/{id}     → privilege-slug: "view-privilege"
├─ PUT    /api/privileges/{id}     → privilege-slug: "update-privilege"
└─ GET    /api/privilege-groups    → privilege-slug: "privilege-group-list"
```

---

**For detailed guide see**: `RBAC_GUIDE.md`
