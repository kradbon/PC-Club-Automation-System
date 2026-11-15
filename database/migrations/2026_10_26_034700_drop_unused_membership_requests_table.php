<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('membership_requests');
    }

    public function down(): void
    {
        Schema::create('membership_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('gamer_tag')->nullable();
            $table->foreignId('club_event_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 20)->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
};
