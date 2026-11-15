<?php

namespace App\Http\Controllers;

use App\Models\PcZone;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function index(): View
    {
        $rooms = PcZone::query()
            ->where('is_active', true)
            ->with(['pcs' => fn ($query) => $query->where('is_bookable', true)])
            ->orderBy('sort_order')
            ->get();

        return view('pages.rooms', compact('rooms'));
    }
}
