@extends('layouts.dashboard')
@section('title', __('dashboard.pc_status'))
@section('page-title', __('dashboard.pc_status'))
@section('content')
<header class="ops-page-head"><div><p class="ops-kicker">{{ __('dashboard.my_work') }} / GB CLUB</p><h1>{{ __('dashboard.pc_status') }}</h1><p>{{ __('dashboard.read_only') }}</p></div><a class="ops-button ops-button-secondary" href="{{ route('staff.bookings.index') }}">{{ __('dashboard.reservations') }} →</a></header>
@foreach($zones as $zone)
<section class="ops-panel" style="margin-bottom:14px"><div class="ops-panel-head"><div><h2 class="ops-panel-title">{{ $zone->localized('name') }}</h2><p class="ops-panel-subtitle">{{ $zone->category }} · {{ $zone->pcs->count() }} {{ __('dashboard.pc_count') }}</p></div><span class="ops-badge ops-badge-available">{{ $zone->is_active ? __('dashboard.available') : __('dashboard.unavailable') }}</span></div>
<div class="ops-table-wrap"><table class="ops-table"><thead><tr><th>{{ __('dashboard.pc_inventory') }}</th><th>CPU / GPU</th><th>RAM / Monitor</th><th>{{ __('dashboard.status') }}</th><th>{{ __('dashboard.expected_end') }}</th></tr></thead><tbody>
@foreach($zone->pcs as $pc)@php($spec=$pc->specifications ?? []) @php($isOccupied=in_array($pc->id,$activePcIds,true)) @php($status=$isOccupied?'occupied':$pc->status)
<tr><td class="ops-primary-cell">{{ $pc->name }}<span class="ops-secondary-cell">{{ $pc->asset_tag }}</span></td><td>{{ $spec['cpu'] ?? '—' }}<span class="ops-secondary-cell">{{ $spec['gpu'] ?? '—' }}</span></td><td>{{ $spec['ram'] ?? $spec['memory'] ?? '—' }}<span class="ops-secondary-cell">{{ $spec['monitor'] ?? '—' }} · {{ $spec['monitor_hz'] ?? '—' }} Hz</span></td><td><span class="ops-badge ops-badge-{{ $status==='occupied'?'pending':$status }}">{{ $status }}</span></td><td>{{ $activeSessions->get($pc->id)?->booking?->ends_at?->format('H:i') ?? '—' }}</td></tr>
@endforeach
</tbody></table></div></section>
@endforeach
@endsection
