@extends('layouts.site')
@section('title', __('ui.forgot_password').' | GB CLUB')
@section('content')
<div class="auth-wrap"><p class="font-mono text-[10px] uppercase tracking-[.3em] text-acid">GB CLUB / ACCOUNT RECOVERY</p><h1 class="mt-4 font-display text-4xl font-bold">{{ __('ui.forgot_password') }}</h1><form class="mt-7 grid gap-5 border border-white/10 p-7" method="POST" action="{{ route('password.email') }}">@csrf<label class="text-xs" for="email">{{ __('ui.email') }}<input class="arena-input mt-2" id="email" name="email" type="email" value="{{ old('email') }}" required>@error('email')<small class="text-red-300">{{ $message }}</small>@enderror</label><button class="arena-button">{{ __('ui.send_reset') }}</button></form></div>
@endsection
