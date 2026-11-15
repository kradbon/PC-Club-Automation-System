@extends('layouts.dashboard')
@section('title', __('dashboard.my_profile').' | GB CLUB')
@section('page-title', __('dashboard.my_profile'))
@section('content')
<header class="ops-page-head"><div><p class="ops-kicker">GB CLUB / {{ __('dashboard.account_group') }}</p><h1>{{ __('dashboard.my_profile') }}</h1><p>{{ __('ui.profile_copy') }}</p></div></header>
<div class="ops-dashboard-grid" style="grid-template-columns:minmax(0,680px) minmax(240px,1fr)">
<section class="ops-panel"><div class="ops-panel-head"><div><h2 class="ops-panel-title">{{ __('dashboard.my_profile') }}</h2><p class="ops-panel-subtitle">{{ __('ui.email_read_only') }}</p></div></div>
<form method="POST" action="{{ route(auth()->user()->isAdmin() ? 'admin.profile.update' : (auth()->user()->isStaff() ? 'staff.profile.update' : 'account.profile.update')) }}" enctype="multipart/form-data" class="ops-form-grid">@csrf @method('PUT')
<div class="ops-form-full profile-avatar-row"><span class="profile-avatar-preview">@if($user->avatar_path)<img src="{{ asset('storage/'.$user->avatar_path) }}" alt="{{ $user->name }}">@else{{ strtoupper(mb_substr($user->name,0,1)) }}@endif</span><label class="ops-field">{{ __('ui.profile_avatar') }}<input class="ops-input" type="file" name="avatar" accept="image/jpeg,image/png,image/webp"><small>{{ __('ui.profile_avatar_hint') }}</small></label>@error('avatar')<small class="profile-error">{{ $message }}</small>@enderror</div>
<label class="ops-field">{{ __('dashboard.account_name') }}<input class="ops-input" name="name" value="{{ old('name', $user->name) }}" maxlength="120" required>@error('name')<small class="profile-error">{{ $message }}</small>@enderror</label>
<label class="ops-field">{{ __('dashboard.account_email') }}<input class="ops-input" value="{{ $user->email }}" readonly disabled></label>
<label class="ops-field ops-form-full">{{ __('ui.preferred_language') }}<select class="ops-input" name="preferred_locale" required>@foreach(['en'=>'English','ru'=>'Russian','tj'=>'Tajik'] as $locale=>$label)<option value="{{ $locale }}" @selected(old('preferred_locale', $user->preferred_locale ?? app()->getLocale())===$locale)>{{ $label }}</option>@endforeach</select></label>
<div class="ops-form-full"><button class="ops-button" type="submit">{{ __('ui.save_profile') }}</button></div>
</form></section>
<section class="ops-panel"><div class="ops-panel-head"><div><h2 class="ops-panel-title">{{ __('dashboard.role') }}</h2><p class="ops-panel-subtitle">{{ __('dashboard.account_status') }}</p></div></div><p class="ops-primary-cell">{{ ucfirst($user->role) }}</p><span class="ops-badge ops-badge-{{ $user->account_status }}">{{ __('dashboard.'.$user->account_status) }}</span>@if(in_array($user->role,['customer','member'],true))<p class="ops-panel-subtitle" style="margin-top:14px">{{ __('ui.membership') }}: {{ ucfirst($user->membership_tier ?? 'standard') }}</p>@endif</section>
</div>
@endsection
