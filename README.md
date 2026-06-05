# Laravel REST API Boilerplate

Production-ready Laravel 11 REST API boilerplate with RBAC, 2FA, activity logs, and full user management.

---

## Features

| Category | What's Included |
|---|---|
| **Auth** | Register, Login, Logout (single + all devices), Email Verification, Token Refresh |
| **Password** | Forgot Password, Reset Password (token-based, 1-min throttle) |
| **2FA** | TOTP via Google Authenticator, Recovery Codes, Enable/Disable |
| **Profile** | View/Update Profile, Avatar Upload/Delete, Change Password, Active Sessions |
| **Users** | CRUD, Soft Delete, Restore, Force Delete, Status Management, Bulk Actions |
| **Roles** | CRUD, Sync Permissions, List Role Users |
| **Permissions** | CRUD, Grouped by Module |
| **Menus** | Tree structure, Role/Permission-gated menus, Drag-and-drop Reorder |
| **Activity Logs** | Spatie ActivityLog — who did what, when |
| **RBAC** | Spatie Permissions — role + permission middleware on every route |
| **Security** | Token blacklist, Suspended-user guard, Rate limiting per-endpoint |

---

## Stack

- **Laravel 11** + PHP 8.2
- **Laravel Sanctum** — API token auth
- **Spatie Laravel-Permission** — RBAC
- **Spatie Laravel-Activitylog** — Audit trail
- **MySQL 8** + **Redis**
- **Docker** + **Nginx**
- **PestPHP** — Tests
- **GitHub Actions** — CI/CD

---

## Quick Start

### 1. Clone & Install

```bash
git clone https://github.com/yourorg/laravel-api-boilerplate.git
cd laravel-api-boilerplate
composer install
cp .env.example .env
php artisan key:generate
```

### 2. Configure `.env`

```env
DB_DATABASE=laravel_api
DB_USERNAME=root
DB_PASSWORD=your_password

REDIS_HOST=127.0.0.1

MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=1025
```

### 3. Migrate & Seed

```bash
php artisan make:cache-table
php artisan migrate
php artisan db:seed
```

### 4. Start

```bash
php artisan serve
# API available at: http://localhost:8000/api/v1
```

---

## Docker Setup

```bash
cp .env.example .env
# Set DB_HOST=mysql, REDIS_HOST=redis in .env

docker-compose up -d
docker-compose exec app php artisan migrate --seed
```

---

## Seeded Accounts

| Role | Email | Password |
|---|---|---|
| super-admin | superadmin@example.com | SuperAdmin@123 |
| admin | admin@example.com | Admin@123 |
| manager | manager@example.com | Manager@123 |
| user | user@example.com | User@123 |

---

## API Reference

### Authentication

```
POST   /api/v1/auth/register
POST   /api/v1/auth/login
POST   /api/v1/auth/logout
POST   /api/v1/auth/logout-all
POST   /api/v1/auth/refresh
GET    /api/v1/auth/me
GET    /api/v1/auth/verify-email/{token}
POST   /api/v1/auth/resend-verification
```

### Password

```
POST   /api/v1/auth/forgot-password
POST   /api/v1/auth/reset-password
```

### Two-Factor Auth

```
POST   /api/v1/auth/2fa/enable
POST   /api/v1/auth/2fa/verify
POST   /api/v1/auth/2fa/disable
POST   /api/v1/auth/2fa/recovery
```

### Profile

```
GET    /api/v1/profile
PUT    /api/v1/profile
POST   /api/v1/profile/avatar
DELETE /api/v1/profile/avatar
PUT    /api/v1/profile/password
GET    /api/v1/profile/activity
GET    /api/v1/profile/sessions
DELETE /api/v1/profile/sessions/{tokenId}
```

### Users *(requires `manage-users` permission)*

```
GET    /api/v1/users              ?search= &status= &role= &sort= &order= &per_page=
POST   /api/v1/users
GET    /api/v1/users/{id}
PUT    /api/v1/users/{id}
DELETE /api/v1/users/{id}
POST   /api/v1/users/{id}/restore
DELETE /api/v1/users/{id}/force
PUT    /api/v1/users/{id}/status
POST   /api/v1/users/{id}/roles
POST   /api/v1/users/{id}/permissions
POST   /api/v1/users/{id}/impersonate
POST   /api/v1/users/bulk-action   { action: activate|deactivate|suspend|delete, ids: [] }
```

### Roles *(requires `manage-roles` permission)*

