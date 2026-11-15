@extends('layouts.dashboard')
@section('title', __('dashboard.manage_account').' | GB CLUB')
@section('content')
@php($editing = $managedUser->exists)
<div class="mx-auto max-w-3xl px-5 py-12 lg:px-10"><p class="ops-kicker">GB CLUB / {{ __('dashboard.customers_roles') }}</p><h1 class="mt-3 font-display text-4xl font-bold">{{ $editing ? __('dashboard.edit_account') : __('dashboard.create_account') }}</h1>
<form class="ops-panel mt-8 grid gap-5" method="POST" action="{{ $editing ? route('admin.users.update', $managedUser) : route('admin.users.store') }}">@csrf @if($editing) @method('PUT') @endif
<label>{{ __('dashboard.account_name') }}<input class="ops-input mt-2" name="name" value="{{ old('name', $managedUser->name) }}" maxlength="120" required></label>
<label>{{ __('dashboard.account_email') }}<input class="ops-input mt-2" type="email" name="email" value="{{ old('email', $managedUser->email) }}" required></label>
<label>{{ __('dashboard.role') }}<select class="ops-input mt-2" name="role">@foreach(['customer','staff','admin'] as $role)<option value="{{ $role }}" @selected(old('role', $managedUser->role)===$role)>{{ ucfirst($role) }}</option>@endforeach</select></label>
<label>{{ __('dashboard.membership') }}<select class="ops-input mt-2" name="membership_tier"><option value="standard" @selected(old('membership_tier',$managedUser->membership_tier)==='standard')>{{ __('dashboard.standard') }}</option><option value="vip" @selected(old('membership_tier',$managedUser->membership_tier)==='vip')>VIP</option></select></label>
<label>{{ __('dashboard.account_status') }}<select class="ops-input mt-2" name="account_status"><option value="active" @selected(old('account_status',$managedUser->account_status)==='active')>{{ __('dashboard.active') }}</option><option value="suspended" @selected(old('account_status',$managedUser->account_status)==='suspended')>{{ __('dashboard.suspended') }}</option></select></label>
<label>{{ __('dashboard.password') }}{{ $editing ? ' ('.__('dashboard.optional').')' : '' }}<input class="ops-input mt-2" type="password" name="password" autocomplete="new-password" {{ $editing ? '' : 'required' }}></label>
<label>{{ __('dashboard.confirm_password') }}<input class="ops-input mt-2" type="password" name="password_confirmation" autocomplete="new-password" {{ $editing ? '' : 'required' }}></label>
<div class="flex gap-3"><button class="ops-button" type="submit">{{ __('dashboard.save_account') }}</button><a class="ops-button ops-button-secondary" href="{{ route('admin.users') }}">{{ __('dashboard.cancel') }}</a></div>
</form></div>
@endsection
