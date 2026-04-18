# Starter Project — Laravel + React RBAC Admin Panel

A fullstack admin panel with **Role-Based Access Control (RBAC)**, JWT authentication, dynamic menus, and a modern React SPA frontend.

## Tech Stack

| Layer     | Technology                                                   |
| --------- | ------------------------------------------------------------ |
| Backend   | Laravel 11, PHP 8.2+, JWT Auth (php-open-source-saver)      |
| Frontend  | React 19, Vite 6, Tailwind CSS v4, React Router 7, Axios    |
| Database  | MySQL / MariaDB                                              |
| Dev Tools | XAMPP (or any PHP + MySQL setup)                             |

## Features

- JWT Authentication (Login, Logout, Refresh Token)
- Role-Based Access Control (RBAC) with dynamic privileges
- Active Role system — one role per session, switchable
- Dynamic sidebar menu driven by privileges
- Dual-route protection (frontend + backend URL matching)
- User, Role, and Privilege CRUD management
- Change Password & Forgot Password (CNIC-based)
- Configurable app name, icon, and branding via `.env`
- Toast notifications (react-hot-toast)
- Responsive design with collapsible sidebar

## Project Structure

```
fullstack-app/
├── backend/             # Laravel 11 API
│   ├── app/Http/        # Controllers, Middleware
│   ├── config/site.php  # App branding config
│   ├── database/        # Migrations & Seeders
│   ├── routes/          # api.php, web.php
│   └── .env.example
├── frontend/            # React SPA
│   ├── src/
│   │   ├── components/  # Sidebar, Topbar, AppIcon, etc.
│   │   ├── context/     # AuthContext, AppConfigContext
│   │   ├── layouts/     # DashboardLayout
│   │   └── pages/       # All CRUD & auth pages
│   ├── vite.config.js
│   └── package.json
└── README.md
```

---

## Prerequisites

- **PHP** >= 8.2
- **Composer** (https://getcomposer.org)
- **Node.js** >= 18 & **npm** (https://nodejs.org)
- **MySQL** or **MariaDB** (XAMPP works great on Windows)

---

## Setup Instructions

### 1. Clone the Repository

```bash
git clone https://github.com/kashifshaikh-uos/starter-project.git
cd starter-project
```

### 2. Backend Setup

```bash
cd backend

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Generate JWT secret
php artisan jwt:secret
```

### 3. Configure Database

Open `backend/.env` and update your database credentials:

```env
DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=
```

> Create the database `laravel` in phpMyAdmin or MySQL CLI before proceeding.

### 4. Run Migrations & Seed

```bash
php artisan migrate --seed
```

This will create all tables and seed:

- **Super Admin** role with full privileges
- **Admin user** — CNIC: `0000000000000`, Password: `password`
- Settings menu with User, Role, and Privilege management

### 5. Start Backend Server

```bash
php artisan serve
```

Backend will run at `http://localhost:8000`

### 6. Frontend Setup

Open a **new terminal**:

```bash
cd frontend

# Install Node dependencies
npm install

# Start dev server
npm run dev
```

Frontend will run at `http://localhost:3000`

### 7. Login

Open `http://localhost:3000` in your browser:

| Field    | Value           |
| -------- | --------------- |
| CNIC     | 1234567890123   |
| Password | password        |

---

## Configuration

App branding is configurable via `backend/.env`:

```env
SITE_NAME="Admin Panel"
SITE_SHORT_NAME="AP"
SITE_ICON="shield"
SITE_PRIMARY_COLOR="#4f46e5"
```

Available icons: `shield`, `lock`, `cube`, `bolt`, `star`

The frontend URL for redirects:

```env
FRONTEND_URL=http://localhost:3000
```

---

## API Routes

All API routes are prefixed with `/api`.

| Method | Endpoint                           | Auth | Description               |
| ------ | ---------------------------------- | ---- | ------------------------- |
| GET    | /app-config                        | No   | Get app branding config   |
| POST   | /login                             | No   | Login with CNIC/password  |
| POST   | /forgot-password                   | No   | Send password reset email |
| POST   | /reset-password                    | No   | Reset password via token  |
| GET    | /me                                | Yes  | Get current user + menu   |
| POST   | /logout                            | Yes  | Logout                    |
| POST   | /refresh                           | Yes  | Refresh JWT token         |
| POST   | /change-password                   | Yes  | Change password           |
| POST   | /change-role                       | Yes  | Switch active role        |
| GET    | /users                             | Yes  | List users                |
| POST   | /users                             | Yes  | Create user               |
| GET    | /users/{id}                        | Yes  | Show user                 |
| PUT    | /users/{id}                        | Yes  | Update user               |
| POST   | /users/{id}/assign-roles           | Yes  | Assign roles to user      |
| GET    | /roles                             | Yes  | List roles                |
| POST   | /roles                             | Yes  | Create role               |
| GET    | /roles/{id}                        | Yes  | Show role                 |
| PUT    | /roles/{id}                        | Yes  | Update role               |
| POST   | /roles/{id}/assign-privileges      | Yes  | Assign privileges to role |
| GET    | /privileges                        | Yes  | List privileges           |
| POST   | /privileges                        | Yes  | Create privilege          |
| GET    | /privileges/{id}                   | Yes  | Show privilege            |
| PUT    | /privileges/{id}                   | Yes  | Update privilege          |

---

## License

MIT