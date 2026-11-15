<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('tariffs')->update([
            'price_cents' => DB::raw('ROUND(price_cents / 2.0)'),
        ]);

        DB::table('pc_zones')->update([
            'hourly_rate' => DB::raw('ROUND(hourly_rate / 2.0, 2)'),
        ]);

        if (Schema::hasTable('room_types')) {
            DB::table('room_types')->update([
                'hourly_rate_cents' => DB::raw('ROUND(hourly_rate_cents / 2.0)'),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('tariffs')->update([
            'price_cents' => DB::raw('price_cents * 2'),
        ]);

        DB::table('pc_zones')->update([
            'hourly_rate' => DB::raw('hourly_rate * 2'),
        ]);

        if (Schema::hasTable('room_types')) {
            DB::table('room_types')->update([
                'hourly_rate_cents' => DB::raw('hourly_rate_cents * 2'),
            ]);
        }
    }
};
