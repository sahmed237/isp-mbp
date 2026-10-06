<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resellers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('reseller_code')->unique();
            $table->string('business_name');
            $table->string('contact_person');
            $table->string('email')->unique();
            $table->string('phone')->unique();
            $table->string('alternate_phone')->nullable();
            $table->string('password');
            $table->string('business_type')->default('retail_agent'); // cybercafe, hotel, campus_kiosk, retail_agent, business_center, other
            $table->text('shop_address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('id_type')->nullable(); // national_id, voters_card, drivers_license, passport, cac_registration
            $table->string('id_number')->nullable();
            $table->string('id_card_path')->nullable(); // uploaded KYC file path
            $table->decimal('balance', 12, 2)->default(0.00); // prepaid wallet balance
            $table->string('status')->default('pending'); // pending, active, suspended, rejected
            $table->text('rejection_reason')->nullable();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'status']);
            $table->index(['phone']);
            $table->index(['email']);
        });

        Schema::table('hotspot_vouchers', function (Blueprint $table) {
            $table->foreignId('reseller_id')->nullable()->after('customer_id')->constrained('resellers')->nullOnDelete();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('reseller_id')->nullable()->after('customer_id')->constrained('resellers')->nullOnDelete();
        });

        Schema::table('payment_attempts', function (Blueprint $table) {
            $table->foreignId('reseller_id')->nullable()->after('customer_id')->constrained('resellers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payment_attempts', function (Blueprint $table) {
            $table->dropForeign(['reseller_id']);
            $table->dropColumn('reseller_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['reseller_id']);
            $table->dropColumn('reseller_id');
        });

        Schema::table('hotspot_vouchers', function (Blueprint $table) {
            $table->dropForeign(['reseller_id']);
            $table->dropColumn('reseller_id');
        });

        Schema::dropIfExists('resellers');
    }
};
