@extends('layouts.site')
@section('title', __('ui.join').' | GB CLUB')
@section('content')
<div class="auth-wrap"><p class="font-mono text-[10px] uppercase tracking-[.3em] text-acid">GB CLUB / {{ __('ui.join') }}</p><h1 class="mt-4 font-display text-5xl font-bold">{{ __('ui.register_title') }}</h1><form class="mt-7 grid gap-5 border border-white/10 bg-white/[.025] p-7" action="{{ route('register.store') }}" method="POST">@csrf
<label class="text-xs text-white/65" for="name">{{ __('ui.name') }}<input class="arena-input mt-2" id="name" name="name" value="{{ old('name') }}" required maxlength="120" autocomplete="name">@error('name')<span class="mt-1 block text-red-300">{{ $message }}</span>@enderror</label>
<label class="text-xs text-white/65" for="email">{{ __('ui.email') }}<input class="arena-input mt-2" id="email" type="email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email">@error('email')<span class="mt-1 block text-red-300">{{ $message }}</span>@enderror</label>
<label class="text-xs text-white/65" for="password">{{ __('ui.password') }}<input class="arena-input mt-2" id="password" type="password" name="password" required minlength="10" autocomplete="new-password"><small class="mt-1 block text-white/40">Use 10+ characters with uppercase, lowercase, and numbers.</small>@error('password')<span class="mt-1 block text-red-300">{{ $message }}</span>@enderror</label>
<label class="text-xs text-white/65" for="password_confirmation">{{ __('ui.password_confirmation') }}<input class="arena-input mt-2" id="password_confirmation" type="password" name="password_confirmation" required minlength="10" autocomplete="new-password"></label>
<button class="arena-button">{{ __('ui.create_account') }} →</button></form><p class="mt-5 text-sm text-white/55">{{ __('ui.has_account') }} <a class="text-acid" href="{{ route('login') }}">{{ __('ui.login') }}</a></p></div>
@endsection
