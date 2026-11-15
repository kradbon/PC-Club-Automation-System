<?php

namespace App\Http\Controllers;

use App\Models\ClubOrder;
use App\Models\GamingSession;
use App\Models\TournamentRegistration;
use App\Models\Tariff;
use App\Models\Booking;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function dashboard(Request $request): View
    {
        $user = $request->user();
        $recentOrders = $user->orders()->with('tariff')->latest()->limit(5)->get();
        $recentFoodOrders = $user->foodOrders()->with('items')->latest()->limit(5)->get();
        $wallet = app(WalletService::class)->ensureWallet($user);
        $bookings = $user->bookings()->with(['zone', 'pc'])->whereIn('status', ['pending', 'confirmed'])->orderBy('starts_at')->limit(5)->get();
        $nextBooking = $user->bookings()->with(['zone', 'pc'])->where('status', 'confirmed')->where('starts_at', '>=', now())->orderBy('starts_at')->first();
        $upcomingCount = $user->bookings()->whereIn('status', ['pending', 'confirmed'])->where('starts_at', '>=', now())->count();
        $transactions = $wallet->transactions()->latest('created_at')->limit(6)->get();
        $sessions = GamingSession::query()->with(['booking.zone', 'pc'])->where('user_id', $user->id)->latest('started_at')->limit(5)->get();
        $tournamentCount = TournamentRegistration::query()->where('user_id', $user->id)->count();

        return view('account.dashboard', compact('user', 'recentOrders', 'recentFoodOrders', 'wallet', 'bookings', 'nextBooking', 'upcomingCount', 'transactions', 'sessions', 'tournamentCount'));
    }

    public function profile(Request $request): View
    {
        return view('account.profile', ['user' => $request->user()]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'preferred_locale' => ['required', 'in:en,ru,tj'],
        ]);

        $request->user()->update($validated);

        return redirect()->route('account.profile')->with('success', __('ui.profile_saved'));
    }

    public function sessions(Request $request): View
    {
        $sessions = GamingSession::query()->with(['booking.zone', 'pc'])
            ->where('user_id', $request->user()->id)
            ->latest('started_at')
            ->paginate(15);

        return view('account.sessions', compact('sessions'));
    }

    public function tariffs(): View
    {
        $tariffs = Tariff::query()->where('is_active', true)->orderBy('sort_order')->get();

        return view('account.tariffs', compact('tariffs'));
    }

    public function orders(Request $request): View
    {
        $orders = $request->user()->orders()->with('tariff')->latest()->paginate(12);

        return view('account.orders', compact('orders'));
    }

    public function orderTariff(Request $request, string $locale, Tariff $tariff): RedirectResponse
    {
        abort_unless($tariff->is_active, 404);

        ClubOrder::create([
            'user_id' => $request->user()->id,
            'tariff_id' => $tariff->id,
            'order_number' => 'GB-'.Str::upper(Str::random(10)),
            'tariff_name' => $tariff->name,
            'total_cents' => $tariff->price_cents,
            'minutes' => $tariff->minutes,
            'status' => 'pending',
        ]);

        return redirect()->route('account.orders')->with('success', 'Order received. Hours will be added after payment is confirmed.');
    }
}
