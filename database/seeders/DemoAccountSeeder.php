<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;

class DemoAccountSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        $accounts = [
            ['name' => 'GB CLUB Demo Admin', 'email' => 'demo-admin@example.com', 'password' => 'GBClubDemoAdmin!2026', 'role' => 'admin'],
            ['name' => 'GB CLUB Demo Staff', 'email' => 'demo-staff@example.com', 'password' => 'GBClubDemoStaff!2026', 'role' => 'staff'],
            ['name' => 'GB CLUB Demo Customer', 'email' => 'demo-customer@example.com', 'password' => 'GBClubDemoMember!2026', 'role' => 'customer'],
        ];

        foreach ($accounts as $account) {
            $user = User::query()->firstOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => $account['password'],
                    'role' => $account['role'],
                    'membership_tier' => 'standard',
                    'account_status' => 'active',
                    'preferred_locale' => 'en',
                    'email_verified_at' => now(),
                ],
            );

            if ($user->role === 'customer') {
                Wallet::query()->firstOrCreate(
                    ['user_id' => $user->id],
                    ['cash_balance' => '0.00', 'bonus_balance' => '0.00', 'currency' => config('club.currency', 'TJS')],
                );
            }
        }
    }
}
