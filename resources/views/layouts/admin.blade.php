<!DOCTYPE html>
@php
    $NAV = [
        ['to' => '/admin', 'icon' => 'Gauge', 'label' => 'Dashboard', 'exact' => true],
        ['to' => '/admin/packages', 'icon' => 'Boxes', 'label' => 'Packages'],
        ['icon' => 'Users', 'label' => 'Customers', 'children' => [
            ['to' => '/admin/customers', 'label' => 'Customers'],
            ['to' => '/admin/subscriptions', 'label' => 'Subscriptions'],
            ['to' => '/admin/usage', 'label' => 'Usage logs'],
        ]],
        ['icon' => 'Cpu', 'label' => 'AI Settings', 'children' => [
            ['to' => '/admin/tools', 'label' => 'AI tools'],
            ['to' => '/admin/engines', 'label' => 'AI Engines'],
            ['to' => '/admin/taxonomies', 'label' => 'Categories'],
            ['to' => '/admin/keys', 'label' => 'API Keys'],
        ]],
        ['icon' => 'FileText', 'label' => 'Pages', 'children' => [
            ['to' => '/admin/pages', 'label' => 'All pages'],
            ['to' => '/admin/menu', 'label' => 'Menu'],
            ['to' => '/admin/shortcodes', 'label' => 'Shortcodes'],
            ['to' => '/admin/testimonials', 'label' => 'Testimonials'],
        ]],
        ['to' => '/admin/messages', 'icon' => 'Inbox', 'label' => 'Messages'],
        ['to' => '/admin/appearance', 'icon' => 'Palette', 'label' => 'Appearance'],
        ['to' => '/admin/languages', 'icon' => 'Globe', 'label' => 'Languages'],
        ['to' => '/admin/settings', 'icon' => 'Settings2', 'label' => 'Settings'],
    ];
    $path = '/'.request()->path();
    $isActive = function (string $to, bool $exact = false) use ($path) {
        return $exact ? $path === $to : str_starts_with($path, $to);
    };
@endphp
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin — {{ brand('brand_name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script>
        try { if (localStorage.getItem('theme') === 'light') document.documentElement.classList.add('theme-light'); } catch (e) {}
        window.__THEME_COLORS = @json(brand('theme_colors') ?? []);
    </script>
    <link rel="stylesheet" href="{{ asset('assets/app.css') }}">
    <style>[x-cloak]{display:none !important}</style>
</head>
<body class="font-body">
<div class="min-h-screen flex flex-col lg:flex-row" x-data="{ drawer: false }">

    {{-- Mobile top bar --}}
    <div class="lg:hidden sticky top-0 z-40 flex items-center justify-between px-4 h-14 border-b border-ink-700/60 bg-ink-950/90 backdrop-blur">
        <div class="flex items-center gap-2">
            <x-icon name="ShieldCheck" :size="20" class="text-brand" />
            <span class="font-display font-bold text-white text-sm">Admin panel</span>
        </div>
        <button type="button" @click="drawer = true" class="p-2 rounded-lg border border-ink-700 text-slate-300" aria-label="Menu">
            <x-icon name="Menu" :size="18" />
        </button>
    </div>

    {{-- Mobile drawer --}}
    <div x-show="drawer" x-cloak class="lg:hidden fixed inset-0 z-50 bg-ink-950/60 backdrop-blur-sm" @click="drawer = false">
        <aside class="absolute left-0 top-0 bottom-0 w-72 bg-ink-900 border-r border-ink-700/60 p-4 flex flex-col gap-1 overflow-y-auto animate-pop-in" @click.stop>
            <button type="button" @click="drawer = false" class="self-end p-2 text-slate-400" aria-label="Close">
                <x-icon name="X" :size="18" />
            </button>
            @include('layouts.partials.admin-sidebar')
        </aside>
    </div>

    {{-- Desktop sidebar --}}
    <aside class="hidden lg:flex w-64 shrink-0 border-r border-ink-700/60 p-4 flex-col gap-1 bg-ink-900/40">
        @include('layouts.partials.admin-sidebar')
    </aside>

    <main class="flex-1 p-4 sm:p-8 w-full animate-page-in">
        @yield('content')
    </main>
</div>

<script defer src="{{ asset('assets/alpine.min.js') }}"></script>
<script src="{{ asset('assets/chart.umd.js') }}"></script>
<script src="{{ asset('assets/app.js') }}"></script>
@stack('scripts')
</body>
</html>
