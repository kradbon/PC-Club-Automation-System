@extends('layouts.dashboard')
@section('title', 'Manage PC zones | GB CLUB')
@section('page-title', __('dashboard.zones'))
@section('content')
<header class="ops-page-head"><div><p class="ops-kicker">{{ __('dashboard.administration') }} / {{ __('dashboard.club_operations') }}</p><h1>Rooms and PC builds</h1><p>Manage room inventory, PC specifications, capacity, and the hourly rates customers see.</p></div></header>
<section class="ops-panel" style="margin-bottom:14px"><div class="ops-panel-head"><div><h2 class="ops-panel-title">Add a room type</h2><p class="ops-panel-subtitle">Prices are in TJS per hour.</p></div></div><form class="ops-form-grid" method="POST" action="{{ route('admin.rooms.store') }}">@csrf @include('admin.partials.room-fields', ['room' => null])<div class="ops-form-full"><button class="ops-button" type="submit">Add room</button></div></form></section>
@foreach($rooms as $room)
<details class="ops-panel" style="margin-bottom:12px"><summary class="ops-panel-head" style="cursor:pointer;align-items:center"><div><h2 class="ops-panel-title">{{ $room->name }}</h2><p class="ops-panel-subtitle">{{ $room->quantity }} rooms ? {{ $room->player_capacity }} players ? TJS {{ number_format($room->hourly_rate_cents / 100,2) }}/hour ? {{ $room->is_active ? 'Active' : 'Archived' }}</p></div><span class="ops-badge ops-badge-{{ $room->is_active ? 'available' : 'unavailable' }}">{{ $room->is_active ? 'Active' : 'Archived' }}</span></summary>
<form class="ops-form-grid" method="POST" action="{{ route('admin.rooms.update',$room) }}">@csrf @method('PUT') @include('admin.partials.room-fields',['room'=>$room])<div class="ops-form-full ops-action-row"><button class="ops-button" type="submit">Save room</button>@if($room->is_active)<button class="ops-button ops-button-danger" type="submit" form="archive-room-{{ $room->id }}">Hide room</button>@endif</div></form>@if($room->is_active)<form id="archive-room-{{ $room->id }}" method="POST" action="{{ route('admin.rooms.archive',$room) }}">@csrf @method('DELETE')</form>@endif
</details>
@endforeach
@endsection
