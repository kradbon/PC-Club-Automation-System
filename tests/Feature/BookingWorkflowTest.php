<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\ClubOrder;
use App\Models\FoodItem;
use App\Models\Pc;
use App\Models\PcZone;
use App\Models\Tariff;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\BookingService;
use App\Services\GamingSessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BookingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        Carbon::setTestNow(Carbon::parse('2026-10-26 09:00:00', config('app.timezone')));
    }

    public function test_staff_confirmation_debits_wallet_and_snapshots_price(): void
    {
        [$user, $staff, $zone, $pc] = $this->fixture();
        $booking = app(BookingService::class)->request($user, [
            'pc_zone_id' => $zone->id, 'pc_id' => $pc->id,
            'starts_at' => now()->addHours(3)->format('Y-m-d H:i:s'), 'duration_minutes' => 120,
        ]);

        $this->assertSame('pending', $booking->status);
        $confirmed = app(BookingService::class)->confirm($booking, $staff);

        $this->assertSame('confirmed', $confirmed->status);
        $this->assertSame('200.00', $confirmed->total_price_snapshot);
        $this->assertSame('300.00', $user->wallet->fresh()->cash_balance);
        $this->assertSame(1, WalletTransaction::where('transaction_type', 'booking_debit')->count());
    }

    public function test_staff_confirmation_rechecks_overlapping_pc_reservations(): void
    {
        [$firstUser, $staff, $zone, $pc] = $this->fixture();
        $first = app(BookingService::class)->request($firstUser, [
            'pc_zone_id' => $zone->id, 'pc_id' => $pc->id,
            'starts_at' => now()->addHours(3)->format('Y-m-d H:i:s'), 'duration_minutes' => 60,
        ]);
        app(BookingService::class)->confirm($first, $staff);

        $secondUser = User::factory()->create(['role' => 'customer']);
        Wallet::create(['user_id' => $secondUser->id, 'cash_balance' => '500.00', 'bonus_balance' => '0.00', 'currency' => 'TJS']);
        $second = app(BookingService::class)->request($secondUser, [
            'pc_zone_id' => $zone->id, 'pc_id' => $pc->id,
            'starts_at' => now()->addHours(3)->format('Y-m-d H:i:s'), 'duration_minutes' => 60,
        ]);

        try {
            app(BookingService::class)->confirm($second, $staff);
            $this->fail('The overlapping reservation should have been rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('booking', $exception->errors());
        }

        $this->assertSame('pending', $second->fresh()->status);
    }

    public function test_eligible_cancellation_refunds_the_cash_debit_once(): void
    {
        [$user, $staff, $zone, $pc] = $this->fixture();
        $booking = app(BookingService::class)->request($user, [
            'pc_zone_id' => $zone->id, 'pc_id' => $pc->id,
            'starts_at' => now()->addHours(4)->format('Y-m-d H:i:s'), 'duration_minutes' => 60,
        ]);
        $confirmed = app(BookingService::class)->confirm($booking, $staff);
        app(BookingService::class)->cancel($confirmed, $user);

        $this->assertSame('cancelled', $confirmed->fresh()->status);
        $this->assertSame('500.00', $user->wallet->fresh()->cash_balance);
        $this->assertSame(2, WalletTransaction::where('transaction_type', 'booking_debit')->orWhere('transaction_type', 'booking_refund')->count());
    }

    public function test_non_admin_cannot_open_admin_dashboard(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)->get('/en/admin')->assertForbidden();
    }

    public function test_staff_can_place_food_and_package_orders_for_the_reservation_customer_without_email(): void
    {
        [$customer, $staff, $zone, $pc] = $this->fixture();
        $booking = app(BookingService::class)->request($customer, [
            'pc_zone_id' => $zone->id, 'pc_id' => $pc->id,
            'starts_at' => now()->addHours(3)->format('Y-m-d H:i:s'), 'duration_minutes' => 60,
        ]);
        $food = FoodItem::create([
            'slug' => 'test-burger', 'name' => 'Test burger', 'category' => 'Main',
            'price_cents' => 5500, 'currency' => 'TJS', 'is_available' => true,
        ]);
        $tariff = Tariff::create(['name' => 'Test 60 Minute Package', 'minutes' => 60, 'price_cents' => 400, 'is_active' => true]);

        $this->actingAs($staff)->get('/en/staff/bookings')
            ->assertOk()
            ->assertSee('Create an order for this reservation')
            ->assertSee('Test 60 Minute Package')
            ->assertSee('Test burger')
            ->assertDontSee('name="customer_email"');

        $this->actingAs($staff)->post(route('staff.bookings.food-order', ['locale' => 'en', 'booking' => $booking]), [
            'request_key' => (string) Str::uuid(), 'fulfillment_method' => 'club', 'items' => [$food->id => 2],
        ])->assertRedirect();
        $this->assertDatabaseHas('food_orders', ['user_id' => $customer->id, 'booking_id' => $booking->id, 'total_cents' => 11000]);
        $this->assertDatabaseHas('activity_logs', ['actor_id' => $staff->id, 'action' => 'food_order.created_for_customer']);

        $this->actingAs($staff)->post(route('staff.bookings.package-order', ['locale' => 'en', 'booking' => $booking]), [
            'tariff_id' => $tariff->id,
        ])->assertRedirect();
        $this->assertDatabaseHas('club_orders', ['user_id' => $customer->id, 'booking_id' => $booking->id, 'tariff_id' => $tariff->id, 'status' => 'pending']);
    }

    public function test_staff_can_order_for_a_selected_customer_without_an_existing_booking_or_email(): void
    {
        [$customer, $staff] = $this->fixture();
        $food = FoodItem::create([
            'slug' => 'walk-in-drink', 'name' => 'Walk-in drink', 'category' => 'Drinks',
            'price_cents' => 1200, 'currency' => 'TJS', 'is_available' => true,
        ]);
        $tariff = Tariff::create(['name' => 'Walk-in pack', 'minutes' => 60, 'price_cents' => 400, 'is_active' => true]);

        $this->actingAs($staff)->get('/en/staff/bookings')
            ->assertOk()
            ->assertSee('Create an order for a customer')
            ->assertSee($customer->name)
            ->assertDontSee('name="customer_email"');

        $this->actingAs($staff)->post('/en/staff/customer-orders', [
            'customer_id' => $customer->id, 'order_type' => 'food',
            'request_key' => (string) Str::uuid(), 'fulfillment_method' => 'club', 'items' => [$food->id => 1],
        ])->assertRedirect();
        $this->assertDatabaseHas('food_orders', ['user_id' => $customer->id, 'booking_id' => null, 'total_cents' => 1200]);

        $this->actingAs($staff)->post('/en/staff/customer-orders', [
            'customer_id' => $customer->id, 'order_type' => 'package', 'tariff_id' => $tariff->id,
        ])->assertRedirect();
        $this->assertDatabaseHas('club_orders', ['user_id' => $customer->id, 'booking_id' => null, 'tariff_id' => $tariff->id]);
    }

    public function test_staff_can_complete_the_checked_in_session_workflow(): void
    {
        [$customer, $staff, $zone, $pc] = $this->fixture();
        $booking = app(BookingService::class)->request($customer, [
            'pc_zone_id' => $zone->id, 'pc_id' => $pc->id,
            'starts_at' => now()->addHour()->format('Y-m-d H:i:s'), 'duration_minutes' => 60,
        ]);
        $booking = app(BookingService::class)->confirm($booking, $staff);
        Carbon::setTestNow($booking->starts_at->copy());

        $sessions = app(GamingSessionService::class);
        $session = $sessions->checkIn($booking, $staff, $pc->id);
        $this->assertSame('checked_in', $session->status);
        $session = $sessions->start($session, $staff);
        $this->assertSame('active', $session->status);
        $session = $sessions->end($session, $staff);

        $this->assertSame('completed', $session->fresh()->status);
        $this->assertSame('completed', $booking->fresh()->status);
        $this->assertDatabaseHas('gaming_sessions', ['booking_id' => $booking->id, 'status' => 'completed']);
    }

    private function fixture(): array
    {
        $user = User::factory()->create(['role' => 'customer']);
        $staff = User::factory()->create(['role' => 'staff']);
        $zone = PcZone::create([
            'slug' => 'test-zone', 'category' => 'Regular', 'booking_mode' => 'station',
            'name_translations' => ['en' => 'Test zone'], 'hourly_rate' => '100.00', 'currency' => 'TJS',
            'opens_at' => '00:00:00', 'closes_at' => '23:59:00', 'minimum_booking_minutes' => 60,
            'maximum_booking_minutes' => 480, 'maximum_days_ahead' => 30, 'is_active' => true,
        ]);
        $pc = Pc::create([
            'pc_zone_id' => $zone->id, 'name' => 'Test PC', 'asset_tag' => 'TEST-01',
            'specifications' => ['cpu' => 'Test CPU'], 'status' => 'available', 'is_bookable' => true,
        ]);
        Wallet::create(['user_id' => $user->id, 'cash_balance' => '500.00', 'bonus_balance' => '0.00', 'currency' => 'TJS']);

        return [$user, $staff, $zone, $pc];
    }
}
