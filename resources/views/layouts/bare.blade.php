<!DOCTYPE html>
<html lang="{{ webctx()->code() }}" dir="{{ webctx()->rtl() ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? brand('brand_name') }}</title>
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
@yield('content')
<script defer src="{{ asset('assets/alpine.min.js') }}"></script>
<script src="{{ asset('assets/app.js') }}"></script>
</body>
</html>
