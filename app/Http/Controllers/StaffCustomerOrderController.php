<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStaffCustomerOrderRequest;
use App\Models\ClubOrder;
use App\Models\Tariff;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\FoodOrderingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StaffCustomerOrderController extends Controller
{
    public function store(StoreStaffCustomerOrderRequest $request, FoodOrderingService $food, ActivityLogger $activity): RedirectResponse
    {
        $data = $request->validated();
        $customer = User::query()->whereIn('role', ['customer', 'member'])->where('account_status', 'active')->findOrFail($data['customer_id']);

        if ($data['order_type'] === 'package') {
            $tariff = Tariff::query()->where('is_active', true)->findOrFail($data['tariff_id']);
            $actor = $request->user();
            $order = DB::transaction(function () use ($customer, $tariff, $actor, $activity): ClubOrder {
                $order = ClubOrder::create([
                    'user_id' => $customer->id,
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
                    'tariff_id' => $tariff->id,
                ]);

                return $order;
            });

            return back()->with('success', __('dashboard.package_order_created', ['number' => $order->order_number]));
        }

        $foodData = collect($data)->only([
            'request_key', 'fulfillment_method', 'items', 'delivery_address', 'delivery_phone', 'location_note', 'customer_note',
        ])->all();
        $order = $food->place($customer, $foodData, $activity, $request->user());

        return back()->with('success', __('ui.food_order_placed', ['number' => $order->order_number]));
    }
}
