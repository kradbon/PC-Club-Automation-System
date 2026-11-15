<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pc_zone_id')->constrained()->restrictOnDelete();
            $table->foreignId('pc_id')->nullable()->constrained('pcs')->restrictOnDelete();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->string('status', 20)->default('pending')->index();
            $table->text('customer_note')->nullable();
            $table->text('decision_reason')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('hourly_rate_snapshot', 10, 2)->nullable();
            $table->decimal('total_price_snapshot', 12, 2)->nullable();
            $table->decimal('cash_debited', 12, 2)->default(0);
            $table->decimal('bonus_debited', 12, 2)->default(0);
            $table->char('currency', 3)->default('TJS');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['pc_id', 'status', 'starts_at', 'ends_at'], 'bookings_pc_conflict_idx');
            $table->index(['pc_zone_id', 'status', 'starts_at', 'ends_at'], 'bookings_zone_conflict_idx');
            $table->index(['user_id', 'status', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
