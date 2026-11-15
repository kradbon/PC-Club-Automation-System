@extends('layouts.dashboard')
@section('title', 'Manage Events')
@section('content')
    <header class="page-hero"><div class="eyebrow">ADMIN / CLUB CALENDAR</div><h1>EVENTS<br><em>AND MEETUPS.</em></h1><p class="lede">Publish upcoming events, edit details, or hide finished plans from the public calendar.</p></header>
    <nav class="account-nav"><a href="{{ route('admin.dashboard') }}">STATISTICS & ORDERS</a><a href="{{ route('admin.events') }}">EVENTS</a><a href="{{ route('admin.tariffs') }}">HOUR PACKS</a><a href="{{ route('admin.rooms') }}">ROOMS & PC SPECS</a></nav>
    <section class="section"><div class="panel"><div class="eyebrow">ADD EVENT</div><form class="form admin-form-grid" method="POST" action="{{ route('admin.events.store') }}">@csrf @include('admin.partials.event-fields', ['event' => null])<div><button class="button" type="submit">PUBLISH EVENT &gt;</button></div></form></div>
        @foreach ($events as $event)<details class="panel"><summary style="cursor:pointer;display:flex;justify-content:space-between;gap:20px"><strong>{{ $event->title }}</strong><span class="muted">{{ $event->starts_at->format('M j, Y g:i A') }} / {{ $event->is_published ? 'PUBLISHED' : 'HIDDEN' }}</span></summary>
            <form class="form admin-form-grid" method="POST" action="{{ route('admin.events.update', $event) }}" style="margin-top:20px">@csrf @method('PUT') @include('admin.partials.event-fields', ['event' => $event])<div><button class="button" type="submit">SAVE EVENT &gt;</button></div></form>
            @if ($event->is_published)<form method="POST" action="{{ route('admin.events.archive', $event) }}" style="margin-top:12px">@csrf @method('DELETE')<button class="button button-outline" type="submit">HIDE EVENT</button></form>@endif
        </details>@endforeach
    </section>
@endsection
