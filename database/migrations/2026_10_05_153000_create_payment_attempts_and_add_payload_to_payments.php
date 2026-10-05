<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add raw_payload column to payments table
        Schema::table('payments', function (Blueprint $table) {
            $table->jsonb('raw_payload')->nullable()->after('notes');
        });

        // 2. Create payment_attempts table for forensic audit
        Schema::create('payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->string('payment_method'); // paystack, monnify, etc.
            $table->string('reference')->index();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('NGN');
            $table->string('status')->default('initiated'); // initiated, pending, successful, failed, abandoned
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->jsonb('request_payload')->nullable();
            $table->jsonb('response_payload')->nullable();
            $table->jsonb('verification_payload')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('initiated_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'status']);
            $table->index(['invoice_id', 'created_at']);
            $table->index(['customer_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_attempts');

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('raw_payload');
        });
    }
};
