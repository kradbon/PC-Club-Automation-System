@extends('layouts.dashboard')
@section('title', __('ui.food_orders_title').' | GB CLUB')
@section('content')
<div class="mx-auto max-w-[1440px] px-5 py-12 lg:px-10 food-page">
    <header class="food-heading"><div><p class="font-mono text-[10px] uppercase tracking-[.3em] text-acid">GB CLUB / OPERATIONS / FOOD</p><h1 class="mt-3 font-display text-5xl font-bold">{{ __('ui.food_queue_title') }}</h1><p class="mt-4 max-w-2xl text-sm leading-7 text-white/55">{{ __('ui.food_queue_copy') }}</p></div><a class="arena-button arena-button-outline" href="{{ route('staff.bookings.index') }}">{{ __('ui.staff_queue') }}</a></header>
    <section class="food-staff-list">
        @forelse($orders as $order)
            <article class="food-staff-order">
                <div class="food-order-top"><div><strong>{{ $order->order_number }}</strong><p>{{ $order->user?->name }} / {{ $order->user?->email }}</p></div><span class="food-order-status food-status-{{ $order->status }}">{{ __('ui.food_status_'.$order->status) }}</span></div>
                <p class="food-order-meta">{{ $order->created_at->format('M j, Y / H:i') }} / {{ $order->fulfillment_method === 'delivery' ? __('ui.food_delivery') : __('ui.food_in_club') }}@if($order->booking) / {{ __('dashboard.reservation_id') }} #{{ $order->booking->id }}@endif</p>
                @if($order->fulfillment_method === 'delivery')<p class="food-order-address">{{ $order->delivery_address }} / {{ $order->delivery_phone }}</p>@elseif($order->location_note)<p class="food-order-address">{{ $order->location_note }}</p>@endif
                <ul class="food-staff-items">@foreach($order->items as $line)<li>{{ $line->quantity }} × {{ $line->item_name }} <span>{{ number_format($line->line_total_cents / 100, 2) }} {{ $order->currency }}</span></li>@endforeach</ul>
                @if($order->customer_note)<p class="food-order-address">{{ __('ui.food_customer_note') }}: {{ $order->customer_note }}</p>@endif
                <div class="food-order-total"><span>{{ __('ui.food_total') }}</span><strong>{{ $order->currency }} {{ number_format($order->total_cents / 100, 2) }}</strong></div>
                @if($order->allowedNextStatuses())<form class="food-staff-actions" method="POST" action="{{ route('staff.food-orders.update', $order) }}">@csrf @method('PUT')<label>{{ __('ui.food_update_status') }}<select class="arena-input" name="status">@foreach($order->allowedNextStatuses() as $status)<option value="{{ $status }}">{{ __('ui.food_status_'.$status) }}</option>@endforeach</select></label><button class="arena-button" type="submit">{{ __('ui.save_changes') }}</button></form>@endif
            </article>
        @empty<p class="food-empty">{{ __('ui.food_order_empty') }}</p>@endforelse
    </section>
    {{ $orders->links() }}
</div>
@endsection
