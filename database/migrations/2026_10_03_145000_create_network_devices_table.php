<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('network_devices', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('name');
            $table->string('device_type'); // mikrotik_router, core_router, access_router, switch, access_point, olt, tower, cpe, other
            $table->string('ip_address', 45);
            $table->string('hostname')->nullable();
            $table->string('mac_address', 20)->nullable();
            $table->string('vendor')->nullable(); // MikroTik, Cisco, Huawei, Ubiquiti, Cambium, etc.
            $table->string('model')->nullable();
            $table->string('location')->nullable();
            $table->unsignedSmallInteger('api_port')->default(8728);
            $table->unsignedSmallInteger('ssh_port')->default(22);
            $table->unsignedSmallInteger('web_port')->default(80);
            $table->string('username')->nullable();
            $table->text('password')->nullable(); // encrypted
            $table->string('status')->default('offline'); // online, offline, warning, maintenance
            $table->timestamp('last_seen_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'status']);
            $table->index(['organization_id', 'device_type']);
            $table->index(['organization_id', 'branch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('network_devices');
    }
};
