@extends('layouts.site')
@section('title', __('ui.zones').' | GB CLUB')
@section('content')
<header class="mx-auto max-w-[1440px] px-5 pb-10 pt-16 lg:px-10"><p class="font-mono text-[10px] uppercase tracking-[.3em] text-acid">GB CLUB / HARDWARE / RATES</p><h1 class="mt-4 font-display text-5xl font-bold sm:text-7xl">@if(app()->getLocale() === 'en')ROOMS FOR<br><em class="text-acid">EVERY SQUAD.</em>@else{{ __('ui.zones_title') }}@endif</h1><p class="mt-5 max-w-2xl text-lg leading-8 text-white/55">{{ __('ui.zones_copy') }}</p></header>
<section class="mx-auto max-w-[1440px] px-5 pb-20 lg:px-10"><div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
@forelse($rooms as $room)
    @php($pc = $room->pcs->first()) @php($spec = $pc?->specifications ?? [])
    <article class="border border-white/10 bg-white/[.025] p-6 transition hover:border-acid/40">
        <div class="flex items-center justify-between"><span class="font-mono text-[10px] uppercase tracking-[.2em] text-acid">{{ $room->category }}</span><span class="font-mono text-[10px] text-white/45">{{ $room->pcs->count() }} {{ __('ui.pcs_in_room') }}</span></div>
        @php($photo = match($room->slug) {
            'regular' => 'photo-1630201129622-a8e8ef3f7245',
            'vip' => 'photo-1636487658586-2e83d26f5e8c',
            'luxe' => 'photo-1636914086911-e2a87814479b',
            'streamer-room' => 'photo-1639342405971-a428b16b0f16',
            'bootcamp-dust2' => 'photo-1633545486613-feaf749f7805',
            'bootcamp-mirage' => 'photo-1629102981237-c44ffad32775',
            'wingman-01' => 'photo-1632603093711-0d93a0bcc6cc',
            'wingman-02' => 'photo-1723792306904-c417c0da40e3',
            'solo-luxe-01' => 'photo-1725629021424-ba7b8ce8848b',
            'solo-luxe-02' => 'photo-1786577341324-bb415d916948',
            default => 'photo-1630201129622-a8e8ef3f7245',
        })
        <div class="mt-5 h-44 border border-white/10 bg-cover bg-center" role="img" aria-label="{{ $room->localized('name') }}" style="background-image:linear-gradient(0deg,#080a0bcc,transparent 80%),url('https://images.unsplash.com/{{ $photo }}?auto=format&fit=crop&w=1000&q=80')"></div>
        <h2 class="mt-6 font-display text-3xl font-bold">{{ $room->localized('name') }}</h2><p class="mt-2 min-h-12 text-sm leading-6 text-white/55">{{ $room->localized('description') }}</p>
        <div class="mt-5 grid grid-cols-2 gap-3 border-y border-white/10 py-4 text-sm"><div><span class="block font-mono text-[9px] uppercase text-white/40">{{ __('ui.rate') }}</span><b class="mt-1 block text-acid">{{ number_format((float)$room->hourly_rate, 2) }} {{ $room->currency }} <small class="font-normal text-white/45">/ {{ __('ui.per_hour') }}</small></b></div><div><span class="block font-mono text-[9px] uppercase text-white/40">{{ __('ui.capacity') }}</span><b class="mt-1 block">{{ $room->pcs->count() }} {{ __('ui.pcs_in_room') }}</b></div></div>
        <div class="mt-4 space-y-2 text-sm">
            @foreach(['cpu' => 'CPU', 'gpu' => 'GPU', 'memory' => 'MEMORY', 'monitor' => __('ui.display'), 'monitor_hz' => __('ui.refresh_rate'), 'keyboard' => __('ui.keyboard'), 'mouse' => __('ui.mouse'), 'headset' => __('ui.headset'), 'microphone' => __('ui.microphone'), 'camera' => __('ui.camera'), 'streaming' => __('ui.streaming_gear'), 'peripherals' => __('ui.other_gear')] as $key => $label)
                @if(!empty($spec[$key]))<div class="flex justify-between gap-5"><span class="shrink-0 font-mono text-[9px] uppercase text-white/40">{{ $label }}</span><b class="text-right text-xs font-medium text-white/80">{{ $key === 'monitor_hz' ? $spec[$key].' Hz' : $spec[$key] }}</b></div>@endif
            @endforeach
        </div>
        <a class="arena-button mt-6 w-full justify-center" href="{{ auth()->check() ? route('account.bookings.create', ['zone' => $room->id]) : route('register') }}">{{ __('ui.book_this_zone') }} →</a>
    </article>
@empty
    <p class="text-white/50">{{ __('ui.rooms_empty') }}</p>
@endforelse
</div><p class="mt-6 text-xs text-white/40">{{ __('ui.configuration_sample') }}</p></section>
@endsection
