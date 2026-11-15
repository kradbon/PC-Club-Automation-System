@extends('layouts.dashboard')
@section('title', __('dashboard.dashboard'))
@section('page-title', __('dashboard.dashboard'))
@section('content')
@php
    $maxRevenue = max(1, (float) $revenueChart->max('value'));
    $maxBookings = max(1, (int) $bookingChart->max('value'));
@endphp
<div class="ops-greeting">
    <div><p class="ops-kicker">{{ __('dashboard.administration') }} / {{ now()->format('F j, Y') }}</p><h1>{{ __('dashboard.greeting', ['name' => auth()->user()->name]) }}</h1><p>{{ __('dashboard.admin_summary') }}</p></div>
    <div class="ops-action-row"><a class="ops-button ops-button-secondary" href="{{ route('admin.bookings.index') }}">{{ __('dashboard.pending_bookings') }} <span>({{ $stats['booking_requests'] }})</span></a><a class="ops-button ops-button-secondary" href="{{ route('admin.food-menu') }}">{{ __('dashboard.manage_menu') }}</a><a class="ops-button" href="{{ route('admin.tournaments') }}">＋ {{ __('dashboard.new_tournament') }}</a></div>
</div>

<section class="ops-kpi-grid" id="statistics" aria-label="{{ __('dashboard.statistics') }}">
    <article class="ops-kpi"><div class="ops-kpi-label">{{ __('dashboard.today_revenue') }} <span class="ops-kpi-icon">↗</span></div><strong class="ops-kpi-value">{{ number_format($stats['revenue_today'], 2) }}<small style="font-size:12px"> TJS</small></strong><span class="ops-kpi-note">{{ __('dashboard.revenue_caption') }}</span></article>
    <article class="ops-kpi"><div class="ops-kpi-label">{{ __('dashboard.active_sessions') }} <span class="ops-kpi-icon">◷</span></div><strong class="ops-kpi-value">{{ number_format($stats['active_sessions']) }}</strong><span class="ops-kpi-note">{{ __('dashboard.session_caption') }}</span></article>
    <article class="ops-kpi"><div class="ops-kpi-label">{{ __('dashboard.pending_reservations') }} <span class="ops-kpi-icon">⌛</span></div><strong class="ops-kpi-value">{{ number_format($stats['booking_requests']) }}</strong><span class="ops-kpi-note">{{ __('dashboard.pending_bookings') }}</span></article>
    <article class="ops-kpi"><div class="ops-kpi-label">{{ __('dashboard.available_pcs') }} <span class="ops-kpi-icon">▦</span></div><strong class="ops-kpi-value">{{ number_format($stats['available_pcs']) }}</strong><span class="ops-kpi-note">{{ __('dashboard.pc_inventory') }}</span></article>
    <article class="ops-kpi"><div class="ops-kpi-label">{{ __('dashboard.occupied_pcs') }} <span class="ops-kpi-icon">◉</span></div><strong class="ops-kpi-value">{{ number_format($stats['occupied_pcs']) }}</strong><span class="ops-kpi-note">{{ __('dashboard.active_sessions') }}</span></article>
    <article class="ops-kpi"><div class="ops-kpi-label">{{ __('dashboard.customers') }} <span class="ops-kpi-icon">♙</span></div><strong class="ops-kpi-value">{{ number_format($stats['members']) }}</strong><span class="ops-kpi-note">{{ __('dashboard.customers_total') }}</span></article>
</section>

<div class="ops-dashboard-grid">
    <section class="ops-panel">
        <div class="ops-panel-head"><div><h2 class="ops-panel-title">{{ __('dashboard.revenue_trend') }}</h2><p class="ops-panel-subtitle">{{ __('dashboard.revenue_trend_copy') }}</p></div><div class="ops-panel-tools"><form class="ops-range-form" method="GET" action="{{ route('admin.dashboard') }}"><label>{{ __('dashboard.date_range') }}<select class="ops-input" name="period"><option value="7" @selected($period===7)>{{ __('dashboard.last_7_days') }}</option><option value="14" @selected($period===14)>{{ __('dashboard.last_14_days') }}</option><option value="30" @selected($period===30)>{{ __('dashboard.last_30_days') }}</option></select></label><button class="ops-button ops-button-secondary" type="submit">{{ __('dashboard.apply_filter') }}</button></form><a class="ops-panel-link" href="{{ route('admin.financials') }}">{{ __('dashboard.wallets_payments') }} →</a></div></div>
        <div class="ops-chart" role="img" aria-label="{{ __('dashboard.revenue_trend') }}">
            @foreach($revenueChart as $point)
                <div class="ops-chart-col"><span class="ops-chart-value">{{ number_format($point['value'], 0) }}</span><div class="ops-chart-track"><span class="ops-chart-bar" style="height:{{ max(4, ($point['value'] / $maxRevenue) * 100) }}%"></span></div><span class="ops-chart-label">{{ $point['label'] }}</span></div>
            @endforeach
        </div>
    </section>
    <section class="ops-panel">
        <div class="ops-panel-head"><div><h2 class="ops-panel-title">{{ __('dashboard.zone_occupancy') }}</h2><p class="ops-panel-subtitle">{{ __('dashboard.active_sessions') }}</p></div><a class="ops-panel-link" href="{{ route('admin.inventory') }}">{{ __('dashboard.view_all') }} →</a></div>
        <div class="ops-occupancy-list">
            @forelse($zoneOccupancy as $zone)
                @php($percentage = $zone->total_pcs_count ? min(100, round($zone->active_bookings_count / $zone->total_pcs_count * 100)) : 0)
                <div class="ops-occupancy-row"><span class="ops-occupancy-name">{{ $zone->localized('name') }}</span><span class="ops-occupancy-value">{{ __('dashboard.occupied_of', ['occupied' => $zone->active_bookings_count, 'total' => $zone->total_pcs_count]) }}</span><div class="ops-progress"><span style="width:{{ $percentage }}%"></span></div></div>
            @empty <div class="ops-empty-block">{{ __('dashboard.no_reservations') }}</div> @endforelse
        </div>
    </section>
