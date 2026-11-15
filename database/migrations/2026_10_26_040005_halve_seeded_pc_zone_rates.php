<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('pc_zones')->update([
            'hourly_rate' => DB::raw('ROUND(hourly_rate / 2.0, 2)'),
        ]);
    }

    public function down(): void
    {
        DB::table('pc_zones')->update([
            'hourly_rate' => DB::raw('hourly_rate * 2'),
        ]);
    }
};
