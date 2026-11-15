@extends('layouts.dashboard')
@section('title', 'Website content | GB CLUB')
@section('page-title', __('dashboard.pages_activity'))
@section('content')
<header class="ops-page-head"><div><p class="ops-kicker">{{ __('dashboard.website_group') }} / CMS</p><h1>{{ __('dashboard.pages_activity') }}</h1><p>Manage published page copy, promotions, and the administrative audit trail.</p></div><a class="ops-button ops-button-secondary" href="{{ route('admin.discounts') }}">{{ __('dashboard.discounts') }} ↓</a></header>

<section class="ops-panel" style="margin-bottom:18px"><div class="ops-panel-head"><div><h2 class="ops-panel-title">Published pages</h2><p class="ops-panel-subtitle">Localized page content · English, Russian, Tajik</p></div></div><div class="ops-list">
@foreach($pages as $page)
<details class="ops-list-item" style="display:block;padding:13px 0"><summary style="cursor:pointer;list-style:none"><strong>{{ $page->localized('title','en') }}</strong><span class="ops-secondary-cell">/{{ $page->slug }} · {{ $page->is_published ? 'Published' : 'Draft' }}</span></summary>
<form class="ops-form-grid" style="margin-top:14px" method="POST" action="{{ route('admin.content.update',$page) }}">@csrf @method('PUT')<input type="hidden" name="is_published" value="0"><label class="ops-checkbox ops-form-full"><input type="checkbox" name="is_published" value="1" @checked($page->is_published)> Published on public site</label>
@foreach(['en'=>'English','ru'=>'Russian','tj'=>'Tajik'] as $locale=>$label)<label class="ops-field">Title / {{ $label }}<input class="ops-input" name="title_{{ $locale }}" value="{{ $page->title_translations[$locale] ?? '' }}" @required($locale==='en')></label>@endforeach
@foreach(['en'=>'English','ru'=>'Russian','tj'=>'Tajik'] as $locale=>$label)<label class="ops-field">Body / {{ $label }}<textarea class="ops-input" name="body_{{ $locale }}" rows="4" @required($locale==='en')>{{ $page->body_translations[$locale] ?? '' }}</textarea></label>@endforeach
<div class="ops-form-full"><button class="ops-button" type="submit">Save page</button></div></form></details>
@endforeach
</div></section>