</div>

<div class="ops-dashboard-grid equal">
    <section class="ops-panel">
        <div class="ops-panel-head"><div><h2 class="ops-panel-title">{{ __('dashboard.booking_activity') }}</h2><p class="ops-panel-subtitle">{{ __('dashboard.booking_activity_copy') }}</p></div><a class="ops-panel-link" href="{{ route('admin.bookings.index') }}">{{ __('dashboard.view_all') }} →</a></div>
        <div class="ops-chart" role="img" aria-label="{{ __('dashboard.booking_activity') }}">
            @foreach($bookingChart as $point)
                <div class="ops-chart-col"><span class="ops-chart-value">{{ $point['value'] }}</span><div class="ops-chart-track"><span class="ops-chart-bar" style="height:{{ max(4, ($point['value'] / $maxBookings) * 100) }}%"></span></div><span class="ops-chart-label">{{ $point['label'] }}</span></div>
            @endforeach
        </div>
    </section>
    <section class="ops-panel">
        <div class="ops-panel-head"><div><h2 class="ops-panel-title">{{ __('dashboard.operational_activity') }}</h2><p class="ops-panel-subtitle">{{ __('dashboard.maintenance_alert') }}</p></div><a class="ops-panel-link" href="{{ route('admin.inventory') }}">{{ __('dashboard.pc_inventory') }} →</a></div>
        <div class="ops-list-item"><div><strong>{{ __('dashboard.open_orders') }}</strong><small>{{ __('dashboard.food_orders_caption') }}</small></div><span class="ops-badge {{ $stats['food_orders'] ? 'ops-badge-pending' : 'ops-badge-available' }}">{{ $stats['food_orders'] }}</span></div>
        @forelse($maintenancePcs as $pc)<div class="ops-alert-row"><span class="ops-alert-marker"></span><div><strong>{{ $pc->name }}</strong><small class="ops-secondary-cell">{{ $pc->zone?->localized('name') }} · {{ $pc->maintenance_note ?: ucfirst($pc->status) }}</small></div><span class="ops-badge ops-badge-{{ $pc->status }}">{{ $pc->status }}</span></div>@empty<div class="ops-alert-row"><span class="ops-online-dot"></span><span>{{ __('dashboard.all_clear') }}</span></div>@endforelse
    </section>
</div>

<section class="ops-panel" style="margin-top:14px">
    <div class="ops-panel-head"><div><h2 class="ops-panel-title">{{ __('dashboard.recent_reservations') }}</h2><p class="ops-panel-subtitle">{{ __('dashboard.booking_activity_copy') }}</p></div><a class="ops-panel-link" href="{{ route('admin.bookings.index') }}">{{ __('dashboard.view_all') }} →</a></div>
    <div class="ops-table-wrap"><table class="ops-table"><thead><tr><th>{{ __('dashboard.customer') }}</th><th>{{ __('dashboard.zone_pc') }}</th><th>{{ __('dashboard.session') }}</th><th>{{ __('dashboard.amount') }}</th><th>{{ __('dashboard.status') }}</th></tr></thead><tbody>
        @forelse($recentBookings as $booking)<tr><td class="ops-primary-cell">{{ $booking->user?->name }}<span class="ops-secondary-cell">{{ $booking->user?->email }}</span></td><td>{{ $booking->zone?->localized('name') }}<span class="ops-secondary-cell">{{ $booking->pc?->name ?? __('ui.private_room') }}</span></td><td>{{ $booking->starts_at->format('M j, H:i') }} – {{ $booking->ends_at->format('H:i') }}</td><td>{{ number_format((float) ($booking->total_price_snapshot ?? 0), 2) }} {{ $booking->currency }}</td><td><span class="ops-badge ops-badge-{{ $booking->status }}">{{ __('ui.status_'.$booking->status) }}</span></td></tr>@empty<tr><td class="ops-empty" colspan="5">{{ __('dashboard.no_reservations') }}</td></tr>@endforelse
    </tbody></table></div>
