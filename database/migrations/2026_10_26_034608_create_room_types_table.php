<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category', 40);
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->unsignedSmallInteger('player_capacity')->default(1);
            $table->string('cpu', 120);
            $table->string('gpu', 120);
            $table->string('memory', 80);
            $table->string('monitor', 120);
            $table->unsignedSmallInteger('monitor_hz');
            $table->string('peripherals')->nullable();
            $table->unsignedInteger('hourly_rate_cents');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_types');
    }
};
