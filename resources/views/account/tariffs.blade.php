@extends('layouts.dashboard')
@section('title', 'Buy Play Hours')
@section('content')
    <header class="page-hero"><div class="eyebrow">MY ACCOUNT / HOUR PACKS</div><h1>LOAD UP<br><em>YOUR HOURS.</em></h1><p class="lede">Choose a pack. Your order will appear in your cabinet while staff confirms payment.</p></header>
    <nav class="account-nav"><a href="{{ route('account.dashboard') }}">OVERVIEW</a><a href="{{ route('account.tariffs') }}">BUY HOURS</a><a href="{{ route('account.orders') }}">ORDER HISTORY</a></nav>
    <section class="section"><div class="card-grid">@forelse ($tariffs as $tariff)<article class="card price-card"><span class="eyebrow">{{ number_format($tariff->minutes / 60, 1) }} HOURS</span><h3>{{ $tariff->name }}</h3><p>{{ $tariff->description }}</p><div class="price">TJS {{ number_format($tariff->price_cents / 100, 2) }}</div><form method="POST" action="{{ route('account.order', $tariff) }}">@csrf<button class="button" type="submit">PLACE ORDER &gt;</button></form></article>@empty<p class="muted">No hour packs are active right now.</p>@endforelse</div><p class="muted" style="margin-top:20px">An admin confirms payment before hours are added to your balance.</p></section>
@endsection
