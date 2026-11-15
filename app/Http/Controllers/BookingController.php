<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Pc;
use App\Models\PcZone;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $bookings = $request->user()->bookings()->with(['zone', 'pc'])->latest('starts_at')->paginate(12);

        return view('account.bookings.index', compact('bookings'));
    }

    public function create(Request $request): View
    {
        $zones = PcZone::query()->where('is_active', true)->with(['pcs' => fn ($query) => $query
            ->where('status', 'available')->where('is_bookable', true)->orderBy('name')])->orderBy('sort_order')->get();

        return view('account.bookings.create', compact('zones'));
    }

    public function store(StoreBookingRequest $request, string $locale, BookingService $bookings): RedirectResponse
    {
        $this->authorize('create', Booking::class);
        $booking = $bookings->request($request->user(), $request->validated());

        return redirect()->route('account.bookings.index')->with('success', __('ui.booking_submitted', [], app()->getLocale()));
    }

    public function cancel(Request $request, string $locale, Booking $booking, BookingService $bookings): RedirectResponse
    {
        $this->authorize('cancel', $booking);
        $bookings->cancel($booking, $request->user());

        return back()->with('success', __('ui.booking_cancelled', [], app()->getLocale()));
    }

    public function availability(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'zone_id' => ['required', 'integer', 'exists:pc_zones,id'],
            'starts_at' => ['required', 'date', 'after:now'],
            'duration_minutes' => ['required', 'integer', 'min:30', 'max:720'],
        ]);
        $startsAt = \Illuminate\Support\Carbon::parse($validated['starts_at']);
        $endsAt = $startsAt->copy()->addMinutes((int) $validated['duration_minutes']);
        $zone = PcZone::findOrFail($validated['zone_id']);
        abort_unless($zone->is_active, 404);

        $pcs = $zone->pcs()->where('status', 'available')->where('is_bookable', true)
            ->whereDoesntHave('bookings', fn ($query) => $query->where('status', 'confirmed')
                ->where('starts_at', '<', $endsAt)->where('ends_at', '>', $startsAt))
            ->whereDoesntHave('maintenancePeriods', fn ($query) => $query
                ->where('starts_at', '<', $endsAt)->where('ends_at', '>', $startsAt))
            ->orderBy('name')->get(['id', 'name', 'specifications']);

        return response()->json(['pcs' => $pcs, 'private_zone' => $zone->booking_mode === 'private_room']);
    }
}
