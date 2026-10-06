<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->change();
            $table->foreignId('voucher_id')->nullable()->after('invoice_id')->constrained('hotspot_vouchers')->nullOnDelete();
        });

        Schema::table('payment_attempts', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->change();
            $table->foreignId('invoice_id')->nullable()->change();
            $table->foreignId('voucher_id')->nullable()->after('invoice_id')->constrained('hotspot_vouchers')->nullOnDelete();
            $table->string('batch_id')->nullable()->after('voucher_id');
        });
    }

    public function down(): void
    {
        Schema::table('payment_attempts', function (Blueprint $table) {
            $table->dropForeign(['voucher_id']);
            $table->dropColumn(['voucher_id', 'batch_id']);
            $table->foreignId('invoice_id')->nullable(false)->change();
            $table->foreignId('customer_id')->nullable(false)->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['voucher_id']);
            $table->dropColumn('voucher_id');
            $table->foreignId('customer_id')->nullable(false)->change();
        });
    }
};
