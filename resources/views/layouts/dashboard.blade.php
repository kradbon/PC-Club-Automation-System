<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('dashboard.overview')) — GB CLUB</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=Inter:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app-fallback.css') }}?v=20261027-2">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}?v=20261027-4">
</head>
<body class="ops-body">
    @php($isAdmin = auth()->user()->isAdmin())
    @php($isCustomer = in_array(auth()->user()->role, ['customer', 'member'], true))
    @php($workspaceRoute = $isAdmin ? 'admin.dashboard' : ($isCustomer ? 'account.dashboard' : 'staff.dashboard'))
    @php($workspaceLabel = $isAdmin ? __('dashboard.administrator') : ($isCustomer ? __('ui.my_account') : __('dashboard.staff_workspace')))
    <div class="ops-shell">
        <div class="ops-scrim" data-ops-scrim></div>
        <aside class="ops-sidebar" id="ops-sidebar" aria-label="{{ __('dashboard.navigation') }}">
            <a class="ops-brand" href="{{ route($workspaceRoute) }}"><span class="ops-brand-mark">GB</span><span><strong>GB CLUB</strong><small>{{ $isCustomer ? __('ui.my_account') : __('dashboard.control_center') }}</small></span></a>
            <div class="ops-role"><span class="ops-online-dot"></span>{{ $workspaceLabel }}</div>
            <nav class="ops-nav">
                @if($isAdmin)
                    <p class="ops-nav-label">{{ __('dashboard.overview_group') }}</p>
                    <a class="ops-nav-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}" href="{{ route('admin.dashboard') }}"><span class="ops-nav-icon">◫</span>{{ __('dashboard.dashboard') }}</a>
                    <a class="ops-nav-link" href="{{ route('admin.dashboard') }}#statistics"><span class="ops-nav-icon">⌁</span>{{ __('dashboard.statistics') }}</a>

                    <p class="ops-nav-label">{{ __('dashboard.club_operations') }}</p>
                    <a class="ops-nav-link {{ request()->routeIs('admin.bookings.*') ? 'is-active' : '' }}" href="{{ route('admin.bookings.index') }}"><span class="ops-nav-icon">◷</span>{{ __('dashboard.reservations') }}<span class="ops-nav-count">{{ \App\Models\Booking::where('status','pending')->count() }}</span></a>
                    <a class="ops-nav-link {{ request()->routeIs('admin.inventory') ? 'is-active' : '' }}" href="{{ route('admin.inventory') }}"><span class="ops-nav-icon">▦</span>{{ __('dashboard.pc_inventory') }}</a>
                    <a class="ops-nav-link {{ request()->routeIs('admin.rooms') ? 'is-active' : '' }}" href="{{ route('admin.rooms') }}"><span class="ops-nav-icon">▧</span>{{ __('dashboard.zones') }}</a>
                    <a class="ops-nav-link {{ request()->routeIs('admin.tariffs') ? 'is-active' : '' }}" href="{{ route('admin.tariffs') }}"><span class="ops-nav-icon">◈</span>{{ __('dashboard.pricing') }}</a>
                    <a class="ops-nav-link {{ request()->routeIs('admin.food-menu') ? 'is-active' : '' }}" href="{{ route('admin.food-menu') }}"><span class="ops-nav-icon">◉</span>{{ __('dashboard.food_menu') }}</a>
                    <a class="ops-nav-link {{ request()->routeIs('staff.food-orders.*') ? 'is-active' : '' }}" href="{{ route('staff.food-orders.index') }}"><span class="ops-nav-icon">▤</span>{{ __('dashboard.food_orders') }}</a>

                    <p class="ops-nav-label">{{ __('dashboard.customers_finance') }}</p>
                    <a class="ops-nav-link {{ request()->routeIs('admin.users') ? 'is-active' : '' }}" href="{{ route('admin.users') }}"><span class="ops-nav-icon">♙</span>{{ __('dashboard.customers_roles') }}</a>
                    <a class="ops-nav-link {{ request()->routeIs('admin.financials') ? 'is-active' : '' }}" href="{{ route('admin.financials') }}"><span class="ops-nav-icon">＄</span>{{ __('dashboard.wallets_payments') }}</a>

                    <p class="ops-nav-label">{{ __('dashboard.competitions') }}</p>
                    <a class="ops-nav-link {{ request()->routeIs('admin.tournaments') ? 'is-active' : '' }}" href="{{ route('admin.tournaments') }}"><span class="ops-nav-icon">♜</span>{{ __('dashboard.tournaments') }}</a>

                    <p class="ops-nav-label">{{ __('dashboard.website_group') }}</p>
                    <a class="ops-nav-link {{ request()->routeIs('admin.discounts') ? 'is-active' : '' }}" href="{{ route('admin.discounts') }}"><span class="ops-nav-icon">％</span>{{ __('dashboard.discounts') }}</a>
                    <a class="ops-nav-link {{ request()->routeIs('admin.content') ? 'is-active' : '' }}" href="{{ route('admin.content') }}"><span class="ops-nav-icon">▧</span>{{ __('dashboard.pages_activity') }}</a>
                    <a class="ops-nav-link {{ request()->routeIs('admin.events') ? 'is-active' : '' }}" href="{{ route('admin.events') }}"><span class="ops-nav-icon">◷</span>{{ __('dashboard.events') }}</a>
                    <a class="ops-nav-link {{ request()->routeIs('admin.reviews') ? 'is-active' : '' }}" href="{{ route('admin.reviews') }}"><span class="ops-nav-icon">☆</span>{{ __('dashboard.reviews') }}</a>
                    <p class="ops-nav-label">{{ __('dashboard.account_group') }}</p>
                    <a class="ops-nav-link {{ request()->routeIs('admin.profile') ? 'is-active' : '' }}" href="{{ route('admin.profile') }}"><span class="ops-nav-icon">A</span>{{ __('dashboard.my_profile') }}</a>
                @elseif($isCustomer)
                    <p class="ops-nav-label">{{ __('dashboard.overview_group') }}</p>
                    <a class="ops-nav-link {{ request()->routeIs('account.dashboard') ? 'is-active' : '' }}" href="{{ route('account.dashboard') }}"><span class="ops-nav-icon">O</span>{{ __('dashboard.overview') }}</a>
                    <p class="ops-nav-label">{{ __('dashboard.club_operations') }}</p>
                    <a class="ops-nav-link {{ request()->routeIs('account.bookings.*') ? 'is-active' : '' }}" href="{{ route('account.bookings.index') }}"><span class="ops-nav-icon">R</span>{{ __('dashboard.reservations') }}</a>
                    <a class="ops-nav-link {{ request()->routeIs('account.sessions') ? 'is-active' : '' }}" href="{{ route('account.sessions') }}"><span class="ops-nav-icon">S</span>{{ __('ui.my_sessions') }}</a>
                    <a class="ops-nav-link" href="{{ route('tournaments.index') }}"><span class="ops-nav-icon">T</span>{{ __('dashboard.tournaments') }}</a>
                    <a class="ops-nav-link {{ request()->routeIs('account.food') ? 'is-active' : '' }}" href="{{ route('account.food') }}"><span class="ops-nav-icon">F</span>{{ __('ui.food') }}</a>
                    <a class="ops-nav-link {{ request()->routeIs('account.tariffs') ? 'is-active' : '' }}" href="{{ route('account.tariffs') }}"><span class="ops-nav-icon">P</span>{{ __('dashboard.pricing') }}</a>
                    <a class="ops-nav-link {{ request()->routeIs('account.orders') ? 'is-active' : '' }}" href="{{ route('account.orders') }}"><span class="ops-nav-icon">O</span>{{ __('ui.orders') }}</a>
                    <p class="ops-nav-label">{{ __('dashboard.account_group') }}</p>
                    <a class="ops-nav-link {{ request()->routeIs('account.wallet') ? 'is-active' : '' }}" href="{{ route('account.wallet') }}"><span class="ops-nav-icon">$</span>{{ __('ui.wallet') }} &amp; {{ __('ui.bonus_balance') }}</a>
                    <a class="ops-nav-link {{ request()->routeIs('account.teams.*') ? 'is-active' : '' }}" href="{{ route('account.teams.index') }}"><span class="ops-nav-icon">T</span>{{ __('ui.teams') }}</a>
                    <a class="ops-nav-link {{ request()->routeIs('account.profile*') ? 'is-active' : '' }}" href="{{ route('account.profile') }}"><span class="ops-nav-icon">A</span>{{ __('ui.profile_settings') }}</a>
                @else
                    <p class="ops-nav-label">{{ __('dashboard.my_work') }}</p>
                    <a class="ops-nav-link {{ request()->routeIs('staff.dashboard') ? 'is-active' : '' }}" href="{{ route('staff.dashboard') }}"><span class="ops-nav-icon">◫</span>{{ __('dashboard.overview') }}</a>
                    <a class="ops-nav-link {{ request()->routeIs('staff.bookings.*') ? 'is-active' : '' }}" href="{{ route('staff.bookings.index') }}"><span class="ops-nav-icon">◷</span>{{ __('dashboard.reservations') }}</a>
                    <a class="ops-nav-link {{ request()->routeIs('staff.pcs') ? 'is-active' : '' }}" href="{{ route('staff.pcs') }}"><span class="ops-nav-icon">▦</span>{{ __('dashboard.pc_status') }}</a>
                    <a class="ops-nav-link {{ request()->routeIs('staff.food-orders.*') ? 'is-active' : '' }}" href="{{ route('staff.food-orders.index') }}"><span class="ops-nav-icon">▤</span>{{ __('dashboard.food_orders') }}</a>
                    <p class="ops-nav-label">{{ __('dashboard.account_group') }}</p>
                    <a class="ops-nav-link {{ request()->routeIs('staff.profile') ? 'is-active' : '' }}" href="{{ route('staff.profile') }}"><span class="ops-nav-icon">♙</span>{{ __('dashboard.my_profile') }}</a>
                @endif
            </nav>
            <div class="ops-sidebar-footer"><span class="ops-avatar">@if(auth()->user()->avatar_path)<img src="{{ asset('storage/'.auth()->user()->avatar_path) }}" alt="">@else{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}@endif</span><span class="ops-user-label"><strong>{{ auth()->user()->name }}</strong><small>{{ $workspaceLabel }}</small></span><form method="POST" action="{{ route('logout') }}">@csrf<button class="ops-logout" type="submit" title="{{ __('ui.sign_out') }}">↗</button></form></div>
        </aside>

        <div class="ops-main">
            <header class="ops-topbar">
                <button type="button" class="ops-menu-toggle" data-ops-toggle aria-controls="ops-sidebar" aria-expanded="false" aria-label="{{ __('dashboard.open_navigation') }}">☰</button>
                <div class="ops-breadcrumb"><span>GB CLUB</span><b>/</b><span>{{ $isAdmin ? __('dashboard.administration') : ($isCustomer ? __('ui.my_account') : __('dashboard.operations')) }}</span><b>/</b><strong>@yield('page-title', \Illuminate\Support\Str::before($__env->yieldContent('title', __('dashboard.overview')), ' | GB CLUB'))</strong></div>
                <div class="ops-top-actions"><span class="ops-date">{{ now()->format('D, M j') }}</span><div class="ops-languages"><a class="{{ app()->getLocale()==='en'?'is-current':'' }}" href="{{ route('locale.switch','en') }}">EN</a><a class="{{ app()->getLocale()==='ru'?'is-current':'' }}" href="{{ route('locale.switch','ru') }}">RU</a><a class="{{ app()->getLocale()==='tj'?'is-current':'' }}" href="{{ route('locale.switch','tj') }}">TJ</a></div><a class="ops-top-avatar" href="{{ route($isAdmin ? 'admin.profile' : ($isCustomer ? 'account.profile' : 'staff.profile')) }}" aria-label="{{ __('dashboard.my_profile') }}">@if(auth()->user()->avatar_path)<img src="{{ asset('storage/'.auth()->user()->avatar_path) }}" alt="">@else{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}@endif</a></div>
            </header>
            <main class="ops-content">
                @if(session('success'))<div class="ops-alert ops-alert-success" role="status">{{ session('success') }}</div>@endif
                @if($errors->any())<div class="ops-alert ops-alert-error" role="alert">{{ $errors->first() }}</div>@endif
                @yield('content')
                <footer class="ops-footer"><span>GB CLUB <i>/</i> {{ __('dashboard.control_center') }}</span><span>{{ now()->year }} · {{ __('dashboard.secure_operations') }}</span></footer>
            </main>
        </div>
    </div>
    <script src="{{ asset('js/dashboard.js') }}?v=20261027-4" defer></script>
</body>
</html>
