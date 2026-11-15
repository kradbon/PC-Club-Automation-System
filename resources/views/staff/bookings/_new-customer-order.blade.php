<details class="ops-panel mb-6" style="padding:14px">
    <summary class="cursor-pointer font-mono text-xs uppercase text-acid">+ {{ __('dashboard.new_customer_order') }}</summary>
    <form class="mt-4 grid gap-4 lg:grid-cols-2" method="POST" action="{{ route('staff.customer-orders.store') }}" data-food-cart>@csrf
        <label>{{ __('dashboard.customer_account') }}
            <input class="ops-input mt-1" type="search" data-customer-filter placeholder="{{ __('dashboard.filter_customers') }}">
            <select class="ops-input mt-1" name="customer_id" required data-customer-picker @disabled($customers->isEmpty())>
                <option value="">{{ __('dashboard.select_customer') }}</option>
                @foreach($customers as $customer)<option value="{{ $customer->id }}" data-search="{{ mb_strtolower($customer->name.' '.$customer->id) }}">{{ $customer->name }} · #{{ $customer->id }}</option>@endforeach
            </select>
        </label>
        <label>{{ __('dashboard.order_type') }}<select class="ops-input mt-1" name="order_type" data-order-type><option value="package">{{ __('dashboard.quick_package_order') }}</option><option value="food">{{ __('dashboard.quick_food_order') }}</option></select></label>
        <section class="rounded-lg border border-white/10 p-4 lg:col-span-2" data-order-package-fields>
            <label>{{ __('dashboard.package') }}<select class="ops-input mt-1" name="tariff_id" data-package-picker @disabled($tariffs->isEmpty())>@foreach($tariffs as $tariff)<option value="{{ $tariff->id }}">{{ $tariff->name }} · {{ $tariff->minutes }} min · {{ number_format($tariff->price_cents / 100, 2) }} TJS</option>@endforeach</select></label>
        </section>
        <section class="hidden rounded-lg border border-white/10 p-4 lg:col-span-2" data-order-food-fields>
            <input type="hidden" name="request_key" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
            <div class="grid gap-3">
                <label>{{ __('ui.food_fulfillment') }}<select class="ops-input mt-1" name="fulfillment_method" data-fulfillment><option value="club">{{ __('ui.food_in_club') }}</option><option value="delivery">{{ __('ui.food_delivery') }}</option></select></label>
                <div class="grid gap-2 sm:grid-cols-[1fr_90px_auto]">
                    <label class="min-w-0">{{ __('dashboard.menu_item') }}<select class="ops-input mt-1" name="food_item_id" data-item-picker @disabled($foodItems->isEmpty())><option value="">{{ __('dashboard.choose_menu_item') }}</option>@foreach($foodItems as $item)<option value="{{ $item->id }}" data-name="{{ $item->name }}" data-price="{{ $item->price_cents }}" data-currency="{{ $item->currency }}">{{ $item->category }} · {{ $item->name }} · {{ number_format($item->price_cents / 100, 2) }} {{ $item->currency }}</option>@endforeach</select></label>
                    <label>{{ __('ui.food_quantity') }}<input class="ops-input mt-1" type="number" name="item_quantity" min="1" max="12" value="1" data-item-quantity></label>
                    <button class="ops-button self-end !py-2" type="button" data-add-item @disabled($foodItems->isEmpty())>{{ __('dashboard.add_item') }}</button>
                </div>
                <div class="rounded-md bg-black/25 p-3"><ul class="grid gap-2" data-cart-items><li class="text-sm text-white/50" data-cart-empty>{{ __('ui.food_select_item') }}</li></ul><div class="mt-3 flex justify-between border-t border-white/10 pt-3 text-sm"><span>{{ __('ui.food_estimate') }}</span><strong class="text-acid" data-cart-total>0.00</strong></div><div data-cart-fields></div></div>
                <div data-club-location><label>{{ __('ui.food_location_note') }}<input class="ops-input mt-1" name="location_note" maxlength="255" placeholder="{{ __('ui.food_location_placeholder') }}"></label></div>
                <details data-delivery-fields><summary class="cursor-pointer text-xs text-white/60">{{ __('ui.food_delivery') }} - {{ __('ui.food_delivery_address') }}</summary><div class="mt-2 grid gap-2 sm:grid-cols-2"><label>{{ __('ui.food_delivery_address') }}<input class="ops-input mt-1" name="delivery_address" maxlength="1200"></label><label>{{ __('ui.food_phone') }}<input class="ops-input mt-1" name="delivery_phone" maxlength="40"></label></div></details>
                <label>{{ __('ui.food_customer_note') }}<input class="ops-input mt-1" name="customer_note" maxlength="1000"></label>
            </div>
        </section>
        <button class="ops-button lg:col-span-2" type="submit" data-submit-order @disabled($customers->isEmpty())>{{ __('dashboard.create_customer_order') }}</button>
    </form>
</details>
