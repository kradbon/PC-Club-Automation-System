@extends('layouts.dashboard')
@section('title', __('dashboard.my_profile').' | GB CLUB')
@section('page-title', __('dashboard.my_profile'))
@section('content')
<div class="mx-auto max-w-3xl px-5 py-12 lg:px-10">
    <header class="ops-page-head"><div><p class="ops-kicker">GB CLUB / {{ __('dashboard.staff_workspace') }}</p><h1>{{ __('dashboard.my_profile') }}</h1><p>{{ __('dashboard.staff_profile_copy') }}</p></div></header>
    <section class="ops-panel mt-6 grid gap-5 sm:grid-cols-2">
        <div><p class="ops-kpi-label">{{ __('dashboard.account_name') }}</p><p class="mt-2 text-lg font-semibold">{{ $user->name }}</p></div>
        <div><p class="ops-kpi-label">{{ __('dashboard.account_email') }}</p><p class="mt-2 text-lg font-semibold">{{ $user->email }}</p></div>
        <div><p class="ops-kpi-label">{{ __('dashboard.role') }}</p><p class="mt-2 text-lg font-semibold">{{ ucfirst($user->role) }}</p></div>
        <div><p class="ops-kpi-label">{{ __('dashboard.account_status') }}</p><p class="mt-2 text-lg font-semibold">{{ __('dashboard.'.$user->account_status) }}</p></div>
    </section>
</div>
@endsection
