@extends('layouts.dashboard')
@section('title', __('dashboard.overview'))
@section('page-title', __('dashboard.overview'))
@section('content')
<div class="ops-greeting"><div><p class="ops-kicker">{{ __('dashboard.staff_workspace') }} / {{ now()->format('F j, Y') }}</p><h1>{{ __('dashboard.greeting', ['name' => auth()->user()->name]) }}</h1><p>{{ __('dashboard.staff_summary') }}</p></div><div class="ops-action-row"><a class="ops-button" href="{{ route('staff.bookings.index') }}">{{ __('dashboard.reservations') }} →</a><a class="ops-button ops-button-secondary" href="{{ route('staff.pcs') }}">{{ __('dashboard.pc_status') }}</a></div></div>

<section class="ops-kpi-grid" aria-label="{{ __('dashboard.shift_status') }}">
    <article class="ops-kpi"><div class="ops-kpi-label">{{ __('dashboard.pending_reservations') }} <span class="ops-kpi-icon">⌛</span></div><strong class="ops-kpi-value">{{ number_format($stats['pending']) }}</strong><span class="ops-kpi-note">{{ __('dashboard.reservations') }}</span></article>
    <article class="ops-kpi"><div class="ops-kpi-label">{{ __('dashboard.today_arrivals') }} <span class="ops-kpi-icon">◷</span></div><strong class="ops-kpi-value">{{ number_format($stats['arrivals']) }}</strong><span class="ops-kpi-note">{{ __('dashboard.arrivals_caption') }}</span></article>
    <article class="ops-kpi"><div class="ops-kpi-label">{{ __('dashboard.active_sessions') }} <span class="ops-kpi-icon">◉</span></div><strong class="ops-kpi-value">{{ number_format($stats['active']) }}</strong><span class="ops-kpi-note">{{ __('dashboard.session_caption') }}</span></article>
    <article class="ops-kpi"><div class="ops-kpi-label">{{ __('dashboard.occupied_pcs') }} <span class="ops-kpi-icon">▦</span></div><strong class="ops-kpi-value">{{ number_format($stats['occupied']) }}</strong><span class="ops-kpi-note">{{ __('dashboard.pc_status') }}</span></article>
    <article class="ops-kpi"><div class="ops-kpi-label">{{ __('dashboard.available_pcs') }} <span class="ops-kpi-icon">✓</span></div><strong class="ops-kpi-value">{{ number_format($stats['available']) }}</strong><span class="ops-kpi-note">{{ __('dashboard.pc_status') }}</span></article>
    <article class="ops-kpi"><div class="ops-kpi-label">{{ __('dashboard.maintenance_alert') }} <span class="ops-kpi-icon">!</span></div><strong class="ops-kpi-value">{{ number_format($stats['attention']) }}</strong><span class="ops-kpi-note">{{ __('dashboard.maintenance') }}</span></article>
</section>

<div class="ops-dashboard-grid equal">
    <section class="ops-panel"><div class="ops-panel-head"><div><h2 class="ops-panel-title">{{ __('dashboard.next_reservations') }}</h2><p class="ops-panel-subtitle">{{ __('dashboard.arrivals_caption') }}</p></div><a class="ops-panel-link" href="{{ route('staff.bookings.index') }}">{{ __('dashboard.view_all') }} →</a></div>
        @forelse($nextBookings as $booking)<div class="ops-list-item"><div><strong>{{ $booking->user?->name }} · {{ $booking->zone?->localized('name') }}</strong><small>{{ $booking->pc?->name ?? __('ui.private_room') }} · {{ $booking->user?->email }}</small></div><div class="ops-list-meta">{{ $booking->starts_at->format('M j · H:i') }}<small><span class="ops-badge ops-badge-{{ $booking->status }}">{{ __('ui.status_'.$booking->status) }}</span></small></div></div>@empty<div class="ops-empty-block">{{ __('dashboard.no_reservations') }}</div>@endforelse
    </section>
    <section class="ops-panel"><div class="ops-panel-head"><div><h2 class="ops-panel-title">{{ __('dashboard.live_sessions') }}</h2><p class="ops-panel-subtitle">{{ __('dashboard.session_caption') }}</p></div><a class="ops-panel-link" href="{{ route('staff.pcs') }}">{{ __('dashboard.pc_status') }} →</a></div>
        @forelse($activeSessions as $booking)<div class="ops-list-item"><div><strong>{{ $booking->pc?->name ?? $booking->zone?->localized('name') }} · {{ $booking->user?->name }}</strong><small>{{ $booking->zone?->localized('name') }} · {{ __('dashboard.expected_end') }} {{ $booking->ends_at->format('H:i') }}</small></div><span class="ops-list-meta">{{ $booking->starts_at->format('H:i') }} — {{ $booking->ends_at->format('H:i') }}</span></div>@empty<div class="ops-empty-block">{{ __('dashboard.no_reservations') }}</div>@endforelse
    </section>
</div>
<div class="ops-dashboard-grid equal">
    <section class="ops-panel"><div class="ops-panel-head"><div><h2 class="ops-panel-title">{{ __('dashboard.maintenance') }}</h2><p class="ops-panel-subtitle">{{ __('dashboard.report_issue') }}</p></div><a class="ops-panel-link" href="{{ route('staff.pcs') }}">{{ __('dashboard.view_all') }} →</a></div>
        @forelse($maintenancePcs as $pc)<div class="ops-alert-row"><span class="ops-alert-marker"></span><div><strong>{{ $pc->name }}</strong><small class="ops-secondary-cell">{{ $pc->zone?->localized('name') }} · {{ $pc->maintenance_note ?: $pc->status }}</small></div><span class="ops-badge ops-badge-{{ $pc->status }}">{{ $pc->status }}</span></div>@empty<div class="ops-empty-block">{{ __('dashboard.all_clear') }}</div>@endforelse
    </section>
    <section class="ops-panel"><div class="ops-panel-head"><div><h2 class="ops-panel-title">{{ __('dashboard.quick_actions') }}</h2><p class="ops-panel-subtitle">{{ __('dashboard.shift_status') }}</p></div></div><div class="ops-action-row"><a class="ops-button" href="{{ route('staff.bookings.index') }}">◷ {{ __('dashboard.reservations') }}</a><a class="ops-button ops-button-secondary" href="{{ route('staff.food-orders.index') }}">▤ {{ __('dashboard.food_orders') }}</a><a class="ops-button ops-button-secondary" href="{{ route('staff.pcs') }}">▦ {{ __('dashboard.pc_status') }}</a></div><div class="ops-empty-block" style="margin-top:15px">{{ __('dashboard.read_only') }}</div></section>
</div>
@endsection
