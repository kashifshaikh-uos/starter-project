# Frontend Documentation

Complete guide to the React SPA frontend architecture, components, and pages.

---

## Table of Contents

- [Tech Stack](#tech-stack)
- [Project Structure](#project-structure)
- [Setup & Development](#setup--development)
- [Architecture Overview](#architecture-overview)
- [Entry Point](#entry-point)
- [API Layer](#api-layer)
- [Contexts](#contexts)
  - [AuthContext](#authcontext)
  - [AppConfigContext](#appconfigcontext)
- [Routing](#routing)
- [Layouts](#layouts)
- [Components](#components)
  - [Sidebar](#sidebar)
  - [Topbar](#topbar)
  - [ProtectedRoute](#protectedroute)
  - [PrivilegeRoute](#privilegeroute)
  - [AppIcon](#appicon)
  - [MenuIcon](#menuicon)
- [Pages](#pages)
  - [Login](#login)
  - [Dashboard](#dashboard)
  - [UserList / UserCreate / UserEdit](#user-management-pages)
  - [RoleList / RoleCreate / RoleEdit](#role-management-pages)
  - [PrivilegeList / PrivilegeCreate / PrivilegeEdit](#privilege-management-pages)
  - [ChangePassword](#changepassword)
  - [ChangeRole](#changerole)
  - [ForgotPassword](#forgotpassword)
  - [ResetPassword](#resetpassword)
- [State Management](#state-management)
- [Toast Notifications](#toast-notifications)
- [Styling](#styling)
- [Build & Deployment](#build--deployment)
- [Adding a New Page (Guide)](#adding-a-new-page-guide)

---

## Tech Stack

| Package           | Version | Purpose                          |
|-------------------|---------|----------------------------------|
| React             | 19.x    | UI library                       |
| React Router DOM  | 7.x     | Client-side routing (SPA)        |
| Axios             | 1.x     | HTTP client for API calls        |
| Tailwind CSS      | 4.x     | Utility-first CSS framework      |
| Vite              | 6.x     | Build tool & dev server          |
| react-hot-toast   | 2.x     | Toast notifications              |

---

## Project Structure

```
frontend/
├── index.html              # HTML entry point
├── package.json            # Dependencies & scripts
├── vite.config.js          # Vite config with proxy
├── src/
│   ├── main.jsx            # React root + providers
│   ├── App.jsx             # Route definitions
│   ├── api.js              # Axios instance + interceptors
│   ├── index.css            # Tailwind CSS import
│   ├── context/
│   │   ├── AuthContext.jsx    # Auth state (user, menu, privileges)
│   │   └── AppConfigContext.jsx # App branding config
│   ├── layouts/
│   │   └── DashboardLayout.jsx # Sidebar + Topbar + content
│   ├── components/
│   │   ├── Sidebar.jsx      # Collapsible sidebar with menus
│   │   ├── Topbar.jsx       # Header with user dropdown
│   │   ├── ProtectedRoute.jsx # Auth guard
│   │   ├── PrivilegeRoute.jsx # Privilege guard
│   │   ├── AppIcon.jsx      # Dynamic app icon
│   │   └── MenuIcon.jsx     # Menu icon mapper
│   └── pages/
│       ├── Login.jsx
│       ├── Dashboard.jsx
│       ├── UserList.jsx
│       ├── UserCreate.jsx
│       ├── UserEdit.jsx
│       ├── RoleList.jsx
│       ├── RoleCreate.jsx
│       ├── RoleEdit.jsx
│       ├── PrivilegeList.jsx
│       ├── PrivilegeCreate.jsx
│       ├── PrivilegeEdit.jsx
│       ├── ChangePassword.jsx
│       ├── ChangeRole.jsx
│       ├── ForgotPassword.jsx
│       └── ResetPassword.jsx
```

---

## Setup & Development

```bash
cd frontend

# Install dependencies
npm install

# Start dev server (port 3000)
npm run dev

# Build for production
npm run build

# Preview production build
npm run preview
```

### Vite Dev Server Proxy

The Vite dev server proxies `/api` requests to the Laravel backend:

```js
// vite.config.js
server: {
  port: 3000,
  proxy: {
    '/api': {
      target: 'http://localhost:8000',
      changeOrigin: true,
      secure: false,
    },
  },
}
```

This means in development:
- Frontend runs at `http://localhost:3000`
- API calls to `/api/*` are forwarded to `http://localhost:8000/api/*`
- No CORS issues in development

---

## Architecture Overview

```
┌─────────────────────────────────────────────────────┐
│  BrowserRouter                                       │
│  ┌─────────────────────────────────────────────────┐ │
│  │  AppConfigProvider (fetches /api/app-config)     │ │
│  │  ┌─────────────────────────────────────────────┐ │ │
│  │  │  AuthProvider (manages user/menu/privileges) │ │ │
│  │  │  ┌─────────────────────────────────────────┐ │ │ │
│  │  │  │  App (route definitions)                 │ │ │ │
│  │  │  │  ┌─────────────────────────────────────┐ │ │ │ │
│  │  │  │  │  DashboardLayout                     │ │ │ │ │
│  │  │  │  │  ┌──────┐ ┌──────────────────────┐  │ │ │ │ │
│  │  │  │  │  │Sidebar│ │ Topbar               │  │ │ │ │ │
│  │  │  │  │  │      │ │ ┌──────────────────┐ │  │ │ │ │ │
│  │  │  │  │  │      │ │ │ <Outlet /> (page) │ │  │ │ │ │ │
│  │  │  │  │  │      │ │ └──────────────────┘ │  │ │ │ │ │
│  │  │  │  │  └──────┘ └──────────────────────┘  │ │ │ │ │
│  │  │  │  └─────────────────────────────────────┘ │ │ │ │
│  │  │  └─────────────────────────────────────────┘ │ │ │
│  │  └─────────────────────────────────────────────┘ │ │
│  └─────────────────────────────────────────────────┘ │
│  Toaster (react-hot-toast, top-right)                │
└─────────────────────────────────────────────────────┘
```

---

## Entry Point

### `main.jsx`

```jsx
<BrowserRouter>
  <AppConfigProvider>     // Fetches branding from /api/app-config
    <AuthProvider>        // Manages auth state
      <App />             // Routes
      <Toaster />         // Global toast notifications
    </AuthProvider>
  </AppConfigProvider>
</BrowserRouter>
```

**Provider order matters:**
1. `BrowserRouter` — enables routing
2. `AppConfigProvider` — loads app name/icon (no auth needed)
3. `AuthProvider` — checks localStorage token, fetches user if exists

---

## API Layer

### `api.js`

Centralized Axios instance used by all pages and contexts.

**Base URL:** `/api` (proxied to backend in development)

**Request Interceptor:**
- Reads JWT token from `localStorage` key `token`
- Attaches `Authorization: Bearer <token>` header to every request

**Response Interceptor Error Handling:**

| Status | Action                                          |
|--------|-------------------------------------------------|
| 401    | Clear token, redirect to `/login`               |
| 403    | Show toast: "You do not have permission..."     |
| 422    | Pass through (pages handle validation errors)   |
| 500+   | Show toast: "Server error..."                   |
| Network| Show toast: "Network error..."                  |

**Usage in pages:**
```jsx
import api from '../api';

// GET request
const { data } = await api.get('/users');

// POST request
await api.post('/users', { name: 'John', cnic_no: '1234567890123', password: 'Password1' });

// PUT request
await api.put(`/users/${id}`, { name: 'Updated' });
```

---

## Contexts

### AuthContext

Provides authentication state and methods to the entire app.

**State:**
| State       | Type     | Description                                |
|-------------|----------|--------------------------------------------|
| user        | object   | Current user object (null if not logged in) |
| menu        | array    | Dynamic menu tree from active role          |
| privileges  | array    | Array of privilege slugs (strings)          |
| loading     | boolean  | True while checking auth on app load        |

**Methods:**

| Method       | Params              | Description                                                |
|--------------|---------------------|------------------------------------------------------------|
| `login()`    | cnic_no, password   | POST /login, store token, set user, fetch menu/privileges  |
| `logout()`   | —                   | POST /logout, clear token, clear state                     |
| `fetchUser()`| —                   | GET /me, refresh user/menu/privileges                      |
| `hasPrv()`   | slug (string)       | Returns true if user has this privilege slug                |

**Usage:**
```jsx
import { useAuth } from '../context/AuthContext';

function MyComponent() {
  const { user, menu, privileges, login, logout, hasPrv, fetchUser } = useAuth();

  if (hasPrv('create-user')) {
    // Show create button
  }
}
```

**Login Flow:**
1. `login(cnic, password)` → POST `/api/login`
2. Store `access_token` in localStorage
3. Set `user` in state
4. Call `fetchUser()` → GET `/api/me`
5. Set `menu` and `privileges` from response
6. Navigate to `/`

**App Load Flow:**
1. Check if `token` exists in localStorage
2. If yes → call `fetchUser()` to validate token
3. If token invalid → clear localStorage, set user to null
4. If no token → set loading to false (show login)

---

### AppConfigContext

Provides app branding configuration (loaded once on app start).

**State:**
| Field         | Type   | Default        | Description                    |
|---------------|--------|----------------|--------------------------------|
| name          | string | "Admin Panel"  | Full app name                  |
| short_name    | string | "AP"           | Short name (collapsed sidebar) |
| icon          | string | "shield"       | Icon key for AppIcon component |
| primary_color | string | "#4f46e5"      | Brand color                    |

**Usage:**
```jsx
import { useAppConfig } from '../context/AppConfigContext';

function MyComponent() {
  const appConfig = useAppConfig();
  return <h1>{appConfig.name}</h1>;
}
```

**Side Effect:** Sets `document.title` to `config.name` when loaded.

---

## Routing

### Route Structure

```
/login                  → Login page (public)
/forgot-password        → Forgot password (public)
/reset-password         → Reset password (public, with token+cnic in URL)

/                       → Dashboard (protected)
/change-password        → Change password (protected, no privilege check)
/change-role            → Change role (protected, no privilege check)

/users                  → User list (protected, privilege: user-list)
/users/create           → Create user (protected, privilege: create-user)
/users/:id/edit         → Edit user (protected, privilege: update-user)

/roles                  → Role list (protected, privilege: role-list)
/roles/create           → Create role (protected, privilege: create-role)
/roles/:id/edit         → Edit role (protected, privilege: update-role)

/privileges             → Privilege list (protected, privilege: privilege-list)
/privileges/create      → Create privilege (protected, privilege: create-privilege)
/privileges/:id/edit    → Edit privilege (protected, privilege: create-privilege)

*                       → Redirect to /
```

### Route Protection Layers

1. **Public routes** (`/login`, `/forgot-password`, `/reset-password`): If user is already logged in, redirects to `/`.
2. **ProtectedRoute**: Checks if `user` exists in AuthContext. If not, redirects to `/login`.
3. **PrivilegeRoute**: Checks if user has a specific privilege slug. If not, shows "Access Denied" UI (no redirect).

---

## Layouts

### DashboardLayout

The main layout wrapper for all authenticated pages. Uses React Router's `<Outlet />`.

**Structure:**
```
┌──────────────────────────────────────────────┐
│ ┌──────────┐ ┌─────────────────────────────┐ │
│ │          │ │ Topbar                       │ │
│ │ Sidebar  │ ├─────────────────────────────┤ │
│ │          │ │                             │ │
│ │          │ │ <Outlet /> (current page)   │ │
│ │          │ │                             │ │
│ │          │ │                             │ │
│ └──────────┘ └─────────────────────────────┘ │
└──────────────────────────────────────────────┘
```

**State:**
| State       | Type    | Description                                |
|-------------|---------|--------------------------------------------|
| sidebarOpen | boolean | Mobile sidebar visibility                  |
| collapsed   | boolean | Desktop sidebar collapsed state            |

---

## Components

### Sidebar

Dynamic sidebar with recursive menu support, collapsible on desktop, overlay on mobile.

**Props:**
| Prop       | Type     | Description                           |
|------------|----------|---------------------------------------|
| menu       | array    | Menu items from AuthContext            |
| open       | boolean  | Mobile sidebar open state             |
| collapsed  | boolean  | Desktop collapsed state               |
| onClose    | function | Close mobile sidebar                  |
| onCollapse | function | Toggle desktop collapse               |

**Features:**
- **Expanded mode:** Full sidebar (w-64) with text labels, expandable parent menus with chevron
- **Collapsed mode:** Icons only (w-16), hover popup menus via `createPortal`
- **Mobile:** Full-width overlay with close button
- **Dashboard link:** Always visible at top (hardcoded, not from menu API)
- **App branding:** Logo icon + app name from AppConfigContext

**Menu item structure (from API):**
```json
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
      "frontend_route": "/users",
      "icon": "fa-users",
      "menu_children": []
    }
  ]
}
```

**Internal Components:**
- `ExpandedMenuItem` — Recursive component for expanded sidebar items
- `CollapsedMenuItem` — Icon button with Portal popup for collapsed items
- `FlyoutMenu` / `FlyoutParent` — Nested popup menus for collapsed state

---

### Topbar

Header bar with mobile toggle, desktop collapse toggle, and user dropdown.

**Props:**
| Prop         | Type     | Description                    |
|--------------|----------|--------------------------------|
| onMenuToggle | function | Open mobile sidebar            |
| collapsed    | boolean  | Desktop sidebar collapsed      |
| onCollapse   | function | Toggle desktop sidebar         |

**User Dropdown:**
- User name + CNIC
- Current active role name (e.g., "Role: Super Admin")
- "Change Password" link → `/change-password`
- "Change Role" link → `/change-role` (only visible if user has 2+ active roles)
- "Sign Out" button → calls `logout()`

---

### ProtectedRoute

Simple auth guard wrapper component.

```jsx
<ProtectedRoute>
  <DashboardLayout />
</ProtectedRoute>
```

**Logic:**
- If `loading` → show spinner
- If `!user` → redirect to `/login`
- Otherwise → render children

---

### PrivilegeRoute

Privilege-based access guard for individual pages.

```jsx
<PrivilegeRoute slug="user-list">
  <UserList />
</PrivilegeRoute>
```

**Logic:**
- Calls `hasPrv(slug)` from AuthContext
- If user lacks the privilege → shows "Access Denied" card (not a redirect)
- If user has the privilege → renders children

---

### AppIcon

Renders the app icon based on `config.icon` from AppConfigContext.

**Supported icon keys:** `shield`, `lock`, `cube`, `bolt`, `star`

```jsx
<AppIcon className="w-8 h-8 text-white" />
```

---

### MenuIcon

Maps icon strings from the database to SVG icons for sidebar menus.

**Supported icons:** `fa-cog`, `fa-users`, `fa-shield`, `fa-key`, `fa-list`, `fa-user-plus`, `fa-plus`, `fa-chart`, and more.

---

## Pages

### Login

**Route:** `/login`  
**Auth:** Public (redirects to `/` if already logged in)

**Features:**
- CNIC input (13 digits, numeric only)
- Password input
- "Forgot Password?" link
- App name and icon from AppConfigContext
- Error messages displayed inline
- Submits to `login()` from AuthContext

---

### Dashboard

**Route:** `/`  
**Auth:** Protected (no privilege check)

Simple welcome page showing the authenticated user's name.

---

### User Management Pages

#### UserList
**Route:** `/users` — **Privilege:** `user-list`

- Paginated table with search
- Columns: Name, CNIC, Email, Status, Roles, Actions
- "Create New User" button (visible if `hasPrv('create-user')`)
- Edit button per row (visible if `hasPrv('update-user')`)
- Deactivate/Activate toggle per row
- Search by name, CNIC, or email

#### UserCreate
**Route:** `/users/create` — **Privilege:** `create-user`

Form fields:
- Name (required)
- CNIC (required, 13 digits)
- Email (optional)
- Phone (optional)
- Password (required, min 8, mixed case + number)
- Is Active checkbox
- Role assignment checkboxes (fetched from `/api/roles`)

On submit → POST `/api/users`, then POST `/api/users/{id}/assign-roles`

#### UserEdit
**Route:** `/users/:id/edit` — **Privilege:** `update-user`

Same form as create but pre-filled. Password is optional (leave blank to keep current).

On submit → PUT `/api/users/{id}`, then POST `/api/users/{id}/assign-roles`

---

### Role Management Pages

#### RoleList
**Route:** `/roles` — **Privilege:** `role-list`

- Table with: Name, Slug, Description, Status, Users Count, Actions
- Create + Edit buttons (privilege-gated)

#### RoleCreate
**Route:** `/roles/create` — **Privilege:** `create-role`

Form fields:
- Name (required)
- Slug (auto-generated, editable)
- Description (optional)
- Is Active checkbox
- Privilege assignment checkboxes (grouped by privilege group)

On submit → POST `/api/roles`, then POST `/api/roles/{id}/assign-privileges`

#### RoleEdit
**Route:** `/roles/:id/edit` — **Privilege:** `update-role`

Same as create but pre-filled. Existing privilege assignments shown as checked.

---

### Privilege Management Pages

#### PrivilegeList
**Route:** `/privileges` — **Privilege:** `privilege-list`

- Table with: Name, Slug, Group, Route, Method, Menu Type, Status
- Create + Edit buttons

#### PrivilegeCreate
**Route:** `/privileges/create` — **Privilege:** `create-privilege`

Form fields:
- Name, Slug (auto-generated)
- Privilege Group (dropdown)
- Parent Privilege (dropdown)
- Frontend Route, API Route, Method
- Menu Type (menu/submenu/none)
- Icon, Sort Order
- Show In Menu checkbox
- Description, Is Active

#### PrivilegeEdit
**Route:** `/privileges/:id/edit` — **Privilege:** `create-privilege`

Same as create but pre-filled.

---

### ChangePassword

**Route:** `/change-password`  
**Auth:** Protected (no privilege check — every user can change their password)

Form fields:
- Current Password
- New Password (min 8, mixed case + number)
- Confirm New Password

On success → token is invalidated, user is redirected to `/login`.

---

### ChangeRole

**Route:** `/change-role`  
**Auth:** Protected (no privilege check)

- Shows current active role at the top
- Radio-card list of all user's active roles
- "Current" badge on the currently active role
- Submit calls POST `/api/change-role`
- On success → `fetchUser()` refreshes menu/privileges → redirect to `/`

---

### ForgotPassword

**Route:** `/forgot-password`  
**Auth:** Public

- CNIC input (13 digits)
- Submit → POST `/api/forgot-password`
- Always shows success message (anti-enumeration)
- "Back to Login" link

---

### ResetPassword

**Route:** `/reset-password?token=xxx&cnic=xxx`  
**Auth:** Public

- Auto-reads `token` and `cnic` from URL params
- Displays CNIC (read-only)
- New Password + Confirm Password fields
- Submit → POST `/api/reset-password`
- On success → "Back to Login" link

---

## State Management

This app uses **React Context** for global state (no Redux/Zustand needed).

| Context           | Scope    | Data                              |
|-------------------|----------|-----------------------------------|
| AuthContext       | Global   | user, menu, privileges, auth flow |
| AppConfigContext  | Global   | name, short_name, icon, color     |

**Local state** (useState) is used in each page for form data, loading states, and error messages. No shared state between pages.

**Data refresh pattern:**
```
User action → API call → success toast → navigate or refetch
                       → error toast → show inline errors
```

---

## Toast Notifications

Using `react-hot-toast` globally configured in `main.jsx`.

**Position:** top-right  
**Duration:** 4 seconds  
**Styling:** Rounded corners (12px), 14px font

**Global toasts (from api.js interceptor):**
- 403 errors → automatic error toast
- 500+ errors → automatic error toast
- Network errors → automatic error toast

**Page-level toasts:**
```jsx
import toast from 'react-hot-toast';

// Success
toast.success('User created successfully');

// Error
toast.error('Failed to create user');
```

**Note:** 422 validation errors are NOT auto-toasted — pages handle them inline.

---

## Styling

- **Framework:** Tailwind CSS v4 (via `@tailwindcss/vite` plugin)
- **Import:** `@import "tailwindcss"` in `index.css`
- **Design system:**
  - Primary color: Indigo (600/700)
  - Sidebar: Slate-900 dark theme
  - Cards: White with rounded-2xl, shadow-sm, border-gray-100
  - Buttons: Gradient (indigo-600 to indigo-700), rounded-xl
  - Forms: Full-width inputs with focus:ring-2 ring-indigo-500
  - Responsive: Mobile-first with lg: breakpoints

---

## Build & Deployment

```bash
# Build for production
npm run build

# Output to frontend/dist/
# Serve dist/ folder with any static file server
```

**Production considerations:**
- Update the Vite proxy or configure your web server to forward `/api` to the backend
- Set `FRONTEND_URL` in backend `.env` to the production frontend URL
- For SPA routing, configure your web server to serve `index.html` for all routes

---

## Adding a New Page (Guide)

Step-by-step guide for adding a new feature page:

### 1. Create the Backend

```php
// Create migration
php artisan make:migration create_reports_table

// Create model
php artisan make:model Report

// Create controller
php artisan make:controller ReportController --api

// Add routes to routes/api.php (inside the protected group)
Route::apiResource('reports', ReportController::class)->except(['destroy']);
```

### 2. Register Privileges

Add privileges via the Privilege Management UI or seeder:

| Name          | Slug          | API Route      | Method | Frontend Route | Menu Type |
|---------------|---------------|----------------|--------|----------------|-----------|
| Report List   | report-list   | /api/reports   | GET    | /reports       | submenu   |
| Create Report | create-report | /api/reports   | POST   | /reports/create| none      |
| Update Report | update-report | /api/reports/{id} | PUT | —              | none      |

Assign the privileges to the appropriate roles.

### 3. Create Frontend Pages

```jsx
// frontend/src/pages/ReportList.jsx
import { useState, useEffect } from 'react';
import api from '../api';

export default function ReportList() {
  const [reports, setReports] = useState([]);

  useEffect(() => {
    api.get('/reports').then(({ data }) => setReports(data.data));
  }, []);

  return (
    <div>
      <h1>Reports</h1>
      {/* ... table ... */}
    </div>
  );
}
```

### 4. Add Routes to App.jsx

```jsx
import ReportList from './pages/ReportList';
import ReportCreate from './pages/ReportCreate';
import ReportEdit from './pages/ReportEdit';

// Inside the protected layout routes:
<Route path="reports" element={<PrivilegeRoute slug="report-list"><ReportList /></PrivilegeRoute>} />
<Route path="reports/create" element={<PrivilegeRoute slug="create-report"><ReportCreate /></PrivilegeRoute>} />
<Route path="reports/:id/edit" element={<PrivilegeRoute slug="update-report"><ReportEdit /></PrivilegeRoute>} />
```

### 5. Test

1. Login as admin
2. Assign the new privileges to your role
3. Refresh the page → menu should update
4. Navigate to the new pages

**That's it!** The middleware, CORS, auth, and menu system handle everything automatically.