```
GET    /api/v1/roles
POST   /api/v1/roles
GET    /api/v1/roles/{id}
PUT    /api/v1/roles/{id}
DELETE /api/v1/roles/{id}
POST   /api/v1/roles/{id}/permissions
GET    /api/v1/roles/{id}/users
```

### Permissions *(requires `manage-permissions` permission)*

```
GET    /api/v1/permissions
POST   /api/v1/permissions
GET    /api/v1/permissions/{id}
PUT    /api/v1/permissions/{id}
DELETE /api/v1/permissions/{id}
GET    /api/v1/permissions/grouped
```

### Menus

```
GET    /api/v1/menus              (manage-menus | view-menus)
POST   /api/v1/menus              (manage-menus)
GET    /api/v1/menus/tree
GET    /api/v1/menus/my-menus     (role-filtered for current user)
PUT    /api/v1/menus/reorder      (manage-menus)
GET    /api/v1/menus/{id}
PUT    /api/v1/menus/{id}         (manage-menus)
DELETE /api/v1/menus/{id}         (manage-menus)
```

### Activity Logs *(requires `view-activity-logs` permission)*

```
GET    /api/v1/activity-logs      ?causer_id= &log_name= &event= &date_from= &date_to=
GET    /api/v1/activity-logs/{id}
```

---

## Response Format

All responses follow a consistent envelope:

```json
{
  "success": true,
  "message": "Success",
  "data": { ... },
  "meta": { "current_page": 1, "total": 100, ... },
  "links": { "next": "...", "prev": "..." }
}
```

### Error Format

```json
{
  "success": false,
  "message": "Validation failed.",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

---

## Role Hierarchy

```
super-admin  →  bypasses ALL permission checks (Gate::before)
admin        →  all permissions except impersonate
manager      →  view users/roles, manage menus, view logs
user         →  view dashboard only
```

---

## Running Tests

```bash
# All tests
php artisan test

# With coverage
php artisan test --coverage --min=80

# Parallel
php artisan test --parallel

# Single suite
php artisan test --testsuite=Feature
```

---

## What You Might Also Want to Add

| Feature | Package / Approach |
|---|---|
| API Documentation | `knuckleswtf/scribe` — auto-generates from docblocks |
| OAuth2 Social Login | `laravel/socialite` — Google, GitHub, etc. |
| Audit / Changelog | Already have activitylog; add `owen-it/laravel-auditing` for field-level diffs |
| Notifications Centre | Database + broadcast notifications |
| File Management | `spatie/laravel-medialibrary` |
| Settings / Config | `spatie/laravel-settings` |
| Localization / i18n | Laravel's built-in `Lang::` + `spatie/laravel-translatable` |
| Feature Flags | `laravel/pennant` |
| Search | `laravel/scout` + MeiliSearch |
| Horizon (queue dashboard) | `laravel/horizon` |
| Telescope (debugging) | `laravel/telescope` |
| Health Checks | `spatie/laravel-health` |
| Multi-tenancy | `stancl/tenancy` |

---

## Security Checklist

- [x] Passwords hashed with bcrypt (auto via `hashed` cast)
- [x] Tokens expire (configurable via `SANCTUM_TOKEN_EXPIRATION`)
- [x] Token blacklist on password change / account suspend
- [x] Login throttle: 5 attempts/minute per email+IP
- [x] Suspended users blocked at middleware level
- [x] Super-admin cannot be deleted (system role guard)
- [x] Users cannot delete themselves
- [x] Soft deletes — data is never permanently lost without `force`
- [x] All API routes force `Accept: application/json`
- [x] 2FA with encrypted secrets + disposable recovery codes
- [x] Password reset tokens are hashed + expire after 60 min
- [ ] Add HTTPS (enforce via Nginx / load balancer in production)
- [ ] Add `Content-Security-Policy` headers for web frontends



# Laravel REST API Boilerplate

Production-ready Laravel 11 REST API boilerplate with RBAC, 2FA, activity logs, and full user management.

---

## Features

| Category | What's Included |
|---|---|
| **Auth** | Register, Login, Logout (single + all devices), Email Verification, Token Refresh |
| **Password** | Forgot Password, Reset Password (token-based, 1-min throttle) |
| **2FA** | TOTP via Google Authenticator, Recovery Codes, Enable/Disable |
| **Profile** | View/Update Profile, Avatar Upload/Delete, Change Password, Active Sessions |
| **Users** | CRUD, Soft Delete, Restore, Force Delete, Status Management, Bulk Actions |
| **Roles** | CRUD, Sync Permissions, List Role Users |
| **Permissions** | CRUD, Grouped by Module |
| **Menus** | Tree structure, Role/Permission-gated menus, Drag-and-drop Reorder |
| **Activity Logs** | Spatie ActivityLog — who did what, when |
| **RBAC** | Spatie Permissions — role + permission middleware on every route |
| **Security** | Token blacklist, Suspended-user guard, Rate limiting per-endpoint |

---

## Stack

- **Laravel 11** + PHP 8.2
- **Laravel Sanctum** — API token auth
- **Spatie Laravel-Permission** — RBAC
- **Spatie Laravel-Activitylog** — Audit trail
- **MySQL 8** + **Redis**
- **Docker** + **Nginx**
- **PestPHP** — Tests
- **GitHub Actions** — CI/CD

---

## Quick Start

### 1. Clone & Install

```bash
git clone https://github.com/yourorg/laravel-api-boilerplate.git
cd laravel-api-boilerplate
composer install
cp .env.example .env
php artisan key:generate
```

### 2. Configure `.env`

```env
DB_DATABASE=laravel_api
DB_USERNAME=root
DB_PASSWORD=your_password

