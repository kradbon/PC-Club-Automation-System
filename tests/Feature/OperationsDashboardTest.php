<?php

namespace Tests\Feature;

use App\Models\FoodItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class OperationsDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_dashboard_and_manage_food_menu(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/en/admin')->assertOk();
        $this->actingAs($admin)->get('/en/admin?period=30')->assertOk();
        $this->actingAs($admin)->get('/en/admin/food-menu')->assertOk();
        $this->actingAs($admin)->get('/en/admin/rooms')->assertOk()->assertSee('HOURLY RATE (TJS)');
        $this->actingAs($admin)->get('/en/admin/tariffs')->assertOk()->assertSee('Price (TJS)');
        $this->actingAs($admin)->get('/en/admin/profile')->assertOk()->assertSee('name="avatar"', false);
        $this->actingAs($admin)->get('/en/admin/users')->assertOk();
        $this->actingAs($admin)->get('/en/admin/users/create')->assertOk();
        $this->actingAs($admin)->post('/en/admin/food-menu', [
            'name' => 'Test Special',
            'slug' => 'test-special',
            'category' => 'Mains',
            'description' => 'A menu test item.',
            'price' => '12.50',
            'is_available' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('food_items', [
            'name' => 'Test Special',
            'price_cents' => 1250,
            'is_available' => true,
        ]);

        $this->actingAs($admin)->post('/en/admin/promotions', [
            'code' => 'WEEKEND10',
            'title_en' => 'Weekend 10% off',
            'reward_type' => 'discount_percent',
            'reward_value' => '10',
            'minimum_purchase' => '0',
            'usage_limit' => '100',
        ])->assertRedirect();

        $this->assertDatabaseHas('promotions', ['code' => 'WEEKEND10', 'reward_type' => 'discount_percent']);
    }

    public function test_staff_has_a_separate_dashboard_and_cannot_open_admin_tools(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)->get('/en/staff')->assertOk();
        $this->actingAs($staff)->get('/en/staff/pcs')->assertOk();
        $this->actingAs($staff)->get('/en/staff/profile')->assertOk()->assertSee($staff->email)->assertDontSee('Cash balance');
        $this->actingAs($staff)->get('/en/staff/assisted')->assertRedirect('/en/staff/bookings');
        $this->actingAs($staff)->get('/en/account/wallet')->assertForbidden();
        $this->actingAs($staff)->get('/en/admin')->assertForbidden();
        $this->actingAs($staff)->get('/en/admin/food-menu')->assertForbidden();
    }

    public function test_customer_cannot_open_staff_profile_and_suspended_staff_loses_dashboard_access(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer)->get('/en/account')->assertOk();
        $this->actingAs($customer)->get('/en/account/bookings')->assertOk();
        $this->actingAs($customer)->get('/en/account/bookings/create')->assertOk();
        $this->actingAs($customer)->get('/en/account/wallet')->assertOk();
        $this->actingAs($customer)->get('/en/account/food')->assertOk();
        $this->actingAs($customer)->get('/en/account/tariffs')->assertOk();
        $this->actingAs($customer)->get('/en/account/orders')->assertOk()->assertSee('Order #')->assertSee('Package');
        $this->actingAs($customer)->get('/en/account/teams')->assertOk();
        $this->actingAs($customer)->get('/en/account/sessions')->assertOk();
        $this->actingAs($customer)->get('/en/account/profile')->assertOk()->assertSee($customer->email);
        $this->actingAs($customer)->put('/en/account/profile', ['name' => 'Updated Player', 'preferred_locale' => 'ru'])->assertRedirect('/en/account/profile');
        $this->assertDatabaseHas('users', ['id' => $customer->id, 'name' => 'Updated Player', 'preferred_locale' => 'ru']);
        $this->actingAs($customer)->get('/en/staff/profile')->assertForbidden();

        $staff = User::factory()->create(['role' => 'staff', 'account_status' => 'suspended']);
        $this->actingAs($staff)->get('/en/staff')->assertForbidden();
    }


    public function test_admin_staff_and_customers_can_update_their_own_profile_and_avatar(): void
    {
        Storage::fake('public');

        foreach ([['admin', 'admin.profile'], ['staff', 'staff.profile'], ['customer', 'account.profile']] as [$role, $profileRoute]) {
            $user = User::factory()->create(['role' => $role]);
            $this->actingAs($user)->get(route($profileRoute, ['locale' => 'en']))->assertOk()->assertSee('name="avatar"', false);
            $this->actingAs($user)->put(route($profileRoute.'.update', ['locale' => 'en']), [
                'name' => 'Profile '.$role,
                'preferred_locale' => 'tj',
                'avatar' => UploadedFile::fake()->image('avatar.png', 120, 120),
            ])->assertRedirect(route($profileRoute, ['locale' => 'en']));
            $user->refresh();
            $this->assertSame('Profile '.$role, $user->name);
            $this->assertSame('tj', $user->preferred_locale);
            $this->assertNotEmpty($user->avatar_path);
            Storage::disk('public')->assertExists($user->avatar_path);
        }
    }

    public function test_package_catalog_uses_the_saved_zone_prices_and_disables_demo_tariffs(): void
    {
        $expected = [
            'Regular' => 1250,
            'VIP' => 2250,
            'Luxe' => 4000,
            'Streamer Room' => 5500,
            'Bootcamp DUST2' => 11250,
            'Bootcamp MIRAGE' => 11250,
            'WINGMAN / 01' => 4500,
            'WINGMAN / 02' => 4500,
            'SOLO LUXE / 01' => 4500,
            'SOLO LUXE / 02' => 4500,
        ];

        foreach ($expected as $name => $priceCents) {
            $this->assertDatabaseHas('tariffs', [
                'name' => $name,
                'minutes' => 60,
                'price_cents' => $priceCents,
                'is_active' => true,
            ]);
        }

        foreach (['Quick Match', 'Squad Session', 'All-Nighter Pack'] as $demoName) {
            $this->assertDatabaseMissing('tariffs', ['name' => $demoName, 'is_active' => true]);
        }
    }

    public function test_demo_seeder_creates_the_documented_local_login_accounts(): void
    {
        $this->seed(\Database\Seeders\DemoAccountSeeder::class);

        foreach ([
            'demo-admin@example.com' => ['GBClubDemoAdmin!2026', 'admin'],
            'demo-staff@example.com' => ['GBClubDemoStaff!2026', 'staff'],
            'demo-customer@example.com' => ['GBClubDemoMember!2026', 'customer'],
        ] as $email => [$password, $role]) {
            $user = User::query()->where('email', $email)->firstOrFail();
            $this->assertSame($role, $user->role);
            $this->assertTrue(Hash::check($password, $user->password));
            $this->assertSame('active', $user->account_status);
        }
    }
}