</section>

<div class="ops-dashboard-grid equal">
    <section class="ops-panel"><div class="ops-panel-head"><div><h2 class="ops-panel-title">{{ __('dashboard.recent_transactions') }}</h2><p class="ops-panel-subtitle">{{ __('dashboard.revenue_caption') }}</p></div><a class="ops-panel-link" href="{{ route('admin.financials') }}">{{ __('dashboard.view_all') }} →</a></div>
        @forelse($recentTransactions as $transaction)<div class="ops-list-item"><div><strong>{{ $transaction->user?->name ?? __('dashboard.customer') }}</strong><small>{{ str_replace('_',' ',$transaction->transaction_type) }} · {{ $transaction->bucket }} · {{ $transaction->reason ?: __('dashboard.secure_operations') }}</small></div><div class="ops-list-meta">{{ number_format((float)$transaction->amount,2) }} {{ $transaction->currency }}<small>{{ $transaction->created_at?->format('M j H:i') }}</small></div></div>@empty<div class="ops-empty-block">{{ __('dashboard.no_transactions') }}</div>@endforelse
    </section>
    <section class="ops-panel"><div class="ops-panel-head"><div><h2 class="ops-panel-title">{{ __('dashboard.upcoming_tournaments') }}</h2><p class="ops-panel-subtitle">{{ __('dashboard.competitions') }}</p></div><a class="ops-panel-link" href="{{ route('admin.tournaments') }}">{{ __('dashboard.view_all') }} →</a></div>
        @forelse($upcomingTournaments as $tournament)<div class="ops-list-item"><div><strong>{{ $tournament->localized('name') }}</strong><small>{{ $tournament->game }} · {{ strtoupper($tournament->format) }}</small></div><span class="ops-list-meta">{{ $tournament->starts_at?->format('M j, H:i') ?? __('dashboard.no_date') }}</span></div>@empty<div class="ops-empty-block">{{ __('dashboard.no_tournaments') }}</div>@endforelse
    </section>
</div>

<div class="ops-dashboard-grid equal">
    <section class="ops-panel"><div class="ops-panel-head"><div><h2 class="ops-panel-title">{{ __('dashboard.latest_activity') }}</h2><p class="ops-panel-subtitle">{{ __('dashboard.pages_activity') }}</p></div><a class="ops-panel-link" href="{{ route('admin.content') }}#activity">{{ __('dashboard.view_all') }} →</a></div>
        @forelse($recentActivity as $log)<div class="ops-list-item"><div><strong>{{ str_replace(['.','_'], ' ', $log->action) }}</strong><small>{{ $log->actor?->name ?? __('dashboard.administrator') }} · {{ class_basename($log->subject_type ?? '') }}</small></div><span class="ops-list-meta">{{ $log->created_at?->format('M j H:i') }}</span></div>@empty<div class="ops-empty-block">{{ __('dashboard.no_activity') }}</div>@endforelse
    </section>
    <section class="ops-panel"><div class="ops-panel-head"><div><h2 class="ops-panel-title">{{ __('dashboard.quick_actions') }}</h2><p class="ops-panel-subtitle">{{ __('dashboard.administration') }}</p></div></div><div class="ops-action-row"><a class="ops-button ops-button-secondary" href="{{ route('admin.discounts') }}">％ {{ __('dashboard.add_promotion') }}</a><a class="ops-button ops-button-secondary" href="{{ route('admin.users') }}">♙ {{ __('dashboard.customers_roles') }}</a><a class="ops-button ops-button-secondary" href="{{ route('admin.tariffs') }}">◈ {{ __('dashboard.pricing') }}</a><a class="ops-button ops-button-secondary" href="{{ route('admin.events') }}">◷ {{ __('dashboard.events') }}</a></div>
        <div style="margin-top:20px"><div class="ops-list-item"><div><strong>{{ __('dashboard.customers_roles') }}</strong><small>{{ __('dashboard.customers_total') }}</small></div><span class="ops-list-meta">{{ number_format($stats['members']) }}</span></div><div class="ops-list-item"><div><strong>{{ __('dashboard.maintenance_alert') }}</strong><small>{{ __('dashboard.maintenance') }}</small></div><span class="ops-badge {{ $stats['maintenance_pcs'] ? 'ops-badge-maintenance' : 'ops-badge-available' }}">{{ $stats['maintenance_pcs'] }}</span></div></div>
    </section>
</div>
@endsection
