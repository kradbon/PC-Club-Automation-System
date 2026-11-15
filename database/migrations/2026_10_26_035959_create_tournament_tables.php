<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tournaments', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('game', 80)->index();
            $table->json('name_translations');
            $table->json('description_translations')->nullable();
            $table->json('prize_translations')->nullable();
            $table->string('format', 24)->default('single_elimination');
            $table->string('status', 24)->default('draft')->index();
            $table->unsignedSmallInteger('participant_limit')->nullable();
            $table->unsignedTinyInteger('team_size')->default(1);
            $table->timestamp('registration_opens_at')->nullable();
            $table->timestamp('registration_closes_at')->nullable();
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('ends_at')->nullable();
            $table->decimal('registration_fee', 10, 2)->default(0);
            $table->char('currency', 3)->default('TJS');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('tournament_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('registration_code')->unique();
            $table->string('status', 20)->default('registered')->index();
            $table->timestamp('registered_at')->useCurrent();
            $table->timestamps();
            $table->unique(['tournament_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournament_registrations');
        Schema::dropIfExists('tournaments');
    }
};
