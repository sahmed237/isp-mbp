# ISP Management & Billing Platform (ISP-MBP)

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-13+-4169E1?style=for-the-badge&logo=postgresql&logoColor=white)](https://www.postgresql.org/)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.4-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![Alpine.js](https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?style=for-the-badge&logo=alpinedotjs&logoColor=white)](https://alpinejs.dev)
[![Tests](https://img.shields.io/badge/Tests-53%20Passed%20(263%20Assertions)-emerald?style=for-the-badge&logo=checkmarx&logoColor=white)](#automated-testing)

The **ISP Management & Billing Platform (ISP-MBP)** is a commercial-grade, multi-tenant Internet Service Provider operations, subscriber lifecycle, automated billing, network infrastructure, and AAA (Authentication, Authorization, Accounting) platform built with **Laravel 12** and **PostgreSQL**.

Designed to service thousands of subscribers across diverse network access technologies:
- **Fibre Optics** (FTTH / GPON / EPON / OLTs / ONUs)
- **Point-to-Point (PtP) & Point-to-Multipoint (PtMP) Wireless** (Towers, Sectors, Base Stations, Customer CPEs)
- **MikroTik RouterOS Hotspot** (Vouchers, captive portals, guest access, public instant purchase)
- **PPPoE** (Broadband subscriber authentication, dynamic queues, dynamic address pools)
- **Dedicated Leased Lines & Enterprise Carrier Interconnects**

Comparable to tier-1 platforms such as Splynx, NetOS, and AliRadius, engineered with strict tenant isolation, multi-guard authentication, role-based access control, and payment gateway forensics.

---

## Table of Contents

1. [Key Features & Capabilities](#key-features--capabilities)
2. [Portals & URL Directory](#portals--url-directory)
3. [System Architecture](#system-architecture)
4. [Technology Stack](#technology-stack)
5. [Prerequisites & Requirements](#prerequisites--requirements)
6. [Installation & Setup](#installation--setup)
7. [Default Seeded Credentials](#default-seeded-credentials)
8. [Automated Testing](#automated-testing)
9. [Payment Gateways & Forensics](#payment-gateways--forensics)
10. [FreeRADIUS Integration](#freeradius-integration)
11. [Security Mandates](#security-mandates)

---

## Key Features & Capabilities

### 1. Multi-Tenancy & Regional Branch Scoping
- **Organization Hierarchy**: Manage top-level ISP operating entities with complete data isolation.
- **Branch Management**: Physical and operational subdivisions (e.g., Abuja Central HQ, Yola Branch, Kano Hub).
- **Server-Side Tenant Scoping**: Query scopes and policy assertions prevent cross-tenant data leakage.

### 2. Subscriber Lifecycle Management
- **Account Types**: Individual, Corporate, Government, and Reseller.
- **Statuses**: Active, Suspended, Expired, Terminated, and Lead.
- **Customer Documents & KYC**: Identity cards, installation agreements, and CAC registration with secure upload, view, and download.
- **Locations & Customer Groups**: Physical installation coordinates (GPS latitude/longitude) and segmentation tags.

### 3. Bandwidth Profiles & Internet Packages
- **Speed Limits**: Granular download and upload caps (Kbps/Mbps).
- **Burst Profiles**: Burst download/upload, burst threshold, burst time.
- **Billing Cycles**: Monthly, quarterly, bi-annual, annual, or custom validity periods.
- **Connection Protocols**: PPPoE, Hotspot, Static IP, Dedicated Leased Lines.

### 4. Billing, Invoicing & Financials
- **Automated Invoicing**: System-generated recurring invoices with proration support.
- **Public Invoice Checkout (`/pay/invoice/{id}`)**: One-click invoice payment links and QR code resolution.
- **Payment Forensics**: Granular tracking of all checkout attempts (`payment_attempts` table), payload dumps, gateway references, and automatic invalidation of superseded attempts.
- **Printable Receipts & Invoices**: Professional, branded HTML/PDF print formats with payment breakdown and QR verification.

### 5. Hotspot Voucher & Dedicated Reseller Ecosystem
- **Public Self-Service Portal (`/hotspot`)**: Direct voucher purchase for guests and public users via card, bank transfer, or USSD.
- **Dedicated Reseller Portal (`/reseller/login`)**:
  - Independent authentication guard (`reseller`).
  - Self-service agent application (`/reseller/apply`) with Nigerian States dropdown (36 states + FCT Abuja) and interactive password security policy.
  - Admin approval/rejection workflow with KYC verification.
  - Prepaid reseller wallet system (`adjustWallet`, instant online top-up).
  - Bulk voucher generation with retail discounts.
  - Print-ready 3-column voucher distribution sheets with batch serials and cutting guides.

### 6. FreeRADIUS AAA & Network Infrastructure
- **FreeRADIUS Integration**: Real-time provisioning into `radcheck` and `radreply` tables.
- **Dynamic Access Control**: Automatic customer suspension via `Auth-Type := Reject` attribute and instant reconnection upon renewal.
- **NAS Client Management**: Manage MikroTik routers, Cisco gateways, and OLTs with IP addresses, shared secrets, and connection tests.

### 7. Customer Self-Service Portal (`/portal/login`)
- Dedicated subscriber portal for viewing active subscriptions, bandwidth quotas, data usage, invoice history, payment receipts, and profile settings.

### 8. Granular RBAC & Immutable Security Audit Trail
- **Spatie Laravel Permission**: 8 pre-configured operational roles:
  - *Super Administrator*, *Organization Administrator*, *Operations Manager*, *Billing Manager*, *Network Administrator*, *Customer Support*, *Accountant*, *Read Only*.
- **Dynamic Sidebar**: Navigation links render strictly based on user permissions and operational roles.
- **Append-Only Audit Logs**: Comprehensive records of actions, actor IP addresses, user agents, and before/after attribute state diffs.

---

## Portals & URL Directory

| Portal / Module | URL | Description | Default Guard |
| :--- | :--- | :--- | :--- |
| **Admin Operations Panel** | `/login` or `/dashboard` | ISP staff and management operations panel | `web` |
| **Public Hotspot Purchase** | `/hotspot` | Public portal for purchasing Wi-Fi hotspot vouchers | *Guest* |
| **Reseller Portal Login** | `/reseller/login` | Authorized voucher agent & merchant portal | `reseller` |
| **Reseller Agent Application** | `/reseller/apply` | Prospective voucher distributor self-onboarding | *Guest* |
| **Customer Self-Service** | `/portal/login` | Subscriber dashboard for invoices, subscriptions, and profile | `customer` |
| **Public Invoice Payment** | `/pay/invoice/{identifier}` | Direct invoice payment with gateway selection & QR code | *Guest* |
| **FreeRADIUS AAA Engine** | `/radius` | NAS clients, RADIUS attributes, and subscriber AAA states | `web` |

---

## System Architecture

```text
┌────────────────────────────────────────────────────────────────────────┐
│                        Presentation Layer                              │
│   Blade Components / Tailwind CSS / Alpine.js / Responsive UI / APIs   │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
┌───────────────────────────────────▼────────────────────────────────────┐
│                       HTTP & Routing Layer                             │
│   Multi-Guard Auth (web, customer, reseller) / RBAC Gates / Policies   │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
┌───────────────────────────────────▼────────────────────────────────────┐
│                    Application Service Layer                           │
│   CustomerService, PackageService, HotspotVoucherService,             │
│   PaymentGatewayFactory, FreeRadiusService, AuditService               │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
┌───────────────────────────────────▼────────────────────────────────────┐
│                  Domain & Data Layer (PostgreSQL)                      │
│   Multi-tenant Eloquent Models, JSONB, Foreign Keys, Scopes, Observers │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
┌───────────────────────────────────▼────────────────────────────────────┐
│                     External Integration Layer                         │
│  - FreeRADIUS Database (radcheck, radreply, nas)                       │
│  - MikroTik RouterOS API / REST v7 Service                             │
│  - Payment Gateways (Paystack, Flutterwave, Moniepoint, Monnify)       │
└────────────────────────────────────────────────────────────────────────┘
```

---

## Technology Stack

- **Framework**: Laravel 12.x
- **Runtime**: PHP 8.4
- **Database**: PostgreSQL 13+ (supports MySQL/SQLite for local development)
- **Frontend Architecture**: Blade Components, Tailwind CSS, Alpine.js 3.x, JetBrains Mono & Plus Jakarta Sans typography
- **Authentication**: Multi-guard session authentication (`web`, `customer`, `reseller`)
- **Access Control**: Spatie Laravel Permission with organizational context
- **Testing**: PHPUnit / Pest with in-memory transaction rollbacks

---

## Prerequisites & Requirements

- **PHP**: `^8.2` (PHP 8.4 recommended)
  - Extensions: `pdo_pgsql` (or `pdo_mysql`), `curl`, `mbstring`, `openssl`, `tokenizer`, `xml`, `bcmath`
- **Composer**: `^2.5`
- **PostgreSQL**: `^13.0` (or MySQL 8.0+)
- **Node.js & npm**: Node 18+ (for compiling assets via Vite)

---

## Installation & Setup

### 1. Clone the Repository
```bash
git clone https://github.com/sahmed237/isp-mbp.git
cd isp-mbp
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Configure Environment Variables
Copy `.env.example` to `.env`:
```bash
cp .env.example .env
```

Configure your database and app keys in `.env`:
```env
APP_NAME="ISP-MBP"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=isp_mbp
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

Generate the application encryption key:
```bash
php artisan key:generate
```

### 4. Run Migrations & Seeders
Execute migrations along with comprehensive seeders (roles, permissions, organizations, branches, network devices, and demo accounts):
```bash
php artisan migrate --seed
```

Create the public storage symlink for uploaded customer documents and KYC files:
```bash
php artisan storage:link
```

### 5. Build Assets & Start Development Server
```bash
npm run build
php artisan serve
```

Visit [http://localhost:8000](http://localhost:8000) in your browser.

---

## Default Seeded Credentials

All demo accounts use the standard password: **`Password123!`**

### 1. Administrative Staff Accounts (`/login`)
| Role | Email | Password | Access Level |
| :--- | :--- | :--- | :--- |
| **Super Administrator** | `superadmin@isp-mbp.local` | `Password123!` | System-wide full root access |
| **Organization Administrator** | `orgadmin@isp-mbp.local` | `Password123!` | Full tenant organization control |
| **Operations Manager** | `opsmanager@isp-mbp.local` | `Password123!` | Operational workflows & customers |
| **Billing Manager** | `billing@isp-mbp.local` | `Password123!` | Invoices, payments, and financial adjustments |
| **Network Administrator** | `netadmin@isp-mbp.local` | `Password123!` | Routers, switches, OLTs, and RADIUS AAA |
| **Customer Support** | `support@isp-mbp.local` | `Password123!` | Ticket handling and subscriber inquiries |
| **Accountant** | `accountant@isp-mbp.local` | `Password123!` | Invoices, payment reconciliation & reporting |
| **Read Only / Auditor** | `auditor@isp-mbp.local` | `Password123!` | View-only access across platform resources |

### 2. Reseller Portal (`/reseller/login`)
- Log in with **Reseller Code**, **Email**, or **Phone Number**.
- Prospective agents can apply online at `/reseller/apply`. Applications are immediately reviewed by administrators at `/resellers`.

### 3. Customer Portal (`/portal/login`)
- Log in with **Account Number** (e.g., `CUST-xxx`), **Email**, or **Phone Number**.

---

## Automated Testing

The platform includes a test suite covering authentication, multi-tenant isolation, RBAC permissions, payment attempts, invoice checkouts, FreeRADIUS provisioning, and reseller workflows.

Run the test suite with:
```bash
php artisan test
```

### Current Test Suite Status
```text
Pass: 53 tests, 263 assertions (100% passing)
Duration: ~37s
```

Key test feature suites:
- `Tests\Feature\DedicatedResellerManagementAndPortalTest`
- `Tests\Feature\HotspotVoucherResellerAndPublicPortalTest`
- `Tests\Feature\PaymentAttemptForensicsTest`
- `Tests\Feature\RadiusManagementTest`
- `Tests\Feature\MultiTenantIsolationTest`
- `Tests\Feature\AuthorizationTest`
- `Tests\Feature\AuditLogTest`

---

## Payment Gateways & Forensics

The billing engine supports multiple payment providers:
- **Paystack** (Cards, Bank Transfer, USSD)
- **Moniepoint** (Business Banking & POS)
- **Monnify** (Virtual Accounts & Direct Debit)
- **Flutterwave** (Cards & Alternative Channels)

### Forensics Architecture
Every checkout attempt initializes a record in `payment_attempts` with a unique reference. When a customer initiates a new checkout, any preceding uncompleted attempts are automatically marked as `superseded` / `cancelled`, preventing duplicate charges and reconciliation errors. Full raw API payloads and error responses are archived for troubleshooting.

---

## FreeRADIUS Integration

Subscriber lifecycle actions trigger FreeRADIUS synchronization:
- **Customer Creation**: Automatically provisions `Cleartext-Password` in `radcheck` and bandwidth limit attributes in `radreply` (`Mikrotik-Rate-Limit`).
- **Customer Suspension**: Immediately injects `Auth-Type := Reject` into `radcheck`, denying network access within milliseconds.
- **Customer Reactivation / Renewal**: Removes rejection entries and restores assigned bandwidth rate limits.
- **Customer Deletion**: Completely purges RADIUS user records.

---

## Security Mandates

1. **Server-Side Authorization**: Every administrative controller enforces `$this->authorize(...)` and policy checks.
2. **Strict Multi-Tenancy**: Organization IDs are verified server-side; direct object manipulation across tenants triggers HTTP 403.
3. **Immutable Auditing**: Audit logs are append-only. There is no route or UI to edit or delete security audit trails.
4. **Password Security**: Bcrypt hashing with >= 12 rounds. Interactive password strength validation enforces uppercase, lowercase, numbers, and symbols.
5. **Rate Limiting**: Authentication and checkout endpoints are protected by rate limiters (e.g. 5 attempts/minute).
6. **Encrypted Secrets**: RouterOS credentials, RADIUS secrets, and payment API keys are encrypted at rest using Laravel's encryption engine.

---

## License

This software is licensed under the [MIT License](LICENSE).
