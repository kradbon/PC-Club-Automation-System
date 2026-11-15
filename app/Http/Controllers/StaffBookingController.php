<?php

namespace App\Http\Controllers;

use App\Http\Requests\DecideBookingRequest;
use App\Http\Requests\StoreStaffFoodOrderRequest;
use App\Models\Booking;
use App\Models\ClubOrder;
use App\Models\GamingSession;
use App\Models\PcZone;
use App\Models\Tariff;
use App\Models\FoodItem;
use App\Models\User;
use App\Services\BookingService;
use App\Services\ActivityLogger;
use App\Services\FoodOrderingService;
use App\Services\GamingSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class StaffBookingController extends Controller
{
    public function index(Request $request): View
    {
        $query = Booking::with(['user', 'zone.pcs', 'pc', 'gamingSession.pc'])->latest('starts_at');
        if ($request->filled('status') && in_array($request->string('status')->toString(), ['pending', 'confirmed', 'declined', 'cancelled', 'completed'], true)) {
            $query->where('status', $request->string('status')->toString());
        }
        if ($request->filled('date')) {
            $query->whereDate('starts_at', $request->string('date')->toString());
        }
        if ($request->filled('customer')) {
            $search = '%'.$request->string('customer')->toString().'%';
            $query->whereHas('user', fn ($users) => $users->where('name', 'like', $search)->orWhere('email', 'like', $search));
        }
        if ($request->filled('pc_zone_id') && ctype_digit($request->string('pc_zone_id')->toString())) {
            $query->where('pc_zone_id', (int) $request->string('pc_zone_id')->toString());
        }

        $bookings = $query->paginate(20)->withQueryString();
        $zones = PcZone::query()->orderBy('sort_order')->get();
        $tariffs = Tariff::query()->where('is_active', true)->orderBy('sort_order')->get();
        $foodItems = FoodItem::query()->where('is_available', true)->orderBy('category')->orderBy('sort_order')->get();
        $customers = User::query()->whereIn('role', ['customer', 'member'])->where('account_status', 'active')
            ->orderBy('name')->limit(1000)->get(['id', 'name']);

        return view('staff.bookings.index', compact('bookings', 'zones', 'tariffs', 'foodItems', 'customers'));
    }

    public function decide(DecideBookingRequest $request, string $locale, Booking $booking, BookingService $service): RedirectResponse
    {
        $this->authorize('confirm', Booking::class);
        $data = $request->validated();

        if ($data['decision'] === 'confirm') {
            $service->confirm($booking, $request->user());
            return back()->with('success', __('ui.booking_confirmed', [], app()->getLocale()));
        }

        $service->decline($booking, $request->user(), $data['reason']);

        return back()->with('success', __('ui.booking_declined', [], app()->getLocale()));
    }

    public function cancel(Request $request, string $locale, Booking $booking, BookingService $service): RedirectResponse
    {
        $this->authorize('viewAny', Booking::class);
        $service->cancel($booking, $request->user());

        return back()->with('success', __('ui.booking_cancelled', [], app()->getLocale()));
    }

    public function complete(Request $request, string $locale, Booking $booking, BookingService $service): RedirectResponse
    {
        $this->authorize('viewAny', Booking::class);
        $service->complete($booking, $request->user());

        return back()->with('success', __('ui.booking_completed', [], app()->getLocale()));
    }

    public function checkIn(Request $request, string $locale, Booking $booking, GamingSessionService $sessions): RedirectResponse
    {
        $this->authorize('viewAny', Booking::class);
        $data = $request->validate(['pc_id' => ['required', 'integer', 'exists:pcs,id']]);
        $sessions->checkIn($booking, $request->user(), (int) $data['pc_id']);

        return back()->with('success', __('dashboard.session_checked_in'));
    }

    public function startSession(Request $request, string $locale, Booking $booking, GamingSessionService $sessions): RedirectResponse
    {
        $this->authorize('viewAny', Booking::class);
        $session = $booking->gamingSession()->firstOrFail();
        $sessions->start($session, $request->user());

        return back()->with('success', __('dashboard.session_started'));
    }

    public function endSession(Request $request, string $locale, Booking $booking, GamingSessionService $sessions): RedirectResponse
    {
        $this->authorize('viewAny', Booking::class);
        $session = $booking->gamingSession()->firstOrFail();
        $sessions->end($session, $request->user());

        return back()->with('success', __('dashboard.session_ended'));
    }

    public function noShow(Request $request, string $locale, Booking $booking, GamingSessionService $sessions): RedirectResponse
    {
        $this->authorize('viewAny', Booking::class);
        $sessions->noShow($booking, $request->user());

        return back()->with('success', __('dashboard.session_no_show_saved'));
    }

    public function packageOrder(Request $request, string $locale, Booking $booking, ActivityLogger $activity): RedirectResponse
    {
        $this->authorize('viewAny', Booking::class);
        $customer = $this->orderableCustomer($booking);
        $data = $request->validate(['tariff_id' => ['required', 'integer', 'exists:tariffs,id']]);
        $tariff = Tariff::query()->where('is_active', true)->findOrFail($data['tariff_id']);
        $actor = $request->user();

        $order = DB::transaction(function () use ($booking, $customer, $tariff, $actor, $activity): ClubOrder {
            $order = ClubOrder::create([
                'user_id' => $customer->id,
                'booking_id' => $booking->id,
                'tariff_id' => $tariff->id,
                'processed_by' => $actor->id,
                'order_number' => 'GB-'.Str::upper(Str::random(10)),
                'tariff_name' => $tariff->name,
                'total_cents' => $tariff->price_cents,
                'minutes' => $tariff->minutes,
                'status' => 'pending',
            ]);
            $activity->record($actor, 'club_order.created_for_customer', $order, [
                'customer_id' => $customer->id,
                'booking_id' => $booking->id,
                'tariff_id' => $tariff->id,
            ]);

            return $order;
        });

        return back()->with('success', __('dashboard.package_order_created', ['number' => $order->order_number]));
    }

    public function foodOrder(StoreStaffFoodOrderRequest $request, string $locale, Booking $booking, FoodOrderingService $food, ActivityLogger $activity): RedirectResponse
    {
        $this->authorize('viewAny', Booking::class);
        $customer = $this->orderableCustomer($booking);
        $order = $food->place($customer, $request->validated(), $activity, $request->user(), $booking);

        return back()->with('success', __('ui.food_order_placed', ['number' => $order->order_number]));
    }

    private function orderableCustomer(Booking $booking): User
    {
        abort_unless(in_array($booking->status, ['pending', 'confirmed'], true), 422);
        $customer = $booking->user;
        abort_unless($customer
            && in_array($customer->role, ['customer', 'member'], true)
            && $customer->account_status === 'active', 422);

        return $customer;
    }
}
