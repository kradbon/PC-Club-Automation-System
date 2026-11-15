@extends('layouts.dashboard')
@section('title', __('ui.staff_queue').' | GB CLUB')
@section('content')
<div class="mx-auto max-w-[1440px] px-5 py-12 lg:px-10">
    <header class="ops-page-head"><div><p class="ops-kicker">GB CLUB / {{ __('dashboard.operations') }}</p><h1>{{ __('ui.staff_queue') }}</h1><p>{{ __('dashboard.arrivals_caption') }}</p></div></header>
    @include('staff.bookings._new-customer-order')
    <form class="ops-filter-bar" method="GET">
        <label>{{ __('dashboard.status') }}<select class="ops-input" name="status"><option value="">All statuses</option>@foreach(['pending','confirmed','declined','cancelled','completed'] as $status)<option value="{{ $status }}" @selected(request('status')===$status)>{{ __('ui.status_'.$status) }}</option>@endforeach</select></label>
        <label>{{ __('dashboard.starts') }}<input class="ops-input" name="date" type="date" value="{{ request('date') }}"></label>
        <label>{{ __('dashboard.customer') }}<input class="ops-input" name="customer" value="{{ request('customer') }}" placeholder="Name or email"></label>
        <label>{{ __('dashboard.zones') }}<select class="ops-input" name="pc_zone_id"><option value="">All zones</option>@foreach($zones as $zone)<option value="{{ $zone->id }}" @selected((string)request('pc_zone_id')===(string)$zone->id)>{{ $zone->localized('name') }}</option>@endforeach</select></label>
        <button class="ops-button" type="submit">Filter</button>
    </form>
    <div class="mt-7 divide-y divide-white/10 border-y border-white/10">
        @forelse($bookings as $booking)
            <article class="grid gap-4 py-5 xl:grid-cols-[1fr_auto] xl:items-center">
                <div>
                    <div class="flex flex-wrap items-center gap-3"><h2 class="font-display text-xl font-bold">{{ $booking->zone?->localized('name') }} / {{ $booking->pc?->name ?? __('ui.private_room') }}</h2><span class="font-mono text-[9px] uppercase text-acid">{{ __('ui.status_'.$booking->status) }}</span></div>
                    <p class="mt-2 text-sm text-white/60">{{ $booking->user?->name ?? __('dashboard.customer') }} · {{ $booking->user?->email }}</p>
                    <p class="mt-1 font-mono text-xs text-white/40">{{ $booking->starts_at->format('M j, Y H:i') }} - {{ $booking->ends_at->format('H:i') }} / {{ $booking->customer_note }}</p>
                    @if($booking->status==='confirmed')<p class="mt-1 text-xs text-acid">{{ number_format((float)$booking->total_price_snapshot,2) }} {{ $booking->currency }} / Cash {{ $booking->cash_debited }} + bonus {{ $booking->bonus_debited }}</p>@endif
                    @if($booking->gamingSession)<p class="mt-2 text-xs text-white/60">{{ __('dashboard.session') }}: {{ __('dashboard.session_status_'.$booking->gamingSession->status) }} @if($booking->gamingSession->pc) / {{ $booking->gamingSession->pc->name }} @endif</p>@endif
                </div>
                <div class="flex flex-wrap gap-2">
                    @if($booking->status==='pending')
                        <form method="POST" action="{{ route('staff.bookings.decide',$booking) }}">@csrf<input type="hidden" name="decision" value="confirm"><button class="arena-button !py-2">{{ __('ui.approve') }}</button></form>
                        <form class="flex gap-2" method="POST" action="{{ route('staff.bookings.decide',$booking) }}">@csrf<input type="hidden" name="decision" value="decline"><input class="arena-input !w-48 !py-2" name="reason" placeholder="{{ __('ui.decline_reason') }}" required><button class="border border-red-300/50 px-3 text-xs text-red-200">{{ __('ui.decline') }}</button></form>
                    @elseif($booking->status==='confirmed')
                        @if(!$booking->gamingSession && $booking->starts_at->copy()->subMinutes((int)config('club.session.checkin_early_minutes',30))->lte(now()) && $booking->ends_at->isFuture())
                            <form class="flex gap-2" method="POST" action="{{ route('staff.bookings.check-in',$booking) }}">@csrf<select class="arena-input !w-auto !py-2" name="pc_id" required>@foreach($booking->zone->pcs->where('is_bookable',true)->where('status','available') as $pc)<option value="{{ $pc->id }}" @selected($booking->pc_id===$pc->id)>{{ $pc->name }}</option>@endforeach</select><button class="arena-button !py-2">{{ __('dashboard.check_in') }}</button></form>
                        @endif
                        @if($booking->gamingSession?->status==='checked_in')<form method="POST" action="{{ route('staff.bookings.start-session',$booking) }}">@csrf<button class="arena-button !py-2">{{ __('dashboard.start_session') }}</button></form>@endif
                        @if($booking->gamingSession?->status==='active')<form method="POST" action="{{ route('staff.bookings.end-session',$booking) }}">@csrf<button class="arena-button !py-2">{{ __('dashboard.end_session') }}</button></form>@endif
                        @if(!$booking->gamingSession && $booking->starts_at->copy()->addMinutes((int)config('club.session.no_show_grace_minutes',15))->lte(now()))<form method="POST" action="{{ route('staff.bookings.no-show',$booking) }}">@csrf<button class="border border-red-300/40 px-3 py-2 text-[10px] text-red-200">{{ __('dashboard.mark_no_show') }}</button></form>@endif
                        <form method="POST" action="{{ route('staff.bookings.cancel',$booking) }}">@csrf<button class="border border-red-300/40 px-3 py-2 text-[10px] text-red-200">{{ __('ui.cancel_booking') }}</button></form>
                    @endif
                </div>
                @if(in_array($booking->status, ['pending', 'confirmed'], true) && $booking->user?->account_status === 'active')
                    @include('staff.bookings._quick-orders', ['booking' => $booking])
                @endif
            </article>
        @empty<p class="py-8 text-white/50">{{ __('ui.no_pending_bookings') }}</p>@endforelse
    </div>
    {{ $bookings->links() }}
</div>
@endsection