REDIS_HOST=127.0.0.1

MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=1025
```

### 3. Migrate & Seed

```bash
php artisan migrate
php artisan db:seed
```

### 4. Start

```bash
php artisan serve
# API available at: http://localhost:8000/api/v1
```

---

## Docker Setup

```bash
cp .env.example .env
# Set DB_HOST=mysql, REDIS_HOST=redis in .env

docker-compose up -d
docker-compose exec app php artisan migrate --seed
```

---

## Seeded Accounts

| Role | Email | Password |
|---|---|---|
| super-admin | superadmin@example.com | SuperAdmin@123 |
| admin | admin@example.com | Admin@123 |
| manager | manager@example.com | Manager@123 |
| user | user@example.com | User@123 |

---

## API Reference

### Authentication

```
POST   /api/v1/auth/register
POST   /api/v1/auth/login
POST   /api/v1/auth/logout
POST   /api/v1/auth/logout-all
POST   /api/v1/auth/refresh
GET    /api/v1/auth/me
GET    /api/v1/auth/verify-email/{token}
POST   /api/v1/auth/resend-verification
```

### Password

```
POST   /api/v1/auth/forgot-password
POST   /api/v1/auth/reset-password
```

### Two-Factor Auth

```
POST   /api/v1/auth/2fa/enable
POST   /api/v1/auth/2fa/verify
POST   /api/v1/auth/2fa/disable
POST   /api/v1/auth/2fa/recovery
```

### Profile

```
GET    /api/v1/profile
PUT    /api/v1/profile
POST   /api/v1/profile/avatar
DELETE /api/v1/profile/avatar
PUT    /api/v1/profile/password
GET    /api/v1/profile/activity
GET    /api/v1/profile/sessions
DELETE /api/v1/profile/sessions/{tokenId}
```

### Users *(requires `manage-users` permission)*

```
GET    /api/v1/users              ?search= &status= &role= &sort= &order= &per_page=
POST   /api/v1/users
GET    /api/v1/users/{id}
PUT    /api/v1/users/{id}
DELETE /api/v1/users/{id}
POST   /api/v1/users/{id}/restore
DELETE /api/v1/users/{id}/force
PUT    /api/v1/users/{id}/status
POST   /api/v1/users/{id}/roles
POST   /api/v1/users/{id}/permissions
POST   /api/v1/users/{id}/impersonate
POST   /api/v1/users/bulk-action   { action: activate|deactivate|suspend|delete, ids: [] }
```

### Roles *(requires `manage-roles` permission)*

```
GET    /api/v1/roles
POST   /api/v1/roles
GET    /api/v1/roles/{id}
PUT    /api/v1/roles/{id}
DELETE /api/v1/roles/{id}
POST   /api/v1/roles/{id}/permissions
GET    /api/v1/roles/{id}/users
```

### Permissions *(requires `manage-permissions` permission)*

```
GET    /api/v1/permissions
POST   /api/v1/permissions
GET    /api/v1/permissions/{id}
PUT    /api/v1/permissions/{id}
DELETE /api/v1/permissions/{id}
GET    /api/v1/permissions/grouped
```

### Menus

```
GET    /api/v1/menus              (manage-menus | view-menus)
POST   /api/v1/menus              (manage-menus)
GET    /api/v1/menus/tree
GET    /api/v1/menus/my-menus     (role-filtered for current user)
PUT    /api/v1/menus/reorder      (manage-menus)
GET    /api/v1/menus/{id}
PUT    /api/v1/menus/{id}         (manage-menus)
DELETE /api/v1/menus/{id}         (manage-menus)
```

### Activity Logs *(requires `view-activity-logs` permission)*

```
GET    /api/v1/activity-logs      ?causer_id= &log_name= &event= &date_from= &date_to=
GET    /api/v1/activity-logs/{id}
```

---

## Response Format

All responses follow a consistent envelope:

```json
{
  "success": true,
  "message": "Success",
  "data": { ... },
  "meta": { "current_page": 1, "total": 100, ... },
  "links": { "next": "...", "prev": "..." }
}
```

### Error Format

```json
{
  "success": false,
  "message": "Validation failed.",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

---

## Role Hierarchy

```
super-admin  →  bypasses ALL permission checks (Gate::before)
admin        →  all permissions except impersonate
manager      →  view users/roles, manage menus, view logs
user         →  view dashboard only
```

---

## Running Tests

```bash
# All tests
php artisan test

# With coverage
php artisan test --coverage --min=80

# Parallel
php artisan test --parallel

# Single suite
php artisan test --testsuite=Feature
```

---

## What You Might Also Want to Add

| Feature | Package / Approach |
|---|---|
| API Documentation | `knuckleswtf/scribe` — auto-generates from docblocks |
| OAuth2 Social Login | `laravel/socialite` — Google, GitHub, etc. |
| Audit / Changelog | Already have activitylog; add `owen-it/laravel-auditing` for field-level diffs |
| Notifications Centre | Database + broadcast notifications |
| File Management | `spatie/laravel-medialibrary` |
| Settings / Config | `spatie/laravel-settings` |
| Localization / i18n | Laravel's built-in `Lang::` + `spatie/laravel-translatable` |
| Feature Flags | `laravel/pennant` |
| Search | `laravel/scout` + MeiliSearch |
| Horizon (queue dashboard) | `laravel/horizon` |
| Telescope (debugging) | `laravel/telescope` |
| Health Checks | `spatie/laravel-health` |
| Multi-tenancy | `stancl/tenancy` |

---

## Security Checklist

- [x] Passwords hashed with bcrypt (auto via `hashed` cast)
- [x] Tokens expire (configurable via `SANCTUM_TOKEN_EXPIRATION`)
- [x] Token blacklist on password change / account suspend
- [x] Login throttle: 5 attempts/minute per email+IP
- [x] Suspended users blocked at middleware level
- [x] Super-admin cannot be deleted (system role guard)
- [x] Users cannot delete themselves
- [x] Soft deletes — data is never permanently lost without `force`
- [x] All API routes force `Accept: application/json`
- [x] 2FA with encrypted secrets + disposable recovery codes
- [x] Password reset tokens are hashed + expire after 60 min
- [ ] Add HTTPS (enforce via Nginx / load balancer in production)
- [ ] Add `Content-Security-Policy` headers for web frontends

---

## API Usage Guide

### Postman Setup

1. Create a new **Collection** → `Base API`
2. Add a collection variable: `base_url` = `http://localhost:8000/api/v1`
3. After login, copy the token and set it as collection variable `token`
4. Go to Collection → **Authorization** tab → Type: `Bearer Token` → Value: `{{token}}`
5. All requests in the collection will automatically send the token

**Auto-login Pre-request Script** (add to Collection → Pre-request Script):
```javascript
pm.sendRequest({
    url: pm.variables.get("base_url") + "/auth/login",
    method: "POST",
    header: { "Content-Type": "application/json" },
    body: {
        mode: "raw",
        raw: JSON.stringify({
            email: "admin@example.com",
            password: "Admin@123"
        })
    }
}, (err, res) => {
    pm.collectionVariables.set("token", res.json().data.token);
});
```

---

### Authentication Examples

**Register**
```http
POST {{base_url}}/auth/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "Password@123",
    "password_confirmation": "Password@123"
}
```

**Login**
```http
POST {{base_url}}/auth/login
Content-Type: application/json

{
    "email": "admin@example.com",
    "password": "Admin@123"
}
```

Response:
```json
{
    "success": true,
    "message": "Login successful.",
    "data": {
        "user": { "id": 1, "name": "Admin User", "email": "admin@example.com", "roles": ["admin"] },
        "token": "5|abc123xyz...",
        "token_type": "Bearer",
        "expires_at": "2026-04-27T01:00:00+00:00"
    }
}
```

**Get Current User**
```http
GET {{base_url}}/auth/me
Authorization: Bearer {{token}}
```

**Logout Current Device**
```http
POST {{base_url}}/auth/logout
Authorization: Bearer {{token}}
```

**Logout All Devices**
```http
POST {{base_url}}/auth/logout-all
Authorization: Bearer {{token}}
```

**Forgot Password**
```http
POST {{base_url}}/auth/forgot-password
Content-Type: application/json

{
    "email": "john@example.com"
}
```

**Reset Password**
```http
POST {{base_url}}/auth/reset-password
Content-Type: application/json

{
    "token": "token-from-email",
    "email": "john@example.com",
    "password": "NewPassword@123",
    "password_confirmation": "NewPassword@123"
}
```

---

### Profile Examples

**View Profile**
```http
GET {{base_url}}/profile
Authorization: Bearer {{token}}
```

**Update Profile**
```http
PUT {{base_url}}/profile
Authorization: Bearer {{token}}
Content-Type: application/json

{
    "name": "John Updated",
    "phone": "+911234567890",
    "timezone": "Asia/Kolkata",
    "locale": "en"
}
```

**Change Password**
```http
PUT {{base_url}}/profile/password
Authorization: Bearer {{token}}
Content-Type: application/json

{
    "current_password": "Admin@123",
    "password": "NewAdmin@123",
    "password_confirmation": "NewAdmin@123"
}
```

**Upload Avatar**
```http
POST {{base_url}}/profile/avatar
Authorization: Bearer {{token}}
Content-Type: multipart/form-data

avatar: [select image file, max 2MB]
```

**Delete Avatar**
```http
DELETE {{base_url}}/profile/avatar
Authorization: Bearer {{token}}
```

**View Active Sessions**
```http
GET {{base_url}}/profile/sessions
Authorization: Bearer {{token}}
```

**Revoke a Session**
```http
DELETE {{base_url}}/profile/sessions/{tokenId}
Authorization: Bearer {{token}}
```

---

### User Management Examples

**List Users** (with filters)
```http
GET {{base_url}}/users?search=john&status=active&role=admin&per_page=10&sort=created_at&order=desc
Authorization: Bearer {{token}}
```

**Create User**
```http
POST {{base_url}}/users
Authorization: Bearer {{token}}
Content-Type: application/json

{
    "name": "New User",
    "email": "newuser@example.com",
    "password": "Password@123",
    "password_confirmation": "Password@123",
    "phone": "+911234567890",
    "status": "active",
    "roles": ["user"]
}
```

**Update User**
```http
PUT {{base_url}}/users/{id}
Authorization: Bearer {{token}}
Content-Type: application/json

{
    "name": "Updated Name",
    "status": "inactive"
}
```

**Soft Delete User**
```http
DELETE {{base_url}}/users/{id}
Authorization: Bearer {{token}}
```

**Restore Deleted User**
```http
POST {{base_url}}/users/{id}/restore
Authorization: Bearer {{token}}
```

**Update User Status**
```http
PUT {{base_url}}/users/{id}/status
Authorization: Bearer {{token}}
Content-Type: application/json

{
    "status": "suspended"
}
```
> Available statuses: `active`, `inactive`, `suspended`

**Assign Roles to User**
```http
POST {{base_url}}/users/{id}/roles
Authorization: Bearer {{token}}
Content-Type: application/json

{
    "roles": ["admin", "manager"]
}
```

**Assign Permissions to User**
```http
POST {{base_url}}/users/{id}/permissions
Authorization: Bearer {{token}}
Content-Type: application/json

{
    "permissions": ["view-users", "manage-menus"]
}
```

**Bulk Action**
```http
POST {{base_url}}/users/bulk-action
Authorization: Bearer {{token}}
Content-Type: application/json

{
    "action": "suspend",
    "ids": [2, 3, 4]
}
```
> Available actions: `activate`, `deactivate`, `suspend`, `delete`

---

### Role Examples

**List Roles**
```http
GET {{base_url}}/roles
Authorization: Bearer {{token}}
```

**Create Role**
```http
POST {{base_url}}/roles
Authorization: Bearer {{token}}
Content-Type: application/json

{
    "name": "editor",
    "permissions": ["view-users", "view-menus", "view-dashboard"]
}
```

**Sync Permissions to Role**
```http
POST {{base_url}}/roles/{id}/permissions
Authorization: Bearer {{token}}
Content-Type: application/json

{
    "permissions": ["view-users", "manage-menus", "view-dashboard"]
}
```

**List Users in a Role**
```http
GET {{base_url}}/roles/{id}/users
Authorization: Bearer {{token}}
```

---

### Permission Examples

**List All Permissions**
```http
GET {{base_url}}/permissions
Authorization: Bearer {{token}}
```

**List Grouped by Module**
```http
GET {{base_url}}/permissions/grouped
Authorization: Bearer {{token}}
```

Response:
```json
{
    "data": {
        "users":     [{ "id": 1, "name": "manage-users" }, { "id": 2, "name": "view-users" }],
        "roles":     [{ "id": 6, "name": "manage-roles" }],
        "menus":     [{ "id": 11, "name": "manage-menus" }],
        "dashboard": [{ "id": 15, "name": "view-dashboard" }]
    }
}
```

**Create Permission**
```http
POST {{base_url}}/permissions
Authorization: Bearer {{token}}
Content-Type: application/json

{
    "name": "export-reports",
    "group": "reports"
}
```

---

### Menu Examples

**Get Menu Tree**
```http
GET {{base_url}}/menus/tree
Authorization: Bearer {{token}}
```

Response:
```json
{
    "data": [
        {
            "id": 1, "name": "Dashboard", "url": "/dashboard",
            "icon": "home", "order": 1, "children": []
        },
        {
            "id": 2, "name": "User Management", "url": null, "icon": "users",
            "children": [
                { "id": 3, "name": "Users",       "url": "/users" },
                { "id": 4, "name": "Roles",       "url": "/roles" },
                { "id": 5, "name": "Permissions", "url": "/permissions" }
            ]
        }
    ]
}
```

**Get My Menus** (role-filtered — use this to build your frontend sidebar dynamically)
```http
GET {{base_url}}/menus/my-menus
Authorization: Bearer {{token}}
```

**Create Menu**
```http
POST {{base_url}}/menus
Authorization: Bearer {{token}}
Content-Type: application/json

{
    "name": "Reports",
    "url": "/reports",
    "icon": "bar-chart",
    "type": "sidebar",
    "order": 6,
    "is_active": true,
    "roles": [1, 2]
}
```

**Create Child Menu**
```http
POST {{base_url}}/menus
Authorization: Bearer {{token}}
Content-Type: application/json

{
    "name": "Sales Report",
    "url": "/reports/sales",
    "icon": "trending-up",
    "parent_id": 6,
    "order": 1
}
```

**Reorder Menus**
```http
PUT {{base_url}}/menus/reorder
Authorization: Bearer {{token}}
Content-Type: application/json

{
    "items": [
        { "id": 1, "order": 1, "parent_id": null },
        { "id": 2, "order": 2, "parent_id": null },
        { "id": 3, "order": 1, "parent_id": 2 }
    ]
}
```

---

### Activity Log Examples

**List Logs** (with filters)
```http
GET {{base_url}}/activity-logs?causer_id=1&date_from=2026-01-01&date_to=2026-12-31
Authorization: Bearer {{token}}
```

**Get Single Log**
```http
GET {{base_url}}/activity-logs/{id}
Authorization: Bearer {{token}}
```

---

### Two-Factor Authentication Examples

**Step 1 — Enable 2FA** (returns QR code)
```http
POST {{base_url}}/auth/2fa/enable
Authorization: Bearer {{token}}
```

Response:
```json
{
    "data": {
        "qr_code_url": "otpauth://totp/AppName:user@example.com?secret=BASE32SECRET",
        "secret": "BASE32SECRET",
        "recovery_codes": ["ABCDE-FGHIJ", "KLMNO-PQRST"]
    },
    "message": "Scan the QR code with your authenticator app, then confirm with a code."
}
```

**Step 2 — Confirm 2FA** (enter the 6-digit code from your authenticator app)
```http
POST {{base_url}}/auth/2fa/verify
Authorization: Bearer {{token}}
Content-Type: application/json

{
    "code": "123456"
}
```

**Disable 2FA**
```http
POST {{base_url}}/auth/2fa/disable
Authorization: Bearer {{token}}
Content-Type: application/json

{
    "password": "Admin@123"
}
```

**Use Recovery Code** (when you lose your authenticator device)
```http
POST {{base_url}}/auth/2fa/recovery
Authorization: Bearer {{token}}
Content-Type: application/json

{
    "code": "ABCDE-FGHIJ"
}
```