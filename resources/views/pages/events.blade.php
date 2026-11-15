@extends('layouts.site')
@section('title', 'Events')
@section('content')
    <header class="relative flex min-h-[480px] items-end bg-cover bg-center px-5 py-16 lg:min-h-[560px] lg:px-12" style="background-image:linear-gradient(0deg,#080a0bf0,#080a0b30),url('https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=1800&q=85')"><div class="mx-auto w-full max-w-[1440px]"><p class="font-mono text-[10px] uppercase tracking-[.3em] text-acid">WHAT'S ON / THE CLUB CALENDAR</p><h1 class="mt-4 font-display text-5xl font-bold sm:text-7xl">QUEUE UP.<br><span class="text-acid">SHOW UP.</span></h1><p class="mt-5 max-w-2xl text-lg leading-8 text-white/65">Tournaments, community nights, bootcamps, and open play. Find your next reason to pull up.</p></div></header>
    <section class="mx-auto max-w-[1440px] px-5 py-16 lg:px-10"><div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($events as $event)
            <article class="overflow-hidden border border-white/10 bg-white/[.025]"><div class="h-56 bg-cover bg-center" style="background-image:linear-gradient(0deg,#080a0b55,transparent),url('{{ $event->image_url }}')"></div><div class="p-6"><span class="font-mono text-[9px] uppercase tracking-widest text-acid">{{ $event->type }}</span><h3 class="mt-3 font-display text-2xl font-bold">{{ $event->title }}</h3><p class="mt-3 font-mono text-xs text-white/50">{{ $event->starts_at->format('l, F j') }} / {{ $event->starts_at->format('g:i A') }}@if ($event->ends_at) - {{ $event->ends_at->format('g:i A') }}@endif</p><p class="mt-3 text-sm leading-7 text-white/55">{{ $event->description }}</p><a class="mt-4 inline-flex font-mono text-[10px] uppercase text-acid" href="{{ auth()->check() ? route('account.dashboard') : route('register') }}">{{ auth()->check() ? __('ui.my_account') : __('ui.join') }} &rarr;</a></div></article>
        @empty
            <p class="muted">No upcoming events have been posted. Check back soon.</p>
        @endforelse
    </div></section>
@endsection
