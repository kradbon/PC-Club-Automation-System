<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gaming_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('pc_id')->nullable()->constrained('pcs')->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('checked_in')->index();
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('checked_in_at')->nullable();
            $table->foreignId('started_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->foreignId('ended_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('ended_at')->nullable();
            $table->foreignId('no_show_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('no_show_at')->nullable();
            $table->timestamps();
            $table->index(['pc_id', 'status', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gaming_sessions');
    }
};
