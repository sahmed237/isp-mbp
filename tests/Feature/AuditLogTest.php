<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Organization;
use App\Models\Package;
use App\Models\User;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    public function test_customer_creation_generates_immutable_audit_log(): void
    {
        $admin = User::where('email', 'superadmin@isp-mbp.local')->first();
        $org = Organization::first();

        Customer::where('email', 'audit_cust@test.local')->forceDelete();

        $response = $this->actingAs($admin)->post('/customers', [
            'organization_id' => $org->id,
            'first_name' => 'Auditable',
            'last_name' => 'Customer',
            'email' => 'audit_cust@test.local',
            'phone' => '+234 803 111 2222',
            'customer_type' => 'individual',
            'connection_type' => 'fibre',
            'status' => 'lead',
        ]);

        $response->assertRedirect();

        $customer = Customer::where('email', 'audit_cust@test.local')->first();
        $this->assertNotNull($customer);

        // Verify audit log entry exists
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => Customer::class,
            'auditable_id' => $customer->id,
            'action' => 'created',
            'user_id' => $admin->id,
        ]);
    }

    public function test_audit_logs_can_be_viewed_by_authorized_users(): void
    {
        $superAdmin = User::where('email', 'superadmin@isp-mbp.local')->first();

        $response = $this->actingAs($superAdmin)->get('/audit-logs');
        $response->assertStatus(200);
        $response->assertSee('Security Audit Logs');

        $latestLog = AuditLog::latest('created_at')->first();
        if ($latestLog) {
            $showResponse = $this->actingAs($superAdmin)->get("/audit-logs/{$latestLog->id}");
            $showResponse->assertStatus(200);
            $showResponse->assertSee("Audit Record #{$latestLog->id}");
        }
    }

    public function test_audit_logs_cannot_be_deleted_via_http(): void
    {
        $superAdmin = User::where('email', 'superadmin@isp-mbp.local')->first();
        $latestLog = AuditLog::latest('created_at')->first();

        if ($latestLog) {
            // No DELETE route exists for audit logs
            $response = $this->actingAs($superAdmin)->delete("/audit-logs/{$latestLog->id}");
            $response->assertStatus(405); // Method Not Allowed
        }
    }
}
