<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('club_orders', function (Blueprint $table): void {
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
        });

        Schema::table('food_orders', function (Blueprint $table): void {
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('food_orders', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('booking_id');
        });

        Schema::table('club_orders', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('booking_id');
        });
    }
};
