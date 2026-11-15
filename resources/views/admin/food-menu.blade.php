@extends('layouts.dashboard')
@section('title', __('dashboard.food_catalogue'))
@section('page-title', __('dashboard.food_menu'))
@section('content')
<header class="ops-page-head"><div><p class="ops-kicker">{{ __('dashboard.club_operations') }} / GB CLUB CAFE</p><h1>{{ __('dashboard.food_catalogue') }}</h1><p>{{ __('dashboard.food_catalogue_copy') }}</p></div><a class="ops-button ops-button-secondary" href="{{ route('staff.food-orders.index') }}">{{ __('dashboard.food_orders') }} →</a></header>

<section class="ops-panel" style="margin-bottom:15px">
    <div class="ops-panel-head"><div><h2 class="ops-panel-title">{{ __('dashboard.add_food_item') }}</h2><p class="ops-panel-subtitle">{{ __('dashboard.food_orders_caption') }}</p></div></div>
    <form class="ops-form-grid" method="POST" action="{{ route('admin.food-menu.store') }}">@csrf
        <label class="ops-field">{{ __('dashboard.item_name') }}<input class="ops-input" name="name" value="{{ old('name') }}" required maxlength="120"></label>
        <label class="ops-field">{{ __('dashboard.slug') }}<input class="ops-input" name="slug" value="{{ old('slug') }}" maxlength="140" placeholder="burger-special"></label>
        <label class="ops-field">{{ __('dashboard.category') }}<input class="ops-input" name="category" value="{{ old('category') }}" required maxlength="60"></label>
        <label class="ops-field">{{ __('dashboard.price') }} ({{ config('club.currency','TJS') }})<input class="ops-input" type="number" name="price" value="{{ old('price') }}" required min="0.01" max="10000" step="0.01"></label>
        <label class="ops-field ops-form-full">{{ __('dashboard.description') }}<textarea class="ops-input" name="description" rows="2" maxlength="1000">{{ old('description') }}</textarea></label>
        <label class="ops-field ops-form-full">{{ __('dashboard.image_url') }}<input class="ops-input" type="url" name="image_url" value="{{ old('image_url') }}" maxlength="255"></label>
        <label class="ops-field">{{ __('dashboard.sort_order') }}<input class="ops-input" type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" max="65535"></label>
        <div class="ops-form-full" style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap"><input type="hidden" name="is_available" value="0"><label class="ops-checkbox"><input type="checkbox" name="is_available" value="1" checked> {{ __('dashboard.available_for_order') }}</label><button class="ops-button" type="submit">＋ {{ __('dashboard.add_food_item') }}</button></div>
    </form>
</section>

<div class="ops-panel-head"><div><h2 class="ops-panel-title">{{ __('dashboard.menu_items') }}</h2><p class="ops-panel-subtitle">{{ $items->total() }} {{ __('dashboard.food_menu') }}</p></div></div>
<div class="ops-list">
    @forelse($items as $item)
        <details class="ops-panel" style="margin-bottom:10px"><summary style="display:flex;align-items:center;justify-content:space-between;gap:12px;cursor:pointer;list-style:none"><span><strong style="display:block;font:600 14px var(--ops-display)">{{ $item->name }}</strong><small class="ops-secondary-cell">{{ $item->category }} · {{ config('club.currency','TJS') }} {{ number_format($item->price_cents / 100, 2) }}</small></span><span class="ops-badge ops-badge-{{ $item->is_available ? 'available' : 'unavailable' }}">{{ $item->is_available ? __('dashboard.available') : __('dashboard.unavailable') }}</span></summary>
            <form class="ops-form-grid" style="margin-top:16px;padding-top:16px;border-top:1px solid var(--ops-line)" method="POST" action="{{ route('admin.food-menu.update', $item) }}">@csrf @method('PUT')
                <label class="ops-field">{{ __('dashboard.item_name') }}<input class="ops-input" name="name" value="{{ $item->name }}" required maxlength="120"></label>
                <label class="ops-field">{{ __('dashboard.slug') }}<input class="ops-input" name="slug" value="{{ $item->slug }}" maxlength="140" required></label>
                <label class="ops-field">{{ __('dashboard.category') }}<input class="ops-input" name="category" value="{{ $item->category }}" required maxlength="60"></label>
                <label class="ops-field">{{ __('dashboard.price') }} ({{ config('club.currency','TJS') }})<input class="ops-input" type="number" name="price" value="{{ number_format($item->price_cents / 100, 2, '.', '') }}" required min="0.01" max="10000" step="0.01"></label>
                <label class="ops-field ops-form-full">{{ __('dashboard.description') }}<textarea class="ops-input" name="description" rows="2" maxlength="1000">{{ $item->description }}</textarea></label>
                <label class="ops-field ops-form-full">{{ __('dashboard.image_url') }}<input class="ops-input" type="url" name="image_url" value="{{ $item->image_url }}" maxlength="255"></label>
                <label class="ops-field">{{ __('dashboard.sort_order') }}<input class="ops-input" type="number" name="sort_order" value="{{ $item->sort_order }}" min="0" max="65535"></label>
                <div class="ops-form-full" style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap"><input type="hidden" name="is_available" value="0"><label class="ops-checkbox"><input type="checkbox" name="is_available" value="1" @checked($item->is_available)> {{ __('dashboard.available_for_order') }}</label><button class="ops-button" type="submit">{{ __('dashboard.save_item') }}</button></div>
            </form>
        </details>
    @empty <div class="ops-empty-block">{{ __('dashboard.no_food_items') }}</div> @endforelse
</div>
{{ $items->links() }}
@endsection
