@extends('layouts.site')
@section('title', $tournament->localized('name').' | GB CLUB')
@section('content')
<div class="mx-auto max-w-[1100px] px-5 py-14 lg:px-10"><a class="font-mono text-xs uppercase text-acid" href="{{ route('tournaments.index') }}">← {{ __('ui.tournaments') }}</a><p class="mt-10 font-mono text-xs uppercase tracking-[.3em] text-acid">{{ $tournament->game }} / {{ __('ui.status_'.$tournament->status) }}</p><h1 class="mt-4 font-display text-5xl font-bold sm:text-7xl">{{ $tournament->localized('name') }}</h1><p class="mt-6 max-w-3xl text-lg leading-8 text-white/60">{{ $tournament->localized('description') }}</p>
<div class="mt-9 grid gap-4 sm:grid-cols-3"><div class="border border-white/10 p-5"><small class="font-mono text-[10px] uppercase text-white/45">{{ __('ui.participants') }}</small><p class="mt-2 text-xl">{{ $tournament->registrations->whereIn('status',['approved','registered'])->count() }} / {{ $tournament->participant_limit ?? '∞' }}</p></div><div class="border border-white/10 p-5"><small class="font-mono text-[10px] uppercase text-white/45">{{ __('ui.registration_deadline') }}</small><p class="mt-2 text-xl">{{ $tournament->registration_closes_at?->format('M j, H:i') ?? '—' }}</p></div><div class="border border-white/10 p-5"><small class="font-mono text-[10px] uppercase text-white/45">{{ __('ui.prize') }}</small><p class="mt-2 text-sm">{{ $tournament->localized('prize') }}</p></div></div>
@auth
    @php($myRegistration=$tournament->registrations->firstWhere('user_id',auth()->id()))
    @if($myRegistration && $myRegistration->status==='approved')<div class="mt-8 border border-acid/30 bg-acid/[.07] p-5 text-acid">{{ __('ui.registered') }} / {{ $myRegistration->registration_code }}</div>
    @elseif($tournament->status==='registration')
        <form class="mt-8 grid max-w-xl gap-3" method="POST" action="{{ route('account.tournaments.register',$tournament) }}">@csrf
            @if($tournament->team_size>1)<label class="text-xs text-white/60">{{ __('ui.choose_team') }}<select class="arena-input mt-2" name="team_id" required><option value="">—</option>@foreach($teams as $team)<option value="{{ $team->id }}">{{ $team->name }} ({{ $team->members_count }}/{{ $tournament->team_size }})</option>@endforeach</select></label><p class="text-xs text-white/40">{{ __('ui.team_tournament_help', ['count'=>$tournament->team_size]) }} <a class="text-acid" href="{{ route('account.teams.index') }}">{{ __('ui.teams') }}</a></p>@endif
            <button class="arena-button w-full sm:w-auto">{{ __('ui.tournament_join') }} →</button>@error('tournament')<p class="text-sm text-red-300">{{ $message }}</p>@enderror @error('team_id')<p class="text-sm text-red-300">{{ $message }}</p>@enderror
        </form>
    @endif
@else<a class="arena-button mt-8 inline-flex" href="{{ route('login') }}">{{ __('ui.login') }} →</a>@endauth
<h2 class="mt-12 font-display text-2xl font-bold">{{ __('ui.participants') }}</h2><div class="mt-4 divide-y divide-white/10 border-y border-white/10">@forelse($tournament->registrations->whereIn('status',['approved','registered']) as $registration)<p class="py-3 text-sm">{{ $registration->team?->name ?? $registration->user?->name ?? __('ui.registered') }}</p>@empty<p class="py-4 text-sm text-white/45">—</p>@endforelse</div></div>
@endsection
