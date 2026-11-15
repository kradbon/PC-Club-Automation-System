<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFoodOrderRequest;
use App\Models\FoodItem;
use App\Services\FoodOrderingService;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FoodOrderController extends Controller
{
    public function index(Request $request): View
    {
        $items = FoodItem::query()
            ->where('is_available', true)
            ->orderBy('category')
            ->orderBy('sort_order')
            ->get();
        $orders = $request->user()->foodOrders()->with('items')->latest()->paginate(10);

        return view('account.food', compact('items', 'orders'));
    }

    public function store(StoreFoodOrderRequest $request, FoodOrderingService $food, ActivityLogger $activity): RedirectResponse
    {
        $order = $food->place($request->user(), $request->validated(), $activity);

        return redirect()->route('account.food')->with('success', __('ui.food_order_placed', ['number' => $order->order_number]));
    }
}