<section class="ops-panel" id="promotions" style="margin-bottom:18px"><div class="ops-panel-head"><div><h2 class="ops-panel-title">{{ __('dashboard.discounts') }}</h2><p class="ops-panel-subtitle">Create campaigns and control whether each promotion is active.</p></div></div>
<form class="ops-form-grid" method="POST" action="{{ route('admin.promotions.store') }}">@csrf
<label class="ops-field">Code (optional)<input class="ops-input" name="code" maxlength="40"></label><label class="ops-field">Campaign title<input class="ops-input" name="title_en" required maxlength="180"></label>
<label class="ops-field">Reward type<select class="ops-input" name="reward_type"><option value="discount_percent">Discount percentage</option><option value="bonus_fixed">Fixed bonus credit</option><option value="bonus_percent">Bonus percentage</option></select></label>
<label class="ops-field">Reward value<input class="ops-input" name="reward_value" type="number" step="0.01" min="0.01" max="10000" required></label><label class="ops-field">Minimum purchase<input class="ops-input" name="minimum_purchase" type="number" step="0.01" min="0" value="0"></label><label class="ops-field">Usage limit<input class="ops-input" name="usage_limit" type="number" min="1"></label>
<label class="ops-field">Starts<input class="ops-input" name="starts_at" type="datetime-local"></label><label class="ops-field">Ends<input class="ops-input" name="ends_at" type="datetime-local"></label><label class="ops-field ops-form-full">Description<input class="ops-input" name="description_en" maxlength="2000"></label>
<div class="ops-form-full"><button class="ops-button" type="submit">＋ {{ __('dashboard.add_promotion') }}</button></div></form>
<div class="ops-list" style="margin-top:18px">
@forelse($promotions as $promotion)
<details class="ops-list-item" style="display:block;padding:13px 0"><summary style="display:flex;align-items:center;justify-content:space-between;gap:15px;cursor:pointer;list-style:none"><span><strong>{{ $promotion->localized('title','en') }}</strong><small class="ops-secondary-cell">{{ $promotion->code ?? 'No code' }} · {{ $promotion->reward_type }} / {{ $promotion->reward_value }} · {{ $promotion->uses_count }}{{ $promotion->usage_limit ? ' of '.$promotion->usage_limit : ' uses' }}</small></span><span class="ops-badge ops-badge-{{ $promotion->is_active ? 'available' : 'unavailable' }}">{{ $promotion->is_active ? 'Active' : 'Paused' }}</span></summary>
<form class="ops-form-grid" style="margin-top:14px" method="POST" action="{{ route('admin.promotions.update',$promotion) }}">@csrf @method('PUT')
<label class="ops-field">Code<input class="ops-input" name="code" maxlength="40" value="{{ $promotion->code }}"></label><label class="ops-field">Campaign title<input class="ops-input" name="title_en" required maxlength="180" value="{{ $promotion->localized('title','en') }}"></label>
<label class="ops-field">Reward type<select class="ops-input" name="reward_type"><option value="discount_percent" @selected($promotion->reward_type==='discount_percent')>Discount percentage</option><option value="bonus_fixed" @selected($promotion->reward_type==='bonus_fixed')>Fixed bonus credit</option><option value="bonus_percent" @selected($promotion->reward_type==='bonus_percent')>Bonus percentage</option></select></label><label class="ops-field">Reward value<input class="ops-input" name="reward_value" type="number" step="0.01" min="0.01" value="{{ $promotion->reward_value }}" required></label>
<label class="ops-field">Minimum purchase<input class="ops-input" name="minimum_purchase" type="number" step="0.01" min="0" value="{{ $promotion->minimum_purchase }}"></label><label class="ops-field">Usage limit<input class="ops-input" name="usage_limit" type="number" min="1" value="{{ $promotion->usage_limit }}"></label>
<label class="ops-field">Starts<input class="ops-input" name="starts_at" type="datetime-local" value="{{ $promotion->starts_at?->format('Y-m-d\TH:i') }}"></label><label class="ops-field">Ends<input class="ops-input" name="ends_at" type="datetime-local" value="{{ $promotion->ends_at?->format('Y-m-d\TH:i') }}"></label><label class="ops-field ops-form-full">Description<input class="ops-input" name="description_en" maxlength="2000" value="{{ $promotion->localized('description','en') }}"></label>
<div class="ops-form-full" style="display:flex;align-items:center;justify-content:space-between;gap:15px"><input type="hidden" name="is_active" value="0"><label class="ops-checkbox"><input type="checkbox" name="is_active" value="1" @checked($promotion->is_active)> Active</label><button class="ops-button" type="submit">Save promotion</button></div>
</form></details>
@empty<p class="ops-empty-block">No promotions created yet.</p>@endforelse
</div></section>

<section class="ops-panel" id="activity"><div class="ops-panel-head"><div><h2 class="ops-panel-title">Recent audit activity</h2><p class="ops-panel-subtitle">Administrative actions saved with actor and timestamp.</p></div></div>
@forelse($logs as $log)<div class="ops-list-item"><div><strong>{{ str_replace(['.','_'], ' ', $log->action) }}</strong><small>{{ $log->actor?->name ?? 'System' }} · {{ class_basename($log->subject_type ?? '') }}</small></div><span class="ops-list-meta">{{ $log->created_at->format('Y-m-d H:i:s') }}</span></div>@empty<div class="ops-empty-block">{{ __('dashboard.no_activity') }}</div>@endforelse
</section>
@endsection
