<?php

namespace App\Http\Controllers;

use App\Models\ClubEvent;
use App\Models\PcZone;
use App\Models\Tournament;
use App\Models\ClubReview;
use Illuminate\View\View;

class ClubEventController extends Controller
{
    public function index(): View
    {
        $events = ClubEvent::query()
            ->where('is_published', true)
            ->where('starts_at', '>=', now()->startOfDay())
            ->orderBy('starts_at')
            ->limit(3)
            ->get();

        $zones = PcZone::query()->where('is_active', true)->withCount('pcs')->orderBy('sort_order')->get();
        $zoneCount = PcZone::query()->where('is_active', true)->count();
        $pcCount = \App\Models\Pc::query()->where('is_bookable', true)->count();
        $tournaments = Tournament::query()->whereIn('status', ['registration', 'published'])->orderBy('starts_at')->limit(2)->get();
        $reviews = ClubReview::query()->where('status', 'approved')->with('user')->latest('moderated_at')->limit(6)->get();
        $myReview = auth()->check() ? ClubReview::query()->where('user_id', auth()->id())->first() : null;

        return view('pages.home', compact('events', 'zones', 'tournaments', 'reviews', 'myReview', 'zoneCount', 'pcCount'));
    }

    public function club(): View
    {
        return view('pages.club');
    }

    public function events(): View
    {
        $events = ClubEvent::query()
            ->where('is_published', true)
            ->where('starts_at', '>=', now()->startOfDay())
            ->orderBy('starts_at')
            ->get();

        return view('pages.events', compact('events'));
    }

    public function setup(): View
    {
        return view('pages.setup');
    }
}
