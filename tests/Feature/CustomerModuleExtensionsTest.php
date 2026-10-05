<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerDocument;
use App\Models\Organization;
use App\Models\Package;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerModuleExtensionsTest extends TestCase
{
    protected User $adminUser;
    protected Organization $org;
    protected Package $package;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::where('email', 'superadmin@isp-mbp.local')->first()
            ?? User::factory()->create(['status' => 'active']);

        $this->org = Organization::first() ?? Organization::create([
            'name' => 'Apex Networks Limited',
            'code' => 'APEX-NET',
            'status' => 'active',
            'contact_email' => 'noc@apexnetworks.ng',
        ]);

        $this->package = Package::first() ?? Package::create([
            'organization_id' => $this->org->id,
            'name' => 'Premium Fibre 50M',
            'code' => 'FIBRE-50M',
            'download_speed' => 51200,
            'upload_speed' => 51200,
            'price' => 30000.00,
            'validity_period' => 30,
            'billing_cycle' => 'monthly',
            'connection_type' => 'pppoe',
            'status' => 'active',
        ]);

        $uniqueKey = uniqid('cust_');
        $this->customer = Customer::create([
            'organization_id' => $this->org->id,
            'first_name' => 'Adewale',
            'last_name' => 'Ogunlesi',
            'email' => "{$uniqueKey}@example.ng",
            'phone' => '+234803999' . rand(1000, 9999),
            'account_number' => 'CUST-' . strtoupper($uniqueKey),
            'account_type' => 'corporate',
            'status' => 'active',
            'package_id' => $this->package->id,
            'installation_address' => 'Plot 402 Cadastral Zone, Central Business District, Abuja',
            'city' => 'Abuja',
            'state' => 'FCT',
            'gps_coordinates' => '9.057850, 7.495080',
            'connection_type' => 'fibre',
            'radius_username' => "rad_{$uniqueKey}",
            'radius_password' => 'secret123',
        ]);
    }

    public function test_admin_can_view_customer_groups(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('customers.groups.index'));

        $response->assertStatus(200);
        $response->assertSee('Customer Groups &amp; Segmentation', false);
        $response->assertSee('Corporate');
        $response->assertSee('Individual');
    }

    public function test_admin_can_view_customer_locations(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('customers.locations.index'));

        $response->assertStatus(200);
        $response->assertSee('Customer Locations &amp; Coverage Map', false);
        $response->assertSee('Central Business District');
        $response->assertSee('9.057850');
    }

    public function test_admin_can_view_customer_documents_hub(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('customers.documents.index'));

        $response->assertStatus(200);
        $response->assertSee('Customer Documents &amp; KYC Repository', false);
    }

    public function test_admin_can_upload_download_and_delete_customer_document(): void
    {
        Storage::fake('local');

        $fakeFile = UploadedFile::fake()->create('service_level_agreement.pdf', 350, 'application/pdf');

        // 1. Upload Document
        $uploadResponse = $this->actingAs($this->adminUser)->post(
            route('customers.documents.store', $this->customer),
            [
                'title' => 'Signed Corporate SLA 2026',
                'document_type' => 'sla_contract',
                'file' => $fakeFile,
                'notes' => 'Signed enterprise fibre SLA with 99.9% uptime guarantee',
            ]
        );

        $uploadResponse->assertRedirect();
        $uploadResponse->assertSessionHas('success');

        $document = CustomerDocument::where('customer_id', $this->customer->id)
            ->where('title', 'Signed Corporate SLA 2026')
            ->first();

        $this->assertNotNull($document);
        $this->assertEquals('sla_contract', $document->document_type);
        $this->assertEquals('application/pdf', $document->mime_type);
        $this->assertEquals($this->customer->organization_id, $document->organization_id);

        Storage::disk('local')->assertExists($document->file_path);

        // 2. Download Document
        $downloadResponse = $this->actingAs($this->adminUser)->get(
            route('customers.documents.download', $document)
        );

        $downloadResponse->assertStatus(200);
        $this->assertStringContainsString('service_level_agreement.pdf', (string)$downloadResponse->headers->get('content-disposition'));

        // 3. Delete Document
        $deleteResponse = $this->actingAs($this->adminUser)->delete(
            route('customers.documents.destroy', $document)
        );

        $deleteResponse->assertRedirect();
        $this->assertDatabaseMissing('customer_documents', ['id' => $document->id]);
        Storage::disk('local')->assertMissing($document->file_path);
    }
}
