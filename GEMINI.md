# ISP Management & Billing Platform (ISP-MBP) — Engineering Architecture & Guidelines

## 1. Project Overview

The **ISP Management & Billing Platform (ISP-MBP)** is a commercial-grade, multi-tenant/multi-organization Internet Service Provider management, automated billing, customer lifecycle, network infrastructure, and AAA (Authentication, Authorization, Accounting) platform built with Laravel and PostgreSQL.

The platform is designed to manage large-scale ISP operations servicing thousands of subscribers across diverse network access technologies:
- **Fibre Optics** (FTTH / GPON / EPON / OLTs / ONUs)
- **Point-to-Point (PtP) Wireless Links** (Backhaul & high-capacity enterprise links)
- **Point-to-Multipoint (PtMP) Wireless** (Base stations, sectors, customer CPEs)
- **MikroTik RouterOS Hotspot** (Vouchers, captive portals, guest access)
- **PPPoE** (Broadband subscriber authentication & dynamic queue assignment)
- **Customer Premises Equipment (CPE)** & Managed Customer Routers
- **Dedicated Leased Lines & Carrier Interconnects**

The system provides capabilities comparable to tier-1 commercial ISP platforms such as Splynx, NetOS, and AliRadius, engineered with strict security, server-side tenant isolation, and modular maintainability.

---

## 2. System Architecture

The application adopts a modular, domain-driven layered architecture:

```text
┌─────────────────────────────────────────────────────────────┐
│                 Presentation Layer (UI & API)               │
│   Blade Components / Tailwind CSS / Alpine.js / REST APIs   │
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│           HTTP Layer (Routing, Middleware & Policies)       │
│   Auth, OrganizationScope, RBAC Gates, RateLimiter, Audits  │
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│                Application Service Layer                    │
│   CustomerService, PackageService, NetworkDeviceService,    │
│   AuditService, TenantScopeService                          │
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│           Domain & Data Layer (Eloquent & PostgreSQL)       │
│   Multi-tenant Models, Scopes, JSONB, Indexes, FKs          │
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│          Future External Integration Abstraction Layers     │
│  - FreeRADIUS / DaloRADIUS Adapter Layer                    │
│  - MikroTik RouterOS API / RouterOS REST v7 Adapter         │
│  - Payment Gateways (Paystack, Flutterwave, Moniepoint)     │
└─────────────────────────────────────────────────────────────┘
```

### Core Application Domains

1. **Organization & Multi-Tenancy**:
   - `Organization`: Top-level legal or operating entity (tenant).
   - `Branch`: Operational physical or regional subdivision within an organization (e.g., Abuja Branch, Yola Branch, Kano Branch).
   - Server-side multi-tenancy scoping via query scopes and policy enforcement.
2. **Access Control & RBAC**:
   - Granular permissions mapped strictly in `resource.action` syntax.
   - Built on Spatie Laravel Permission with organization context.
   - 8 Standard operational roles: Super Administrator, Organization Administrator, Operations Manager, Billing Manager, Network Administrator, Customer Support, Accountant, Read Only.
3. **Audit Trails & Security**:
   - Immutable audit logging tracking who changed what, previous values, new values, client IP, and user agent.
4. **Customer Lifecycle**:
   - Account types (Individual, Corporate, Government, Reseller).
   - Status transitions (Lead, Active, Suspended, Expired, Terminated).
   - Documents, physical installation addresses, coordinates, and contact persons.
5. **Services & Internet Packages**:
   - Bandwidth definitions (Download, Upload in Kbps/Mbps).
   - Burst configurations (Burst Download, Burst Upload, Burst Threshold, Burst Time).
   - Billing cycles (Monthly, Quarterly, Bi-annual, Annual, Custom validity days).
   - Connection technologies (PPPoE, Hotspot, Fibre, Wireless, Dedicated).
6. **Network Infrastructure**:
   - Devices (MikroTik Routers, Core Routers, Access Switches, OLTs, Wireless Towers, APs, CPEs).
   - Device credentials, IP management, SNMP/API status indicators.
7. **Billing & Financials (Phases 2-3)**:
   - Automated recurring invoicing, prorated billing, automated payment reconciliation, payment gateway webhooks.
8. **AAA & Provisioning (Phases 4-5)**:
   - RADIUS attribute mapping (`Framed-IP-Address`, `Mikrotik-Rate-Limit`).
   - MikroTik RouterOS API / SSH / REST service layer.

