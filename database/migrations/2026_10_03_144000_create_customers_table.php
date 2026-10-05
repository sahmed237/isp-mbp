<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('current_package_id')->nullable()->constrained('packages')->nullOnDelete();
            $table->string('account_number')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('company_name')->nullable();
            $table->string('customer_type')->default('individual'); // individual, corporate, government, reseller
            $table->string('email')->nullable();
            $table->string('phone');
            $table->string('alternate_phone')->nullable();
            $table->text('installation_address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('gps_coordinates')->nullable(); // e.g. 9.0765,7.3986
            $table->string('connection_type')->default('pppoe'); // hotspot, pppoe, fibre, ptp, ptmp, wireless, dedicated, other
            $table->string('status')->default('active'); // lead, active, suspended, expired, terminated
            $table->string('portal_username')->nullable();
            $table->string('portal_password')->nullable();
            $table->decimal('balance', 12, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'status']);
            $table->index(['organization_id', 'branch_id']);
            $table->index(['organization_id', 'connection_type']);
            $table->index(['first_name', 'last_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
