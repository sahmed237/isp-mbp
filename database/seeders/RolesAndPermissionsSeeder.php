<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Dashboard
            'dashboard.view',

            // Customers
            'customers.view',
            'customers.create',
            'customers.update',
            'customers.delete',
            'customers.export',

            // Resellers & Agents
            'resellers.view',
            'resellers.create',
            'resellers.update',
            'resellers.delete',
            'resellers.approve',

            // Internet Packages
            'packages.view',
            'packages.create',
            'packages.update',
            'packages.delete',

            // Subscriptions
            'subscriptions.view',
            'subscriptions.create',
            'subscriptions.update',
            'subscriptions.cancel',

            // Invoices
            'invoices.view',
            'invoices.create',
            'invoices.update',
            'invoices.delete',
            'invoices.approve',

            // Payments
            'payments.view',
            'payments.create',
            'payments.refund',

            // Network Infrastructure
            'network.devices.view',
            'network.devices.create',
            'network.devices.update',
            'network.devices.delete',
            'network.devices.test_connection',

            // MikroTik Integration
            'mikrotik.view',
            'mikrotik.create',
            'mikrotik.update',
            'mikrotik.delete',
            'mikrotik.execute_command',

            // RADIUS Foundation
            'radius.users.view',
            'radius.users.create',
            'radius.users.update',
            'radius.users.delete',

            // Reports
            'reports.view',
            'reports.export',

            // User Management
            'users.view',
            'users.create',
            'users.update',
            'users.delete',

            // Roles & Permissions
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',
            'permissions.view',

            // Organizations & Branches
            'organizations.view',
            'organizations.create',
            'organizations.update',
            'organizations.delete',
            'branches.view',
            'branches.create',
            'branches.update',
            'branches.delete',

            // Settings & Audit Logs
            'settings.view',
            'settings.update',
            'audit_logs.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // 1. Super Administrator: has all permissions
        $superAdmin = Role::firstOrCreate(['name' => 'Super Administrator', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // 2. Organization Administrator: full access within their organization
        $orgAdmin = Role::firstOrCreate(['name' => 'Organization Administrator', 'guard_name' => 'web']);
        $orgAdmin->syncPermissions([
            'dashboard.view',
            'customers.view', 'customers.create', 'customers.update', 'customers.delete', 'customers.export',
            'resellers.view', 'resellers.create', 'resellers.update', 'resellers.delete', 'resellers.approve',
            'packages.view', 'packages.create', 'packages.update', 'packages.delete',
            'subscriptions.view', 'subscriptions.create', 'subscriptions.update', 'subscriptions.cancel',
            'invoices.view', 'invoices.create', 'invoices.update', 'invoices.delete', 'invoices.approve',
            'payments.view', 'payments.create', 'payments.refund',
            'network.devices.view', 'network.devices.create', 'network.devices.update', 'network.devices.delete', 'network.devices.test_connection',
            'mikrotik.view', 'mikrotik.create', 'mikrotik.update', 'mikrotik.delete', 'mikrotik.execute_command',
            'radius.users.view', 'radius.users.create', 'radius.users.update', 'radius.users.delete',
            'reports.view', 'reports.export',
            'users.view', 'users.create', 'users.update', 'users.delete',
            'roles.view', 'roles.create', 'roles.update', 'permissions.view',
            'branches.view', 'branches.create', 'branches.update', 'branches.delete',
            'settings.view', 'settings.update',
            'audit_logs.view',
        ]);

        // 3. Operations Manager: Customers, Packages, Subscriptions, Network, Support, Reports
        $opsManager = Role::firstOrCreate(['name' => 'Operations Manager', 'guard_name' => 'web']);
        $opsManager->syncPermissions([
            'dashboard.view',
            'customers.view', 'customers.create', 'customers.update', 'customers.export',
            'resellers.view', 'resellers.create', 'resellers.update', 'resellers.approve',
            'packages.view', 'packages.create', 'packages.update',
            'subscriptions.view', 'subscriptions.create', 'subscriptions.update',
            'network.devices.view', 'network.devices.create', 'network.devices.update', 'network.devices.test_connection',
            'mikrotik.view',
            'radius.users.view',
            'reports.view',
            'branches.view',
        ]);

        // 4. Billing Manager: Invoices, Payments, Billing, Customer Accounts
        $billingManager = Role::firstOrCreate(['name' => 'Billing Manager', 'guard_name' => 'web']);
        $billingManager->syncPermissions([
            'dashboard.view',
            'customers.view',
            'resellers.view',
            'subscriptions.view',
            'invoices.view', 'invoices.create', 'invoices.update', 'invoices.delete', 'invoices.approve',
            'payments.view', 'payments.create', 'payments.refund',
            'reports.view', 'reports.export',
        ]);

        // 5. Network Administrator: Routers, MikroTik, Network Devices, RADIUS, Monitoring
        $networkAdmin = Role::firstOrCreate(['name' => 'Network Administrator', 'guard_name' => 'web']);
        $networkAdmin->syncPermissions([
            'dashboard.view',
            'network.devices.view', 'network.devices.create', 'network.devices.update', 'network.devices.delete', 'network.devices.test_connection',
            'mikrotik.view', 'mikrotik.create', 'mikrotik.update', 'mikrotik.delete', 'mikrotik.execute_command',
            'radius.users.view', 'radius.users.create', 'radius.users.update', 'radius.users.delete',
            'reports.view',
        ]);

        // 6. Customer Support: Customers, Subscriptions, Tickets
        $support = Role::firstOrCreate(['name' => 'Customer Support', 'guard_name' => 'web']);
        $support->syncPermissions([
            'dashboard.view',
            'customers.view', 'customers.create', 'customers.update',
            'subscriptions.view',
            'invoices.view',
            'network.devices.view',
        ]);

        // 7. Accountant: View invoices, Record payments, View financial reports
        $accountant = Role::firstOrCreate(['name' => 'Accountant', 'guard_name' => 'web']);
        $accountant->syncPermissions([
            'dashboard.view',
            'customers.view',
            'invoices.view',
            'payments.view', 'payments.create',
            'reports.view', 'reports.export',
        ]);

        // 8. Read Only: View permitted resources but cannot modify them
        $readOnly = Role::firstOrCreate(['name' => 'Read Only', 'guard_name' => 'web']);
        $readOnly->syncPermissions([
            'dashboard.view',
            'customers.view',
            'resellers.view',
            'packages.view',
            'subscriptions.view',
            'invoices.view',
            'payments.view',
            'network.devices.view',
            'mikrotik.view',
            'radius.users.view',
            'reports.view',
            'users.view',
            'roles.view',
            'permissions.view',
            'branches.view',
            'audit_logs.view',
        ]);
    }
}
