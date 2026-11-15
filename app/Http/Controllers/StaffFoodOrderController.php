<?php

namespace App\Http\Controllers;

use App\Models\FoodOrder;
use App\Services\FoodOrderingService;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffFoodOrderController extends Controller
{
    public function index(): View
    {
        $orders = FoodOrder::query()
            ->with(['user', 'items', 'booking'])
            ->latest()
            ->paginate(20);

        return view('staff.food-orders.index', compact('orders'));
    }

    public function update(Request $request, string $locale, FoodOrder $foodOrder, FoodOrderingService $food, ActivityLogger $activity): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:accepted,preparing,ready,out_for_delivery,completed,cancelled']]);
        $food->updateStatus($foodOrder, $data['status'], $request->user(), $activity);

        return back()->with('success', __('ui.food_status_updated'));
    }
}
