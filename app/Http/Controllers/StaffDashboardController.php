<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\GamingSession;
use App\Models\Pc;
use App\Models\PcZone;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $todayBookings = Booking::query()->whereDate('starts_at', today());
        $activeSessions = GamingSession::query()->where('status', 'active');
        $occupiedPcIds = (clone $activeSessions)->whereNotNull('pc_id')->pluck('pc_id')->unique();

        $stats = [
            'pending' => Booking::query()->where('status', 'pending')->count(),
            'arrivals' => (clone $todayBookings)->whereIn('status', ['confirmed', 'pending'])->count(),
            'active' => (clone $activeSessions)->count(),
            'occupied' => $occupiedPcIds->count(),
            'available' => Pc::query()->where('status', 'available')->where('is_bookable', true)
                ->whereNotIn('id', $occupiedPcIds)->count(),
            'attention' => Pc::query()->whereIn('status', ['maintenance', 'unavailable'])->count(),
        ];
        $nextBookings = Booking::with(['user', 'zone', 'pc'])
            ->whereIn('status', ['confirmed', 'pending'])->where('starts_at', '>=', now())
            ->orderBy('starts_at')->limit(8)->get();
        $activeSessions = (clone $activeSessions)->with(['booking.zone', 'user', 'pc'])
            ->orderBy('started_at')->limit(8)->get();
        $maintenancePcs = Pc::with('zone')->whereIn('status', ['maintenance', 'unavailable'])
            ->orderBy('name')->limit(6)->get();

        return view('staff.dashboard', compact('stats', 'nextBookings', 'activeSessions', 'maintenancePcs'));
    }

    public function pcs(): View
    {
        $zones = PcZone::with(['pcs' => fn ($query) => $query->orderBy('name')])->orderBy('sort_order')->get();
        $activeSessions = GamingSession::query()->with('booking')->where('status', 'active')
            ->whereNotNull('pc_id')->orderBy('started_at')->get()->keyBy('pc_id');
        $activePcIds = $activeSessions->keys()->map(fn ($id) => (int) $id)->all();

        return view('staff.pcs', compact('zones', 'activePcIds', 'activeSessions'));
    }

    public function profile(Request $request): View
    {
        return view('staff.profile', ['user' => $request->user()]);
    }
}
