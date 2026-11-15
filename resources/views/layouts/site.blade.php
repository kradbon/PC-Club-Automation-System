<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#080a0b"><meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="GB CLUB — PLAY HARD. LEVEL UP."><title>@yield('title', __('ui.brand')) | GB CLUB</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('css/app-fallback.css') }}?v=20261027"><script defer src="{{ asset('js/app-fallback.js') }}?v=20261027"></script>
    @endif
</head>
<body class="min-h-screen bg-arena-950 text-white antialiased">
    <div class="border-b border-acid/20 bg-acid px-4 py-2 text-center font-mono text-[10px] font-bold uppercase tracking-[.17em] text-arena-950"><span>GB CLUB</span><span class="mx-3 opacity-40">/</span><span>{{ __('ui.tagline') }}</span></div>
    <header class="site-header" data-header>
        <div class="site-nav">
            <a class="brand-lockup" href="{{ auth()->check() ? (auth()->user()->isAdmin() ? route('admin.dashboard') : (auth()->user()->isStaff() ? route('staff.bookings.index') : route('account.dashboard'))) : route('home') }}" aria-label="GB CLUB home"><span class="brand-mark">GB</span><span><strong>GB CLUB</strong><small>PLAY HARD. LEVEL UP.</small></span></a>
            <button class="menu-toggle" type="button" data-menu-toggle aria-expanded="false" aria-label="{{ __('ui.toggle_navigation') }}">&#9776;</button>
            <nav class="main-nav" data-main-nav aria-label="{{ __('ui.main_navigation') }}">
                @guest
                    <a href="{{ route('club') }}">{{ __('ui.club') }}</a><a href="{{ route('events.index') }}">{{ __('ui.events') }}</a><a href="{{ route('rooms') }}">{{ __('ui.zones') }}</a><a href="{{ route('tournaments.index') }}">{{ __('ui.tournaments') }}</a>
                @else
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}">{{ __('ui.admin') }}</a><a href="{{ route('admin.bookings.index') }}">{{ __('ui.bookings') }}</a><a href="{{ route('staff.food-orders.index') }}">{{ __('ui.food_orders_title') }}</a><a href="{{ route('admin.inventory') }}">{{ __('ui.pc_inventory') }}</a><a href="{{ route('admin.users') }}">{{ __('ui.users') }}</a><a href="{{ route('admin.content') }}">{{ __('ui.site_content') }}</a>
                    @elseif(auth()->user()->isStaff())
                        <a href="{{ route('staff.bookings.index') }}">{{ __('ui.staff_queue') }}</a><a href="{{ route('staff.food-orders.index') }}">{{ __('ui.food_orders_title') }}</a>
                    @else
                        <a href="{{ route('account.dashboard') }}">{{ __('ui.my_account') }}</a><a href="{{ route('account.bookings.index') }}">{{ __('ui.bookings') }}</a><a href="{{ route('account.food') }}">{{ __('ui.food') }}</a><a href="{{ route('account.wallet') }}">{{ __('ui.wallet') }}</a><a href="{{ route('account.orders') }}">{{ __('ui.orders') }}</a><a href="{{ route('account.teams.index') }}">{{ __('ui.teams') }}</a>
                    @endif
                @endguest
            </nav>
            <div class="nav-actions">
                <div class="language-menu"><button type="button" data-language-toggle aria-expanded="false" aria-label="{{ __('ui.language') }}">{{ strtoupper(app()->getLocale()) }}&#9662;</button><div class="language-options" data-language-options><a href="{{ route('locale.switch','en') }}">EN</a><a href="{{ route('locale.switch','ru') }}">RU</a><a href="{{ route('locale.switch','tj') }}">TJ</a></div></div>
                @auth
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="arena-button small-button">{{ __('ui.sign_out') }}</button></form>
                @else
                    <a href="{{ route('login') }}">{{ __('ui.login') }}</a><a class="arena-button small-button" href="{{ route('register') }}">{{ __('ui.join') }} &rarr;</a>
                @endauth
            </div>
        </div>
    </header>
    <main class="site-main">
        @if (session('success'))<div class="flash-message" role="status">{{ session('success') }}</div>@endif
        @if ($errors->any())<div class="flash-error" role="alert">{{ __('ui.validation_summary') }}</div>@endif
        @yield('content')
    </main>
    <footer class="site-footer">
        <a class="footer-brand" href="{{ auth()->check() ? (auth()->user()->isAdmin() ? route('admin.dashboard') : (auth()->user()->isStaff() ? route('staff.bookings.index') : route('account.dashboard'))) : route('home') }}">GB <span>CLUB</span></a>
        <span class="footer-tagline">{{ __('ui.tagline') }} / {{ date('Y') }}</span>
        <div>@guest<a href="{{ route('events.index') }}">{{ __('ui.events') }}</a><a href="{{ route('rooms') }}">{{ __('ui.zones') }}</a><a href="{{ route('club') }}">{{ __('ui.contact') }}</a>@else @if(auth()->user()->isAdmin() || auth()->user()->isStaff())<a href="{{ route('staff.food-orders.index') }}">{{ __('ui.food_orders_title') }}</a>@else<a href="{{ route('account.food') }}">{{ __('ui.food') }}</a><a href="{{ route('account.wallet') }}">{{ __('ui.wallet') }}</a><a href="{{ route('account.orders') }}">{{ __('ui.orders') }}</a>@endif @endguest</div>
    </footer>
</body>
</html>
