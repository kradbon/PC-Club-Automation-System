@extends('layouts.dashboard')
@section('title', __('ui.food').' | GB CLUB')
@section('content')
<div class="mx-auto max-w-[1440px] px-5 py-12 lg:px-10 food-page">
    <header class="food-heading">
        <div><p class="font-mono text-[10px] uppercase tracking-[.3em] text-acid">GB CLUB / {{ __('ui.my_account') }} / FOOD</p><h1 class="mt-3 font-display text-5xl font-bold">{{ __('ui.food_title') }}</h1><p class="mt-4 max-w-2xl text-sm leading-7 text-white/55">{{ __('ui.food_copy') }}</p></div>
        <a class="arena-button arena-button-outline" href="{{ route('account.orders') }}">{{ __('ui.orders') }} &rarr;</a>
    </header>

    <div class="food-layout">
        <section>
            <div class="food-section-heading"><div><p class="font-mono text-[10px] uppercase tracking-[.3em] text-acid">{{ __('ui.food_menu') }}</p><h2 class="font-display text-2xl font-bold">{{ __('ui.food_choose') }}</h2></div><span class="food-currency">{{ __('ui.currency_tjs') }}</span></div>
            @if($items->isNotEmpty())
            <form method="POST" action="{{ route('account.food.store') }}" class="food-order-form">
                @csrf
                <input type="hidden" name="request_key" value="{{ old('request_key', (string) \Illuminate\Support\Str::uuid()) }}">
                <div class="food-menu-grid">
                    @foreach($items as $item)
                    <article class="food-card">
                        @if($item->image_url)<img class="food-card-image" src="{{ $item->image_url }}" alt="{{ $item->name }}" loading="lazy">@else<div class="food-card-image food-card-placeholder" aria-hidden="true">GB</div>@endif
                        <div class="food-card-content"><span class="food-category">{{ $item->category }}</span><h3>{{ $item->name }}</h3><p>{{ $item->description }}</p>
                            <div class="food-card-footer"><strong>{{ $item->currency }} {{ number_format($item->price_cents / 100, 2) }}</strong><label>{{ __('ui.food_quantity') }}<input type="number" class="arena-input food-quantity" name="items[{{ $item->id }}]" min="0" max="12" value="{{ old('items.'.$item->id, 0) }}" data-unit-price="{{ $item->price_cents }}" inputmode="numeric"></label></div>
                        </div>
                    </article>
                    @endforeach
                </div>
                @error('items')<p class="food-error" role="alert">{{ $message }}</p>@enderror
                <div class="food-checkout" data-food-checkout data-delivery-fee-cents="{{ (int) config('club.food.delivery_fee_cents', 0) }}" data-currency="{{ config('club.currency', 'TJS') }}">
                    <div><p class="font-mono text-[10px] uppercase tracking-[.3em] text-acid">{{ __('ui.food_fulfillment') }}</p><h2 class="font-display text-2xl font-bold">{{ __('ui.food_where') }}</h2><p class="text-sm text-white/50">{{ __('ui.food_pay_on_arrival') }}</p></div>
                    <div class="food-checkout-fields">
                        <label>{{ __('ui.food_fulfillment') }}<select class="arena-input" name="fulfillment_method" data-food-fulfillment required><option value="club" @selected(old('fulfillment_method', 'club') === 'club')>{{ __('ui.food_in_club') }}</option><option value="delivery" @selected(old('fulfillment_method') === 'delivery')>{{ __('ui.food_delivery') }}</option></select></label>
                        <div class="food-location-fields" data-food-club-fields @if(old('fulfillment_method', 'club') !== 'club') hidden @endif><label>{{ __('ui.food_location_note') }}<input class="arena-input" type="text" name="location_note" value="{{ old('location_note') }}" maxlength="255" placeholder="{{ __('ui.food_location_placeholder') }}"></label></div>
                        <div class="food-location-fields" data-food-delivery-fields @if(old('fulfillment_method') !== 'delivery') hidden @endif><label>{{ __('ui.food_delivery_address') }}<textarea class="arena-input" name="delivery_address" maxlength="1200" data-food-address>{{ old('delivery_address') }}</textarea></label><label>{{ __('ui.food_phone') }}<input class="arena-input" name="delivery_phone" type="tel" maxlength="40" value="{{ old('delivery_phone') }}" data-food-phone></label></div>
                        <label class="food-note-field">{{ __('ui.food_customer_note') }}<textarea class="arena-input" name="customer_note" maxlength="1000">{{ old('customer_note') }}</textarea></label>
                    </div>
                    @error('fulfillment_method')<p class="food-error">{{ $message }}</p>@enderror @error('delivery_address')<p class="food-error">{{ $message }}</p>@enderror @error('delivery_phone')<p class="food-error">{{ $message }}</p>@enderror
                    <p class="food-estimate" data-food-estimate data-label="{{ __('ui.food_estimate') }}">{{ __('ui.food_estimate') }}: {{ config('club.currency', 'TJS') }} 0.00</p>
                    <button class="arena-button" type="submit">{{ __('ui.food_order_submit') }} &rarr;</button>
                </div>
            </form>
            @else
                <p class="food-empty">{{ __('ui.food_empty') }}</p>
            @endif
        </section>

        <aside class="food-history">
            <div class="food-section-heading"><div><p class="font-mono text-[10px] uppercase tracking-[.3em] text-acid">{{ __('ui.my_account') }}</p><h2 class="font-display text-2xl font-bold">{{ __('ui.food_orders_title') }}</h2></div></div>
            @forelse($orders as $order)
                <article class="food-order-card"><div class="food-order-top"><strong>{{ $order->order_number }}</strong><span class="food-order-status food-status-{{ $order->status }}">{{ __('ui.food_status_'.$order->status) }}</span></div><p class="food-order-meta">{{ $order->created_at->format('M j, Y / H:i') }} / {{ $order->fulfillment_method === 'delivery' ? __('ui.food_delivery') : __('ui.food_in_club') }}</p><ul>@foreach($order->items as $line)<li>{{ $line->quantity }} &times; {{ $line->item_name }}<span>{{ number_format($line->line_total_cents / 100, 2) }}</span></li>@endforeach</ul>@if($order->fulfillment_method === 'delivery')<p class="food-order-address">{{ $order->delivery_address }}</p>@elseif($order->location_note)<p class="food-order-address">{{ $order->location_note }}</p>@endif<div class="food-order-total"><span>{{ __('ui.food_total') }}</span><strong>{{ $order->currency }} {{ number_format($order->total_cents / 100, 2) }}</strong></div></article>
            @empty
                <p class="food-empty">{{ __('ui.food_order_empty') }}</p>
            @endforelse
            {{ $orders->links() }}
        </aside>
    </div>
</div>
@endsection
