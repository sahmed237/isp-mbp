<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\NetworkDevice;
use App\Models\Package;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    public function test_guest_is_redirected_to_login_on_protected_routes(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->get('/customers')->assertRedirect(route('login'));
        $this->get('/packages')->assertRedirect(route('login'));
        $this->get('/network-devices')->assertRedirect(route('login'));
        $this->get('/users')->assertRedirect(route('login'));
        $this->get('/roles')->assertRedirect(route('login'));
        $this->get('/organizations')->assertRedirect(route('login'));
        $this->get('/audit-logs')->assertRedirect(route('login'));
    }

    public function test_super_admin_has_full_access_to_all_modules(): void
    {
        $superAdmin = User::where('email', 'superadmin@isp-mbp.local')->first();

        $this->actingAs($superAdmin)->get('/dashboard')->assertStatus(200);
        $this->actingAs($superAdmin)->get('/customers')->assertStatus(200);
        $this->actingAs($superAdmin)->get('/packages')->assertStatus(200);
        $this->actingAs($superAdmin)->get('/network-devices')->assertStatus(200);
        $this->actingAs($superAdmin)->get('/users')->assertStatus(200);
        $this->actingAs($superAdmin)->get('/roles')->assertStatus(200);
        $this->actingAs($superAdmin)->get('/organizations')->assertStatus(200);
        $this->actingAs($superAdmin)->get('/audit-logs')->assertStatus(200);
    }

    public function test_read_only_user_cannot_create_or_mutate_resources(): void
    {
        $readOnly = User::where('email', 'readonly@isp-mbp.local')->first();

        // Read only can view lists
        $this->actingAs($readOnly)->get('/customers')->assertStatus(200);
        $this->actingAs($readOnly)->get('/packages')->assertStatus(200);

        // Cannot create customer
        $this->actingAs($readOnly)->get('/customers/create')->assertStatus(403);
        $this->actingAs($readOnly)->post('/customers', [
            'first_name' => 'Unauthorized',
            'last_name' => 'Customer',
            'email' => 'unauth@test.com',
            'account_type' => 'individual',
            'status' => 'lead',
        ])->assertStatus(403);

        // Cannot create package
        $this->actingAs($readOnly)->get('/packages/create')->assertStatus(403);
        $this->actingAs($readOnly)->post('/packages', [
            'name' => 'Unauthorized Package',
            'download_speed' => 10,
            'upload_speed' => 10,
            'price' => 5000,
            'billing_cycle' => 'monthly',
        ])->assertStatus(403);

        // Cannot create or delete roles
        $this->actingAs($readOnly)->get('/roles/create')->assertStatus(403);
        $role = Role::where('name', 'Custom Test Role')->first() ?? Role::create(['name' => 'Custom Test Role']);
        $this->actingAs($readOnly)->delete("/roles/{$role->id}")->assertStatus(403);

        // Read only can view audit logs, but cannot delete or modify them
        $this->actingAs($readOnly)->get('/audit-logs')->assertStatus(200);
    }

    public function test_network_administrator_cannot_access_user_or_role_management(): void
    {
        $netAdmin = User::where('email', 'netadmin@isp-mbp.local')->first();

        // Net admin can access network devices
        $this->actingAs($netAdmin)->get('/network-devices')->assertStatus(200);

        // Net admin cannot access users, roles, or audit logs
        $this->actingAs($netAdmin)->get('/users')->assertStatus(403);
        $this->actingAs($netAdmin)->get('/roles')->assertStatus(403);
        $this->actingAs($netAdmin)->get('/audit-logs')->assertStatus(403);
    }

    public function test_customer_support_cannot_access_roles_or_delete_packages(): void
    {
        $support = User::where('email', 'support@isp-mbp.local')->first();
        $package = Package::first();

        $this->actingAs($support)->get('/customers')->assertStatus(200);
        $this->actingAs($support)->get('/roles')->assertStatus(403);
        $this->actingAs($support)->get('/audit-logs')->assertStatus(403);

        if ($package) {
            $this->actingAs($support)->delete("/packages/{$package->id}")->assertStatus(403);
        }
    }

    public function test_side_menu_displays_based_on_user_permissions(): void
    {
        $superAdmin = User::where('email', 'superadmin@isp-mbp.local')->first();
        $netAdmin = User::where('email', 'netadmin@isp-mbp.local')->first();
        $support = User::where('email', 'support@isp-mbp.local')->first();

        // 1. Super Admin sees all navigation modules
        $superAdminResponse = $this->actingAs($superAdmin)->get(route('dashboard'));
        $superAdminResponse->assertStatus(200);
        $superAdminResponse->assertSee(route('customers.index'));
        $superAdminResponse->assertSee(route('network-devices.index'));
        $superAdminResponse->assertSee(route('users.index'));
        $superAdminResponse->assertSee(route('roles.index'));
        $superAdminResponse->assertSee(route('settings.index'));
        $superAdminResponse->assertSee(route('audit-logs.index'));

        // 2. Network Administrator sees Network & RADIUS, but NOT Customers, Users, Roles, or Settings
        $netAdminResponse = $this->actingAs($netAdmin)->get(route('dashboard'));
        $netAdminResponse->assertStatus(200);
        $netAdminResponse->assertSee(route('network-devices.index'));
        $netAdminResponse->assertSee(route('radius.index'));
        $netAdminResponse->assertDontSee(route('customers.index'));
        $netAdminResponse->assertDontSee(route('users.index'));
        $netAdminResponse->assertDontSee(route('roles.index'));
        $netAdminResponse->assertDontSee(route('settings.index'));

        // 3. Customer Support sees Subscribers, but NOT Roles, Users, Audit Logs, or Settings
        $supportResponse = $this->actingAs($support)->get(route('dashboard'));
        $supportResponse->assertStatus(200);
        $supportResponse->assertSee(route('customers.index'));
        $supportResponse->assertDontSee(route('users.index'));
        $supportResponse->assertDontSee(route('roles.index'));
        $supportResponse->assertDontSee(route('settings.index'));
        $supportResponse->assertDontSee(route('audit-logs.index'));
    }
}

