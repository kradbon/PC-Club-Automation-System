<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tournament_registrations', function (Blueprint $table) {
            $table->unique(['tournament_id', 'team_id'], 'tournament_team_registration_unique');
        });
    }

    public function down(): void
    {
        Schema::table('tournament_registrations', function (Blueprint $table) {
            $table->dropUnique('tournament_team_registration_unique');
        });
    }
};
