# ISP Management & Billing Platform — Developer Practical Guide

This guide describes how to install, configure, develop, test, and maintain the ISP-MBP platform.

---

## 1. Development Workflow

All contributions must follow the strict development lifecycle:

```text
Understand ──> Plan ──> Implement ──> Test ──> Review ──> Document
```

1. **Understand**: Inspect requirements, existing domain entities, and database constraints.
2. **Plan**: Formulate schema changes, permission requirements, policy checks, and UI specifications.
3. **Implement**: Write migrations, models, policies, service classes, controllers, and Blade templates.
4. **Test**: Write automated PHPUnit/Pest authorization and functional tests.
5. **Review**: Check security (IDOR, CSRF, server-side policies, immutable audit logging).
6. **Document**: Update `GEMINI.md` and `GUIDE.md` when introducing new modules or conventions.

---

## 2. Installation & Environment Setup

### Prerequisites
- PHP >= 8.2 with `pdo_pgsql`, `mbstring`, `openssl`, `bcmath`, `curl`, `intl`, `zip`
- PostgreSQL >= 13
- Composer >= 2.x
- Node.js >= 18 & npm

### Setup Steps

```bash
# 1. Clone the repository and navigate into it
cd c:\wamp64\www\isp-mbp

# 2. Configure environment file
copy .env.example .env

# 3. Configure PostgreSQL credentials in .env:
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=isp_mbp
# DB_USERNAME=postgres
# DB_PASSWORD=postgres

# 4. Generate Application Key
php artisan key:generate

# 5. Run Migrations & Seeders
php artisan migrate:fresh --seed

# 6. Install Frontend Assets & Build
npm install
npm run build
```

---

## 3. Running the Application

### Local Web Server
```bash
php artisan serve
```
Access the admin portal at `http://localhost:8000/login`.

### Background Queues
```bash
php artisan queue:work --tries=3 --timeout=90
```

### Task Scheduler (Cron)
On Windows Task Scheduler or cron:
```bash
php artisan schedule:run
```

---

## 4. Default Seeded Accounts

| Role | Email | Password | Scope |
| :--- | :--- | :--- | :--- |
| **Super Administrator** | `superadmin@isp-mbp.local` | `Password123!` | System-wide / Cross-tenant |
| **Organization Administrator** | `orgadmin@isp-mbp.local` | `Password123!` | Organization Wide |
| **Operations Manager** | `opsmanager@isp-mbp.local` | `Password123!` | Ops, Customers, Packages |
| **Billing Manager** | `billing@isp-mbp.local` | `Password123!` | Invoices, Payments, Accounts |
| **Network Administrator** | `netadmin@isp-mbp.local` | `Password123!` | Routers, MikroTik, Infrastructure |
| **Customer Support** | `support@isp-mbp.local` | `Password123!` | Customers, Subscriptions, Tickets |
| **Accountant** | `accountant@isp-mbp.local` | `Password123!` | Financial Reports, View Invoices |
| **Read Only** | `readonly@isp-mbp.local` | `Password123!` | View-Only Access |

---

## 5. Roles & Permissions Architecture

Permissions follow the standard convention:
```text
resource.action
```
Examples:
- `customers.view`, `customers.create`, `customers.update`, `customers.delete`, `customers.export`
- `packages.view`, `packages.create`, `packages.update`, `packages.delete`
- `network.devices.view`, `network.devices.create`, `network.devices.update`, `network.devices.delete`
- `organizations.view`, `organizations.create`, `organizations.update`, `organizations.delete`
- `users.view`, `users.create`, `users.update`, `users.delete`
- `roles.view`, `roles.create`, `roles.update`, `roles.delete`
- `audit_logs.view`

### Adding a New Permission
1. Add permission string to `Database\Seeders\RolesAndPermissionsSeeder`.
2. Assign it to applicable roles.
3. Update policy mapping.
4. Run `php artisan db:seed --class=RolesAndPermissionsSeeder`.

---

## 6. Creating a New Domain Module

When creating a new module (e.g. `Tickets`):

1. **Migration**:
   ```bash
   php artisan make:migration create_tickets_table
   ```
   *Must include `organization_id` (foreign key) and `branch_id` (nullable foreign key).*

2. **Model**:
   ```bash
   php artisan make:model Ticket
   ```
   Add relationship `belongsTo(Organization::class)` and define `$casts`.

3. **Policy**:
   ```bash
   php artisan make:policy TicketPolicy --model=Ticket
   ```
   Enforce both permission check AND organization isolation:
   ```php
   public function update(User $user, Ticket $ticket): bool
   {
       if (!$user->can('tickets.update')) {
           return false;
       }
       return $user->isSuperAdmin() || $user->organization_id === $ticket->organization_id;
   }
   ```

4. **Form Request**:
   Create `StoreTicketRequest` and `UpdateTicketRequest`.

5. **Controller**:
   Use `$this->authorizeResource(Ticket::class)` or `$this->authorize('update', $ticket)`.

6. **View**:
   Create Blade templates in `resources/views/admin/tickets/`.

7. **Audit Logging**:
   Trigger `AuditLog::record(...)` upon create/update/delete.

8. **Test**:
   Write Pest or PHPUnit tests verifying authorization and organization isolation.

---

## 7. Running Tests & Code Quality

```bash
# Run all tests
php artisan test

# Run only authorization tests
php artisan test --filter=AuthorizationTest

# Run code style formatting (Laravel Pint)
./vendor/bin/pint
```
