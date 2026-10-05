<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('download_speed'); // in Kbps
            $table->unsignedInteger('upload_speed');   // in Kbps
            $table->unsignedInteger('burst_download')->nullable();
            $table->unsignedInteger('burst_upload')->nullable();
            $table->unsignedInteger('burst_threshold')->nullable();
            $table->unsignedInteger('burst_time')->nullable(); // in seconds
            $table->decimal('price', 12, 2);
            $table->decimal('installation_fee', 12, 2)->default(0.00);
            $table->decimal('activation_fee', 12, 2)->default(0.00);
            $table->unsignedInteger('validity_period')->default(30); // in days
            $table->string('billing_cycle')->default('monthly'); // monthly, quarterly, biannual, annual, custom
            $table->string('connection_type')->default('pppoe'); // pppoe, hotspot, fibre, ptp, ptmp, wireless, dedicated, other, all
            $table->string('status')->default('active'); // active, inactive, archived
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'status']);
            $table->index(['organization_id', 'connection_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
