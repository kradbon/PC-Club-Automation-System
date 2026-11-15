<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('tariffs')->whereIn('name', ['Quick Match', 'Squad Session', 'All-Nighter Pack'])
            ->update(['is_active' => false, 'updated_at' => now()]);

        $packages = [
            ['name' => 'Regular', 'description' => 'A comfortable, affordable setup for everyday gaming.', 'price_cents' => 1250, 'sort_order' => 1],
            ['name' => 'VIP', 'description' => 'QHD clarity, 240 Hz, and premium peripherals.', 'price_cents' => 2250, 'sort_order' => 2],
            ['name' => 'Luxe', 'description' => 'Flagship solo setup with a 4K OLED display.', 'price_cents' => 4000, 'sort_order' => 3],
            ['name' => 'Streamer Room', 'description' => 'A ready-to-stream creator room with broadcast equipment.', 'price_cents' => 5500, 'sort_order' => 4],
            ['name' => 'Bootcamp DUST2', 'description' => 'Five matched PCs for CS2 DUST2 team practice.', 'price_cents' => 11250, 'sort_order' => 5],
            ['name' => 'Bootcamp MIRAGE', 'description' => 'Five matched PCs for CS2 MIRAGE team practice.', 'price_cents' => 11250, 'sort_order' => 6],
            ['name' => 'WINGMAN / 01', 'description' => 'Private two-player room for duo queue and co-op.', 'price_cents' => 4500, 'sort_order' => 7],
            ['name' => 'WINGMAN / 02', 'description' => 'Private two-player room for duo queue and co-op.', 'price_cents' => 4500, 'sort_order' => 8],
            ['name' => 'SOLO LUXE / 01', 'description' => 'Private top-spec room for a premium solo session.', 'price_cents' => 4500, 'sort_order' => 9],
            ['name' => 'SOLO LUXE / 02', 'description' => 'Private top-spec room for a premium solo session.', 'price_cents' => 4500, 'sort_order' => 10],
        ];

        foreach ($packages as $package) {
            DB::table('tariffs')->updateOrInsert(
                ['name' => $package['name']],
                $package + ['minutes' => 60, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            );
        }
    }

    public function down(): void
    {
        DB::table('tariffs')->whereIn('name', [
            'Regular', 'VIP', 'Luxe', 'Streamer Room', 'Bootcamp DUST2', 'Bootcamp MIRAGE',
            'WINGMAN / 01', 'WINGMAN / 02', 'SOLO LUXE / 01', 'SOLO LUXE / 02',
        ])->update(['is_active' => false, 'updated_at' => now()]);

        DB::table('tariffs')->whereIn('name', ['Quick Match', 'Squad Session', 'All-Nighter Pack'])
            ->update(['is_active' => true, 'updated_at' => now()]);
    }
};
