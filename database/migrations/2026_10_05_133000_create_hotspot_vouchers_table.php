<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotspot_vouchers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            $table->string('batch_id')->nullable();
            $table->string('code')->unique();
            $table->string('username')->unique();
            $table->string('password');
            $table->decimal('price', 12, 2);
            $table->unsignedInteger('duration_value')->default(1);
            $table->string('duration_unit')->default('hours'); // minutes, hours, days
            $table->unsignedBigInteger('data_limit_mb')->nullable();
            $table->string('status')->default('unused'); // unused, active, used, expired, disabled
            $table->timestamp('first_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('used_by_mac')->nullable();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'status']);
            $table->index(['code']);
            $table->index(['username']);
            $table->index(['batch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotspot_vouchers');
    }
};