---

## 3. Technology Stack

- **Framework**: Laravel 12.x / PHP 8.4
- **Database**: PostgreSQL 13+ with schema constraints, UUIDs, and foreign keys.
- **Frontend**: Blade components, Tailwind CSS, Alpine.js, Lucide/Heroicons SVG.
- **Security & RBAC**: Spatie Laravel Permission, Laravel Policies, Session Guard, CSRF tokens, strict rate limiting.
- **Build Tooling**: Vite with Tailwind CSS and modern asset pipeline.
- **Background Processing**: Database queues and task scheduler.

---

## 4. Coding Standards & Conventions

### PHP & Laravel Standards
- Follow **PSR-12** code style guidelines strictly.
- Strict type declarations (`declare(strict_types=1);`) where applicable.
- Explicit return types and parameter type hints on all methods and functions.

### Architecture Rules
1. **Controllers**:
   - Thin controllers. Business logic resides in Application Services (`app/Services`).
   - Controllers handle HTTP validation, dispatch to service, and return View or JsonResponse.
   - Every mutating route MUST be protected by Form Requests (`app/Http/Requests`) and Policies (`app/Policies`).
2. **Models & Scoping**:
   - All tenant-owned models MUST implement `BelongsToOrganization` trait or define `organization_id` foreign key.
   - Use Global/Local Scopes (`TenantScope`) to prevent unintentional cross-tenant leakage.
   - Use soft deletes (`SoftDeletes`) on business-critical entities (Customers, Packages, Invoices).
3. **Form Requests**:
   - Every store/update action must have a dedicated Form Request class.
   - Authorize method in Form Requests must delegate to policy or gate.
4. **Events & Listeners**:
   - State-changing operations (Customer Created, Package Changed, User Logged In) dispatch domain events.
   - Audit logging is hooked via observers and event listeners.

---

## 5. Security Mandates

1. **Backend Authorization is Absolute**:
   - Frontend visibility hiding (e.g. `@can`) is purely ergonomic.
   - Every single administrative controller action MUST enforce `$this->authorize(...)` or middleware check.
2. **Prevent IDOR (Insecure Direct Object Reference)**:
   - Resource access MUST verify organization ownership on the server side:
     ```php
     if ($user->organization_id && $resource->organization_id !== $user->organization_id) {
         abort(403, 'Unauthorized access to organization resource.');
     }
     ```
3. **Immutable Audit Trail**:
   - Audit logs are append-only. There is NO edit or delete API/action for audit logs.
4. **Password Security**:
   - Enforce Bcrypt rounds >= 12. Never store or log plain-text passwords.
5. **Rate Limiting**:
   - Authentication routes (`/login`, `/password/reset`) are strictly throttled (e.g., 5 attempts/minute).
6. **No Leaked Secrets**:
   - Credentials, RouterOS passwords, and API secrets must be encrypted using Laravel's `Crypt` or `encrypted` model casts.

---

## 6. Planned Development Roadmap

- **Phase 1 (Current)**: UI, Admin Panel, Authentication, RBAC, Organizations, Branches, Audit Logs Foundation.
- **Phase 2**: Customers, Customer Accounts, Service Areas, Packages, Subscriptions Lifecycle.
- **Phase 3**: Billing Engine, Invoices, Payments, Nigerian Gateway Integration (Paystack, Moniepoint, Flutterwave).
- **Phase 4**: RADIUS Foundation (FreeRADIUS integration, RADIUS attributes, NAS, Sessions, Accounting).
- **Phase 5**: MikroTik Integration (RouterOS API service, Hotspot, PPPoE queues, dynamic suspension/reactivation).
- **Phase 6**: Network Infrastructure & Topology (Fibre OLTs, Towers, PtP/PtMP, APs, CPEs).
- **Phase 7**: Automation Engine (Auto-expiry, auto-cutoff, automated reconnects, payment reconciliation).
- **Phase 8**: Monitoring & Real-time Network Analytics (Device health, bandwidth graphs, alerts).
- **Phase 9**: Customer Self-Service Portal & Mobile Web App.
- **Phase 10**: Advanced ISP Enterprise Features (Reseller/Franchise hierarchy, hotspot vouchers, WhatsApp/SMS bot, Public API).
