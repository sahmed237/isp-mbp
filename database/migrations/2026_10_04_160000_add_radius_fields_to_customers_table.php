<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('radius_username')->nullable()->unique()->after('status');
            $table->string('radius_password')->nullable()->after('radius_username');
            $table->string('static_ip')->nullable()->after('radius_password');

            $table->index(['organization_id', 'radius_username']);
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['organization_id', 'radius_username']);
            $table->dropColumn(['radius_username', 'radius_password', 'static_ip']);
        });
    }
};
