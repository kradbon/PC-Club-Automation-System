<?php

namespace App\Services;

use App\Models\ClubOrder;
use App\Models\Tariff;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClubOrderService
{
    public function __construct(private readonly ActivityLogger $activity) {}

    public function requestForCustomer(User $customer, Tariff $tariff, User $actor): ClubOrder
    {
        return DB::transaction(function () use ($customer, $tariff, $actor): ClubOrder {
            $lockedTariff = Tariff::query()->lockForUpdate()->findOrFail($tariff->id);
            abort_unless($lockedTariff->is_active, 404);

            $order = ClubOrder::create([
                'user_id' => $customer->id,
                'tariff_id' => $lockedTariff->id,
                'order_number' => 'GB-'.Str::upper(Str::random(10)),
                'tariff_name' => $lockedTariff->name,
                'total_cents' => $lockedTariff->price_cents,
                'minutes' => $lockedTariff->minutes,
                'status' => 'pending',
            ]);
            $this->activity->record($actor, 'club_order.created_for_customer', $order, [
                'customer_id' => $customer->id,
                'tariff_id' => $lockedTariff->id,
                'total_cents' => $order->total_cents,
            ]);

            return $order;
        });
    }
}
