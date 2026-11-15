@extends('layouts.dashboard')
@section('title', __('ui.orders').' | GB CLUB')
@section('page-title', __('ui.orders'))
@section('content')
<header class="ops-page-head"><div><p class="ops-kicker">{{ __('ui.my_account') }} / GB CLUB</p><h1>{{ __('ui.orders') }}</h1><p>{{ __('ui.order_history_copy') }}</p></div><a class="ops-button" href="{{ route('account.tariffs') }}">{{ __('ui.browse_packages') }} ?</a></header>
<div class="ops-table-wrap"><table class="ops-table"><thead><tr><th>{{ __('ui.order_number') }}</th><th>{{ __('ui.date_time') }}</th><th>{{ __('ui.package') }}</th><th>{{ __('ui.hours') }}</th><th>{{ __('ui.total') }}</th><th>{{ __('dashboard.status') }}</th></tr></thead><tbody>@forelse($orders as $order)<tr><td class="ops-primary-cell">{{ $order->order_number }}</td><td>{{ $order->created_at->format('M j, Y') }}</td><td>{{ $order->tariff_name }}</td><td>{{ number_format($order->minutes / 60,1) }}</td><td>TJS {{ number_format($order->total_cents / 100,2) }}</td><td><span class="ops-badge ops-badge-{{ $order->status }}">{{ ucfirst($order->status) }}</span></td></tr>@empty<tr><td class="ops-empty" colspan="6">{{ __('ui.no_hour_orders') }} <a class="ops-panel-link" href="{{ route('account.tariffs') }}">{{ __('ui.browse_packages') }} ?</a></td></tr>@endforelse</tbody></table></div>
{{ $orders->links() }}
@endsection
