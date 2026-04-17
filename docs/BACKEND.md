# Backend API Documentation

Complete API reference for the Laravel backend. All API routes are prefixed with `/api`.

---

## Table of Contents

- [Authentication](#authentication)
  - [POST /login](#post-login)
  - [GET /me](#get-me)
  - [POST /logout](#post-logout)
  - [POST /refresh](#post-refresh)
  - [POST /change-password](#post-change-password)
  - [POST /change-role](#post-change-role)
- [Password Reset](#password-reset)
  - [POST /forgot-password](#post-forgot-password)
  - [POST /reset-password](#post-reset-password)
- [App Config](#app-config)
  - [GET /app-config](#get-app-config)
- [Users](#users)
  - [GET /users](#get-users)
  - [POST /users](#post-users)
  - [GET /users/{id}](#get-usersid)
  - [PUT /users/{id}](#put-usersid)
  - [POST /users/{id}/assign-roles](#post-usersidassign-roles)
- [Roles](#roles)
  - [GET /roles](#get-roles)
  - [POST /roles](#post-roles)
  - [GET /roles/{id}](#get-rolesid)
  - [PUT /roles/{id}](#put-rolesid)
  - [POST /roles/{id}/assign-privileges](#post-rolesidassign-privileges)
- [Privilege Groups](#privilege-groups)
  - [GET /privilege-groups](#get-privilege-groups)
  - [POST /privilege-groups](#post-privilege-groups)
  - [GET /privilege-groups/{id}](#get-privilege-groupsid)
  - [PUT /privilege-groups/{id}](#put-privilege-groupsid)
- [Privileges](#privileges)
  - [GET /privileges](#get-privileges)
  - [POST /privileges](#post-privileges)
  - [GET /privileges/{id}](#get-privilegesid)
  - [PUT /privileges/{id}](#put-privilegesid)
- [Middleware](#middleware)
- [Database Schema](#database-schema)
- [Models & Relationships](#models--relationships)
- [Security](#security)
- [Configuration](#configuration)

---

## Authentication

All protected routes require the `Authorization: Bearer <token>` header.

### POST /login

Login with CNIC and password. Rate limited: **5 requests/minute**.

**Request:**
```json
{
  "cnic_no": "0000000000000",
  "password": "Password1"
}
```

**Validation Rules:**
| Field    | Rules                   |
|----------|-------------------------|
| cnic_no  | required, string, size:13 |
| password | required, string        |

**Success Response (200):**
```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "token_type": "bearer",
  "expires_in": 3600,
  "user": {
    "id": 1,
    "name": "Admin",
    "cnic_no": "0000000000000",
    "email": null,
    "phone": null,
    "is_active": true,
    "active_role_id": 1,
    "created_at": "2026-04-17T00:00:00.000000Z",
    "updated_at": "2026-04-17T00:00:00.000000Z",
    "active_role": {
      "id": 1,
      "name": "Super Admin",
      "slug": "super-admin",
      "description": "Full system access",
      "is_active": true
    },
    "active_roles": [
      {
        "id": 1,
        "name": "Super Admin",
        "slug": "super-admin",
        "pivot": { "is_active": true }
      }
    ]
  }
}
```

**Error Responses:**
| Status | Message                      | Condition                |
|--------|------------------------------|--------------------------|
| 401    | Invalid CNIC or password     | Wrong credentials        |
| 403    | Account is deactivated       | User `is_active = false` |
| 422    | Validation error             | Invalid input            |
| 429    | Too Many Attempts            | Rate limit exceeded      |

**Notes:**
- On first login, if no `active_role_id` is set, the first active role is auto-assigned.
- The `password` field is never returned in any response (hidden in model).

---

### GET /me

Get the currently authenticated user, their menu items, and privilege slugs.

**Headers:** `Authorization: Bearer <token>`

**Success Response (200):**
```json
{
  "user": {
    "id": 1,
    "name": "Admin",
    "cnic_no": "0000000000000",
    "email": null,
    "phone": null,
    "is_active": true,
    "active_role_id": 1,
    "active_role": { "id": 1, "name": "Super Admin", "slug": "super-admin" },
    "active_roles": [
      { "id": 1, "name": "Super Admin", "slug": "super-admin", "pivot": { "is_active": true } }
    ]
  },
  "menu": [
    {
      "id": 1,
      "name": "Settings",
      "slug": "settings-menu",
      "icon": "fa-cog",
      "frontend_route": null,
      "show_in_menu": true,
      "menu_children": [
        {
          "id": 2,
          "name": "User Management",
          "slug": "user-management-menu",
          "icon": "fa-users",
          "frontend_route": "/users",
          "show_in_menu": true,
          "menu_children": []
        },
        {
          "id": 8,
          "name": "Role Management",
          "slug": "role-management-menu",
          "icon": "fa-shield",
          "frontend_route": "/roles",
          "show_in_menu": true,
          "menu_children": []
        },
        {
          "id": 13,
          "name": "Privilege Management",
          "slug": "privilege-management-menu",
          "icon": "fa-key",
          "frontend_route": "/privileges",
          "show_in_menu": true,
          "menu_children": []
        }
      ]
    }
  ],
  "privileges": [
    "settings-menu",
    "user-management-menu",
    "user-list",
    "create-user",
    "update-user",
    "assign-roles",
    "role-management-menu",
    "role-list",
    "create-role",
    "update-role",
    "assign-privileges",
    "privilege-management-menu",
    "privilege-list",
    "create-privilege"
  ]
}
```

**Error:** `401 Unauthenticated` if token is missing/expired.

---

### POST /logout

Invalidate the current JWT token.

**Headers:** `Authorization: Bearer <token>`

**Success Response (200):**
```json
{
  "message": "Logged out successfully"
}
```

---

### POST /refresh

Get a new JWT token using the current (still valid) token.

**Headers:** `Authorization: Bearer <token>`

**Success Response (200):**
```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "token_type": "bearer",
  "expires_in": 3600
}
```

---

### POST /change-password

Change the current user's password. **Invalidates the current JWT token** — user must login again.

**Headers:** `Authorization: Bearer <token>`

**Request:**
```json
{
  "current_password": "OldPassword1",
  "password": "NewPassword1",
  "password_confirmation": "NewPassword1"
}
```

**Validation Rules:**
| Field              | Rules                                                      |
|--------------------|-------------------------------------------------------------|
| current_password   | required, string                                            |
| password           | required, string, min:8, confirmed, mixed case + numbers    |
| password_confirmation | must match `password`                                    |

**Password Requirements:**
- Minimum 8 characters
- At least 1 uppercase letter
- At least 1 lowercase letter
- At least 1 number

**Success Response (200):**
```json
{
  "message": "Password changed successfully"
}
```

**Error Responses:**
| Status | Message                        | Condition               |
|--------|--------------------------------|-------------------------|
| 422    | Current password is incorrect  | Wrong current password  |
| 422    | Validation error               | Weak/mismatched password |

---

### POST /change-role

Switch the user's active role. Only roles assigned and active for the user are allowed.

**Headers:** `Authorization: Bearer <token>`

**Request:**
```json
{
  "role_id": 2
}
```

**Validation Rules:**
| Field    | Rules                    |
|----------|--------------------------|
| role_id  | required, exists:roles,id |

**Success Response (200):**
```json
{
  "message": "Role switched successfully",
  "user": { "...full user with activeRole and activeRoles..." },
  "menu": [ "...menu items for the new active role..." ],
  "privileges": [ "...privilege slugs for the new active role..." ]
}
```

**Error Responses:**
| Status | Message                              | Condition                        |
|--------|--------------------------------------|----------------------------------|
| 403    | You do not have access to this role  | Role not assigned or not active  |
| 422    | Validation error                     | Invalid role_id                  |

---

## Password Reset

### POST /forgot-password

Send a password reset email. Rate limited: **5 requests/minute**.

**Request:**
```json
{
  "cnic_no": "0000000000000"
}
```

**Success Response (200):** _(Always returns same message to prevent user enumeration)_
```json
{
  "message": "If an account with that CNIC exists, a reset link has been sent."
}
```

**Notes:**
- Even if the CNIC doesn't exist or has no email, the same 200 message is returned.
- Reset token expires in **60 minutes**.
- The email contains a link to: `{FRONTEND_URL}/reset-password?token={token}&cnic={cnic_no}`

---

### POST /reset-password

Reset password using the token received via email. Rate limited: **5 requests/minute**.

**Request:**
```json
{
  "cnic_no": "0000000000000",
  "token": "aBcDeFgHiJkLmNoPqRsTuVwXyZ0123456789...",
  "password": "NewPassword1",
  "password_confirmation": "NewPassword1"
}
```

**Validation Rules:**
| Field                  | Rules                                                   |
|------------------------|---------------------------------------------------------|
| cnic_no                | required, string, size:13                                |
| token                  | required, string                                         |
| password               | required, string, min:8, confirmed, mixed case + numbers |
| password_confirmation  | must match `password`                                    |

**Success Response (200):**
```json
{
  "message": "Password has been reset successfully."
}
```

**Error Responses:**
| Status | Message                                          | Condition             |
|--------|--------------------------------------------------|-----------------------|
| 422    | Invalid or expired reset token.                  | Wrong token or CNIC   |
| 422    | Reset token has expired. Please request a new one.| Token older than 60min |
| 404    | User not found.                                  | CNIC has no user      |

---

## App Config

### GET /app-config

Get application branding configuration. **Public — no authentication required.**

**Success Response (200):**
```json
{
  "name": "Admin Panel",
  "short_name": "AP",
  "icon": "shield",
  "primary_color": "#4f46e5"
}
```

---

## Users

All user routes require authentication + privilege check.

### GET /users

List users with pagination and optional search.

**Headers:** `Authorization: Bearer <token>`  
**Required Privilege:** `user-list`

**Query Parameters:**
| Param   | Type   | Description                                   |
|---------|--------|-----------------------------------------------|
| search  | string | Search by name, CNIC, or email (optional)     |
| page    | int    | Page number for pagination (default: 1)       |

**Success Response (200):**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "name": "Admin",
      "cnic_no": "0000000000000",
      "email": null,
      "phone": null,
      "is_active": true,
      "active_role_id": 1,
      "created_at": "2026-04-17T00:00:00.000000Z",
      "updated_at": "2026-04-17T00:00:00.000000Z",
      "active_roles": [
        {
          "id": 1,
          "name": "Super Admin",
          "slug": "super-admin",
          "pivot": { "is_active": true }
        }
      ]
    }
  ],
  "per_page": 15,
  "total": 1,
  "last_page": 1,
  "from": 1,
  "to": 1
}
```

---

### POST /users

Create a new user.

**Headers:** `Authorization: Bearer <token>`  
**Required Privilege:** `create-user`

**Request:**
```json
{
  "name": "John Doe",
  "cnic_no": "1234567890123",
  "email": "john@example.com",
  "phone": "03001234567",
  "password": "Password1",
  "is_active": true
}
```

**Validation Rules:**
| Field     | Rules                                                     |
|-----------|-----------------------------------------------------------|
| name      | required, string, max:255                                  |
| cnic_no   | required, string, size:13, unique:users                    |
| email     | nullable, email, max:255                                   |
| phone     | nullable, string, max:20                                   |
| password  | required, string, min:8, mixed case + numbers              |
| is_active | boolean (optional, default: true)                          |

**Success Response (201):**
```json
{
  "id": 2,
  "name": "John Doe",
  "cnic_no": "1234567890123",
  "email": "john@example.com",
  "phone": "03001234567",
  "is_active": true,
  "active_role_id": null,
  "created_at": "2026-04-18T00:00:00.000000Z",
  "updated_at": "2026-04-18T00:00:00.000000Z",
  "active_roles": []
}
```

**Error:** `422` for validation errors (duplicate CNIC, weak password, etc.)

---

### GET /users/{id}

Get a single user by ID.

**Required Privilege:** `user-list` (via route matching `GET /api/users/{id}`)

**Success Response (200):**
```json
{
  "id": 1,
  "name": "Admin",
  "cnic_no": "0000000000000",
  "email": null,
  "phone": null,
  "is_active": true,
  "active_role_id": 1,
  "active_roles": [...]
}
```

**Error:** `404` if user not found.

---

### PUT /users/{id}

Update an existing user.

**Required Privilege:** `update-user`

**Request:** _(all fields optional)_
```json
{
  "name": "Admin Updated",
  "cnic_no": "0000000000000",
  "email": "admin@example.com",
  "phone": "03001234567",
  "password": "NewPassword1",
  "is_active": true
}
```

**Validation Rules:**
| Field     | Rules                                                       |
|-----------|-------------------------------------------------------------|
| name      | sometimes, string, max:255                                   |
| cnic_no   | sometimes, string, size:13, unique:users (except current)    |
| email     | nullable, email, max:255                                     |
| phone     | nullable, string, max:20                                     |
| password  | nullable, string, min:8, mixed case + numbers                |
| is_active | boolean                                                      |

**Notes:**
- Password is optional — if not sent, it's not changed.
- CNIC uniqueness check excludes the current user.

**Success Response (200):**
```json
{
  "id": 1,
  "name": "Admin Updated",
  "...": "...updated fields..."
}
```

---

### POST /users/{id}/assign-roles

Assign roles to a user. Uses soft-toggle (activates/deactivates rather than delete/insert).

**Required Privilege:** `assign-roles`

**Request:**
```json
{
  "role_ids": [1, 2, 3]
}
```

**Validation Rules:**
| Field      | Rules                       |
|------------|-----------------------------|
| role_ids   | required, array             |
| role_ids.* | exists:roles,id             |

**Behavior:**
- Roles in `role_ids` → set `is_active = true` in pivot (or attach if new)
- Roles NOT in `role_ids` → set `is_active = false` in pivot (not deleted)
- Timestamps are updated on each change

**Success Response (200):**
```json
{
  "id": 1,
  "name": "Admin",
  "active_roles": [
    { "id": 1, "name": "Super Admin", "pivot": { "is_active": true } },
    { "id": 2, "name": "Editor", "pivot": { "is_active": true } }
  ]
}
```

---

## Roles

### GET /roles

List all roles with privileges and user count.

**Required Privilege:** `role-list`

**Success Response (200):**
```json
[
  {
    "id": 1,
    "name": "Super Admin",
    "slug": "super-admin",
    "description": "Full system access",
    "is_active": true,
    "users_count": 1,
    "privileges": [
      { "id": 1, "name": "Settings", "slug": "settings-menu" },
      { "id": 2, "name": "User Management", "slug": "user-management-menu" }
    ]
  }
]
```

---

### POST /roles

Create a new role.

**Required Privilege:** `create-role`

**Request:**
```json
{
  "name": "Editor",
  "slug": "editor",
  "description": "Can edit content",
  "is_active": true
}
```

**Validation Rules:**
| Field       | Rules                                       |
|-------------|---------------------------------------------|
| name        | required, string, max:255                    |
| slug        | nullable, string, max:255, unique:roles      |
| description | nullable, string                             |
| is_active   | boolean                                      |

**Notes:** If `slug` is not provided, it's auto-generated from the name.

**Success Response (201):**
```json
{
  "id": 2,
  "name": "Editor",
  "slug": "editor",
  "description": "Can edit content",
  "is_active": true,
  "created_at": "...",
  "updated_at": "..."
}
```

---

### GET /roles/{id}

Get a single role with its privileges.

**Required Privilege:** `role-list`

**Success Response (200):**
```json
{
  "id": 1,
  "name": "Super Admin",
  "slug": "super-admin",
  "description": "Full system access",
  "is_active": true,
  "privileges": [...]
}
```

---

### PUT /roles/{id}

Update an existing role.

**Required Privilege:** `update-role`

**Request:**
```json
{
  "name": "Super Admin",
  "description": "Updated description",
  "is_active": true
}
```

**Success Response (200):** Returns updated role with privileges.

---

### POST /roles/{id}/assign-privileges

Assign privileges to a role. Uses `sync` — replaces all existing privilege assignments.

**Required Privilege:** `assign-privileges`

**Request:**
```json
{
  "privilege_ids": [1, 2, 3, 4, 5]
}
```

**Validation Rules:**
| Field           | Rules                    |
|-----------------|--------------------------|
| privilege_ids   | required, array          |
| privilege_ids.* | exists:privileges,id     |

**Success Response (200):** Returns role with updated privileges.

---

## Privilege Groups

### GET /privilege-groups

List all privilege groups with their privileges.

**Success Response (200):**
```json
[
  {
    "id": 1,
    "name": "User Management",
    "slug": "user-management",
    "description": null,
    "is_active": true,
    "privileges": [
      { "id": 3, "name": "User List", "slug": "user-list" },
      { "id": 4, "name": "Create User", "slug": "create-user" }
    ]
  }
]
```

---

### POST /privilege-groups

Create a new privilege group.

**Request:**
```json
{
  "name": "Reports",
  "description": "Report management privileges",
  "is_active": true
}
```

**Validation Rules:**
| Field       | Rules                                                |
|-------------|------------------------------------------------------|
| name        | required, string, max:255                             |
| slug        | nullable, string, max:255, unique:privilege_groups    |
| description | nullable, string                                      |
| is_active   | boolean                                               |

**Success Response (201):** Returns the created group.

---

### GET /privilege-groups/{id}

Get a single privilege group with its privileges.

**Success Response (200):** Returns group with privileges sorted by `sort_order`.

---

### PUT /privilege-groups/{id}

Update a privilege group.

**Success Response (200):** Returns updated group with privileges.

---

## Privileges

### GET /privileges

List all privileges. Can filter by group.

**Query Parameters:**
| Param     | Type | Description                         |
|-----------|------|-------------------------------------|
| group_id  | int  | Filter by privilege group (optional) |

**Success Response (200):**
```json
[
  {
    "id": 1,
    "privilege_group_id": null,
    "parent_id": null,
    "name": "Settings",
    "slug": "settings-menu",
    "frontend_route": null,
    "api_route": null,
    "method": null,
    "menu_type": "menu",
    "icon": "fa-cog",
    "sort_order": 1,
    "show_in_menu": true,
    "description": null,
    "is_active": true,
    "group": null,
    "parent": null,
    "children": [...]
  }
]
```

---

### POST /privileges

Create a new privilege.

**Required Privilege:** `create-privilege`

**Request:**
```json
{
  "privilege_group_id": 1,
  "parent_id": 2,
  "name": "View Reports",
  "slug": "view-reports",
  "frontend_route": "/reports",
  "api_route": "/api/reports",
  "method": "GET",
  "menu_type": "submenu",
  "icon": "fa-chart",
  "sort_order": 1,
  "show_in_menu": true,
  "description": "View all reports",
  "is_active": true
}
```

**Validation Rules:**
| Field              | Rules                                                |
|--------------------|------------------------------------------------------|
| privilege_group_id | nullable, exists:privilege_groups,id                  |
| parent_id          | nullable, exists:privileges,id                        |
| name               | required, string, max:255                             |
| slug               | nullable, string, max:255, unique:privileges          |
| frontend_route     | nullable, string, max:255                             |
| api_route          | nullable, string, max:255                             |
| method             | nullable, string, in: GET,POST,PUT,PATCH,DELETE       |
| menu_type          | in: menu, submenu, none                               |
| icon               | nullable, string, max:255                             |
| sort_order         | integer                                               |
| show_in_menu       | boolean                                               |
| description        | nullable, string                                      |
| is_active          | boolean                                               |

**Success Response (201):** Returns created privilege with group and parent.

---

### GET /privileges/{id}

Get a single privilege with group, parent, children, and roles.

**Success Response (200):**
```json
{
  "id": 3,
  "name": "User List",
  "slug": "user-list",
  "group": { "id": 1, "name": "User Management" },
  "parent": { "id": 2, "name": "User Management" },
  "children": [],
  "roles": [
    { "id": 1, "name": "Super Admin" }
  ]
}
```

---

### PUT /privileges/{id}

Update an existing privilege. Same validation as create (with `sometimes` for required fields).

**Success Response (200):** Returns updated privilege with group, parent, and children.

---

## Middleware

### Authentication (`auth:api`)
- All protected routes require a valid JWT token in the `Authorization: Bearer <token>` header.
- Returns `401 Unauthenticated` if token is missing, expired, or invalid.

### Rate Limiting (`throttle:5,1`)
- Applied to: `/login`, `/forgot-password`, `/reset-password`
- Allows **5 requests per minute** per IP.
- Returns `429 Too Many Attempts` when exceeded.

### Privilege Check (`privilege`)
- Applied to all protected routes.
- **Whitelist** (no privilege check needed): `/api/me`, `/api/logout`, `/api/refresh`, `/api/change-password`, `/api/change-role`
- Normalizes URLs: `/api/users/5` → `/api/users/{id}`
- Matches the normalized URL + HTTP method against the `privileges` table.
- **Fail-closed**: If no privilege is configured for a route, access is **denied** (403).
- Checks if the user's **active role** has the matched privilege.

### CORS (`HandleCors`)
- Allows requests only from `FRONTEND_URL` origin.
- All methods and headers allowed.

---

## Database Schema

### users
| Column          | Type         | Notes                                  |
|-----------------|--------------|----------------------------------------|
| id              | bigint (PK)  | Auto-increment                         |
| name            | varchar(255) |                                        |
| cnic_no         | varchar(13)  | Unique, required                       |
| email           | varchar(255) | Nullable                               |
| phone           | varchar(20)  | Nullable                               |
| password        | varchar(255) | Hashed (bcrypt, 12 rounds)             |
| is_active       | boolean      | Default: true                          |
| active_role_id  | bigint (FK)  | Nullable, references roles.id          |
| created_at      | timestamp    |                                        |
| updated_at      | timestamp    |                                        |

### roles
| Column      | Type         | Notes              |
|-------------|--------------|--------------------|
| id          | bigint (PK)  | Auto-increment     |
| name        | varchar(255) |                    |
| slug        | varchar(255) | Unique             |
| description | text         | Nullable           |
| is_active   | boolean      | Default: true      |
| created_at  | timestamp    |                    |
| updated_at  | timestamp    |                    |

### privilege_groups
| Column      | Type         | Notes              |
|-------------|--------------|--------------------|
| id          | bigint (PK)  | Auto-increment     |
| name        | varchar(255) |                    |
| slug        | varchar(255) | Unique             |
| description | text         | Nullable           |
| is_active   | boolean      | Default: true      |
| created_at  | timestamp    |                    |
| updated_at  | timestamp    |                    |

### privileges
| Column              | Type         | Notes                                   |
|---------------------|--------------|-----------------------------------------|
| id                  | bigint (PK)  | Auto-increment                          |
| privilege_group_id  | bigint (FK)  | Nullable, references privilege_groups.id |
| parent_id           | bigint (FK)  | Nullable, self-referencing              |
| name                | varchar(255) |                                         |
| slug                | varchar(255) | Unique                                  |
| frontend_route      | varchar(255) | Nullable (e.g., `/users`)               |
| api_route           | varchar(255) | Nullable (e.g., `/api/users`)           |
| method              | varchar(10)  | Nullable (GET, POST, PUT, PATCH, DELETE)|
| menu_type           | varchar(20)  | menu, submenu, or none                  |
| icon                | varchar(255) | Nullable (e.g., `fa-users`)             |
| sort_order          | integer      | Default: 0                              |
| show_in_menu        | boolean      | Default: true                           |
| description         | text         | Nullable                                |
| is_active           | boolean      | Default: true                           |
| created_at          | timestamp    |                                         |
| updated_at          | timestamp    |                                         |

### role_user (Pivot)
| Column      | Type        | Notes                    |
|-------------|-------------|--------------------------|
| user_id     | bigint (FK) | References users.id      |
| role_id     | bigint (FK) | References roles.id      |
| is_active   | boolean     | Default: true (soft-toggle) |
| created_at  | timestamp   |                          |
| updated_at  | timestamp   |                          |

### privilege_role (Pivot)
| Column        | Type        | Notes                      |
|---------------|-------------|----------------------------|
| privilege_id  | bigint (FK) | References privileges.id   |
| role_id       | bigint (FK) | References roles.id        |

### password_reset_tokens
| Column      | Type         | Notes                            |
|-------------|--------------|----------------------------------|
| cnic_no     | varchar(13)  | Primary key                      |
| token       | varchar(255) | Hashed (bcrypt)                  |
| created_at  | timestamp    | Used for 60-min expiry check     |

---

## Models & Relationships

### User
| Relationship   | Type          | Target         | Description                        |
|----------------|---------------|----------------|------------------------------------|
| roles()        | BelongsToMany | Role           | All roles (including inactive)     |
| activeRoles()  | BelongsToMany | Role           | Only pivot `is_active = true`      |
| activeRole()   | BelongsTo     | Role           | Current active role via `active_role_id` |

**Key Methods:**
- `hasPrivilege(slug)` — Checks if the active role has a specific privilege
- `hasRole(slug)` — Checks if user has an active role by slug
- `getAllPrivileges()` — Returns all privileges for the active role
- `getMenuItems()` — Returns hierarchical menu tree for the active role

### Role
| Relationship   | Type          | Target    |
|----------------|---------------|-----------|
| users()        | BelongsToMany | User      |
| privileges()   | BelongsToMany | Privilege |

### Privilege
| Relationship        | Type          | Target         |
|---------------------|---------------|----------------|
| group()             | BelongsTo     | PrivilegeGroup |
| parent()            | BelongsTo     | Privilege      |
| children()          | HasMany       | Privilege      |
| childrenRecursive() | HasMany       | Privilege (recursive eager load) |
| menuChildren()      | HasMany       | Privilege (active, show_in_menu, sorted) |
| roles()             | BelongsToMany | Role           |

### PrivilegeGroup
| Relationship   | Type    | Target    |
|----------------|---------|-----------|
| privileges()   | HasMany | Privilege |

---

## Security

| Feature                | Implementation                                      |
|------------------------|-----------------------------------------------------|
| Authentication         | JWT Bearer token (HS256, 60-min TTL)                |
| Password Hashing       | bcrypt with 12 rounds                               |
| Password Policy        | Min 8 chars, mixed case, numbers required            |
| Rate Limiting          | 5 requests/min on login, forgot-password, reset      |
| Privilege Middleware    | Fail-closed — denies unregistered routes             |
| CORS                   | Only `FRONTEND_URL` origin allowed                   |
| Token Invalidation     | On logout and password change                        |
| User Enumeration       | Same response for all forgot-password outcomes        |
| LIKE Injection         | `%` and `_` wildcards escaped in search              |
| Mass Assignment        | `$request->only()` used everywhere                   |
| SQL Injection          | Eloquent parameterized queries                       |
| XSS                    | `e()` helper used in email HTML                      |

---

## Configuration

### Environment Variables (`.env`)

| Variable            | Default              | Description                           |
|---------------------|----------------------|---------------------------------------|
| DB_CONNECTION       | mariadb              | Database driver                       |
| DB_DATABASE         | laravel              | Database name                         |
| DB_USERNAME         | root                 | Database user                         |
| DB_PASSWORD         | (empty)              | Database password                     |
| FRONTEND_URL        | http://localhost:3000 | Frontend URL for CORS + redirects     |
| JWT_SECRET          | (generated)          | JWT signing secret                    |
| JWT_ALGO            | HS256                | JWT algorithm                         |
| SITE_NAME           | Admin Panel          | App name shown in sidebar/login       |
| SITE_SHORT_NAME     | AP                   | Short name for collapsed sidebar      |
| SITE_ICON           | shield               | Icon key: shield, lock, cube, bolt, star |
| SITE_PRIMARY_COLOR  | #4f46e5              | Primary brand color                   |
| MAIL_MAILER         | log                  | Email driver (log = `storage/logs/`)  |

### Key Commands

```bash
# Generate app key
php artisan key:generate

# Generate JWT secret
php artisan jwt:secret

# Run migrations + seed
php artisan migrate --seed

# Clear config cache
php artisan config:clear

# Start server
php artisan serve
```
