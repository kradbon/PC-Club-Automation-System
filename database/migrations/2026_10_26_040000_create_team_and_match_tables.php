<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug')->unique();
            $table->foreignId('captain_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 20)->default('member');
            $table->timestamp('joined_at')->useCurrent();
            $table->unique(['team_id', 'user_id']);
        });

        Schema::table('tournament_registrations', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->index(['tournament_id', 'team_id']);
        });

        Schema::create('tournament_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('round_number');
            $table->unsignedSmallInteger('match_number');
            $table->foreignId('team_one_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignId('team_two_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignId('winner_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->unsignedSmallInteger('team_one_score')->nullable();
            $table->unsignedSmallInteger('team_two_score')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->string('status', 20)->default('scheduled')->index();
            $table->timestamps();
            $table->unique(['tournament_id', 'round_number', 'match_number'], 'tournament_match_round_slot_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournament_matches');
        Schema::table('tournament_registrations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('team_id');
        });
        Schema::dropIfExists('team_members');
        Schema::dropIfExists('teams');
    }
};
