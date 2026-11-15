@extends('layouts.dashboard')
@section('title', __('ui.my_sessions').' | GB CLUB')
@section('page-title', __('ui.my_sessions'))
@section('content')
<header class="ops-page-head"><div><p class="ops-kicker">{{ __('ui.my_account') }} / GB CLUB</p><h1>{{ __('ui.my_sessions') }}</h1><p>{{ __('ui.session_history_copy') }}</p></div></header>
<div class="ops-table-wrap"><table class="ops-table"><thead><tr><th>{{ __('ui.session_date') }}</th><th>{{ __('dashboard.zones') }}</th><th>{{ __('dashboard.pc_inventory') }}</th><th>{{ __('dashboard.starts') }}</th><th>{{ __('dashboard.expected_end') }}</th><th>{{ __('dashboard.status') }}</th></tr></thead><tbody>@forelse($sessions as $session)<tr><td>{{ $session->started_at?->format('M j, Y') ?? $session->booking?->starts_at?->format('M j, Y') ?? '?' }}</td><td>{{ $session->booking?->zone?->localized('name') ?? '?' }}</td><td>{{ $session->pc?->name ?? '?' }}</td><td>{{ $session->started_at?->format('H:i') ?? '?' }}</td><td>{{ $session->ended_at?->format('H:i') ?? '?' }}</td><td><span class="ops-badge ops-badge-{{ $session->status }}">{{ ucfirst(str_replace('_', ' ', $session->status)) }}</span></td></tr>@empty<tr><td class="ops-empty" colspan="6">{{ __('ui.no_sessions_yet') }}</td></tr>@endforelse</tbody></table></div>
{{ $sessions->links() }}
@endsection
