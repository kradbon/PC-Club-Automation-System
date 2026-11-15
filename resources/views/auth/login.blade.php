@extends('layouts.site')
@section('title', __('ui.login').' | GB CLUB')
@section('content')
<div class="auth-wrap"><p class="font-mono text-[10px] uppercase tracking-[.3em] text-acid">GB CLUB / {{ __('ui.login') }}</p><h1 class="mt-4 font-display text-5xl font-bold">{{ __('ui.login_title') }}</h1><form class="mt-7 grid gap-5 border border-white/10 bg-white/[.025] p-7" action="{{ route('login.store') }}" method="POST">@csrf
<label class="text-xs text-white/65" for="email">{{ __('ui.email') }}<input class="arena-input mt-2" id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email">@error('email')<span class="mt-1 block text-red-300">{{ $message }}</span>@enderror</label>
<label class="text-xs text-white/65" for="password">{{ __('ui.password') }}<input class="arena-input mt-2" id="password" type="password" name="password" required autocomplete="current-password">@error('password')<span class="mt-1 block text-red-300">{{ $message }}</span>@enderror</label>
<button class="arena-button">{{ __('ui.login') }} →</button><a class="text-xs text-acid" href="{{ route('password.request') }}">{{ __('ui.forgot_password') }}</a>
</form><p class="mt-5 text-sm text-white/55">{{ __('ui.no_account') }} <a class="text-acid" href="{{ route('register') }}">{{ __('ui.join') }} →</a></p></div>
@endsection
