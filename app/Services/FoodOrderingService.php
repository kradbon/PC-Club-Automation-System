<?php

namespace App\Services;

use App\Models\FoodItem;
use App\Models\FoodOrder;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FoodOrderingService
{
    public function place(User $user, array $data, ActivityLogger $activity, ?User $actor = null, ?Booking $booking = null): FoodOrder
    {
        if ($booking && (int) $booking->user_id !== (int) $user->id) {
            abort(403);
        }

        if ($existing = FoodOrder::query()->where('user_id', $user->id)->where('request_key', $data['request_key'])->first()) {
            return $existing->load('items');
        }

        $quantities = collect($data['items'])
            ->filter(fn ($quantity, $id) => ctype_digit((string) $id) && (int) $quantity > 0)
            ->mapWithKeys(fn ($quantity, $id) => [(int) $id => (int) $quantity]);

        if ($quantities->isEmpty()) {
            throw ValidationException::withMessages(['items' => __('ui.food_select_item')]);
        }

        try {
            return DB::transaction(function () use ($user, $data, $quantities, $activity, $actor, $booking): FoodOrder {
                $items = FoodItem::query()
                    ->whereIn('id', $quantities->keys())
                    ->where('is_available', true)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                if ($items->count() !== $quantities->count()) {
                    throw ValidationException::withMessages(['items' => __('ui.food_unavailable')]);
                }

                $currencies = $items->pluck('currency')->unique();
                if ($currencies->count() !== 1) {
                    throw ValidationException::withMessages(['items' => __('ui.food_currency_error')]);
                }

                $subtotal = $quantities->sum(fn (int $quantity, int $id): int => $items[$id]->price_cents * $quantity);
                $deliveryFee = $data['fulfillment_method'] === 'delivery'
                    ? max(0, (int) config('club.food.delivery_fee_cents', 0))
                    : 0;

                $order = FoodOrder::create([
                    'user_id' => $user->id,
                    'booking_id' => $booking?->id,
                    'request_key' => $data['request_key'],
                    'order_number' => 'FO-'.Str::upper(Str::random(10)),
                    'fulfillment_method' => $data['fulfillment_method'],
                    'delivery_address' => $data['fulfillment_method'] === 'delivery' ? $data['delivery_address'] : null,
                    'delivery_phone' => $data['fulfillment_method'] === 'delivery' ? $data['delivery_phone'] : null,
                    'location_note' => $data['location_note'] ?? null,
                    'subtotal_cents' => $subtotal,
                    'delivery_fee_cents' => $deliveryFee,
                    'total_cents' => $subtotal + $deliveryFee,
                    'currency' => $currencies->first(),
                    'status' => 'pending',
                    'customer_note' => $data['customer_note'] ?? null,
                ]);

                foreach ($quantities as $id => $quantity) {
                    $item = $items[$id];
                    $order->items()->create([
                        'food_item_id' => $item->id,
                        'item_name' => $item->name,
                        'quantity' => $quantity,
                        'unit_price_cents' => $item->price_cents,
                        'line_total_cents' => $item->price_cents * $quantity,
                    ]);
                }

                $activity->record($actor ?? $user, $actor ? 'food_order.created_for_customer' : 'food_order.created', $order, [
                    'order_number' => $order->order_number,
                    'fulfillment_method' => $order->fulfillment_method,
                    'total_cents' => $order->total_cents,
                    ...($actor ? ['customer_id' => $user->id] : []),
                ]);

                return $order->load('items');
            });
        } catch (UniqueConstraintViolationException $exception) {
            return FoodOrder::query()
                ->where('user_id', $user->id)
                ->where('request_key', $data['request_key'])
                ->with('items')
                ->firstOrFail();
        }
    }

    public function updateStatus(FoodOrder $order, string $status, User $actor, ActivityLogger $activity): FoodOrder
    {
        return DB::transaction(function () use ($order, $status, $actor): FoodOrder {
            $lockedOrder = FoodOrder::query()->lockForUpdate()->findOrFail($order->id);
            if (! in_array($status, $lockedOrder->allowedNextStatuses(), true)) {
                throw ValidationException::withMessages(['status' => __('ui.food_invalid_transition')]);
            }

            $previousStatus = $lockedOrder->status;
            $lockedOrder->status = $status;
            if ($status === 'accepted') {
                $lockedOrder->accepted_by = $actor->id;
                $lockedOrder->accepted_at = now();
            }
            if ($status === 'completed') {
                $lockedOrder->completed_at = now();
            }
            if ($status === 'cancelled') {
                $lockedOrder->cancelled_at = now();
            }
            $lockedOrder->save();
            $activity->record($actor, 'food_order.status_changed', $lockedOrder, [
                'from' => $previousStatus,
                'to' => $status,
            ]);

            return $lockedOrder->load(['user', 'items']);
        });
    }
}
