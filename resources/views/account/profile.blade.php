@extends('layouts.dashboard')
@section('title', __('ui.profile_settings').' | GB CLUB')
@section('page-title', __('ui.profile_settings'))
@section('content')
<header class="ops-page-head"><div><p class="ops-kicker">{{ __('ui.my_account') }} / GB CLUB</p><h1>{{ __('ui.profile_settings') }}</h1><p>{{ __('ui.profile_copy') }}</p></div></header>
<div class="ops-dashboard-grid" style="grid-template-columns:minmax(0,680px) minmax(240px,1fr)">
<section class="ops-panel"><div class="ops-panel-head"><div><h2 class="ops-panel-title">{{ __('ui.profile_settings') }}</h2><p class="ops-panel-subtitle">{{ __('ui.email_read_only') }}</p></div></div>
<form method="POST" action="{{ route('account.profile.update') }}" class="ops-form-grid">@csrf @method('PUT')
<label class="ops-field">{{ __('dashboard.account_name') }}<input class="ops-input" name="name" value="{{ old('name', $user->name) }}" maxlength="120" required></label>
<label class="ops-field">{{ __('dashboard.account_email') }}<input class="ops-input" value="{{ $user->email }}" readonly disabled></label>
<label class="ops-field ops-form-full">{{ __('ui.preferred_language') }}<select class="ops-input" name="preferred_locale" required>@foreach(['en'=>'English','ru'=>'???????','tj'=>'??????'] as $locale=>$label)<option value="{{ $locale }}" @selected(old('preferred_locale', $user->preferred_locale ?? app()->getLocale())===$locale)>{{ $label }}</option>@endforeach</select></label>
<div class="ops-form-full"><button class="ops-button" type="submit">{{ __('ui.save_profile') }}</button></div>
</form></section>
<section class="ops-panel"><div class="ops-panel-head"><div><h2 class="ops-panel-title">{{ __('ui.membership') }}</h2><p class="ops-panel-subtitle">{{ __('ui.current_tier') }}</p></div></div><span class="ops-badge ops-badge-confirmed">{{ ucfirst($user->membership_tier ?? 'standard') }}</span><p class="ops-panel-subtitle" style="margin-top:14px">{{ __('ui.membership') }} ? {{ __('ui.bonus_balance') }}</p></section>
</div>
@endsection
