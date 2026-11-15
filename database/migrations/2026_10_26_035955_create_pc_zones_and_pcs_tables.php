<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pc_zones', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('category', 40)->index();
            $table->string('booking_mode', 20)->default('station');
            $table->json('name_translations');
            $table->json('description_translations')->nullable();
            $table->decimal('hourly_rate', 10, 2);
            $table->char('currency', 3)->default('TJS');
            $table->time('opens_at')->nullable();
            $table->time('closes_at')->nullable();
            $table->unsignedSmallInteger('minimum_booking_minutes')->default(60);
            $table->unsignedSmallInteger('maximum_booking_minutes')->default(480);
            $table->unsignedSmallInteger('maximum_days_ahead')->default(30);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('pcs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pc_zone_id')->constrained()->cascadeOnDelete();
            $table->string('name', 80);
            $table->string('asset_tag', 80)->unique();
            $table->json('specifications')->nullable();
            $table->string('status', 20)->default('available')->index();
            $table->boolean('is_bookable')->default(true);
            $table->text('maintenance_note')->nullable();
            $table->timestamps();
            $table->index(['pc_zone_id', 'status', 'is_bookable']);
        });

        Schema::create('pc_maintenance_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pc_id')->constrained('pcs')->cascadeOnDelete();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->string('reason', 255);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['pc_id', 'starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pc_maintenance_periods');
        Schema::dropIfExists('pcs');
        Schema::dropIfExists('pc_zones');
    }
};
