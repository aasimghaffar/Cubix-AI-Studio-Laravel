<!DOCTYPE html>
@php
    $b = brand();
    $headerStyle = $b['header_style'] ?? 'classic';
    $footerStyle = $b['footer_style'] ?? 'columns';
    $switcherPos = $b['language_switcher'] ?? 'header';
    $showHeaderSwitcher = in_array($switcherPos, ['header', 'both']);
    $showFooterSwitcher = in_array($switcherPos, ['footer', 'both']);
    $showFloatSwitcher  = $switcherPos === 'float';
    $menu = webctx()->menu();
    $footerMenu = webctx()->footerMenu();
    $user = auth()->user();
    $isActive = fn (string $path) => request()->path() === ltrim($path, '/') || ($path === '/' && request()->path() === '/');
    $navCls = fn (string $path) => 'text-sm transition '.($isActive($path) ? 'text-brand font-medium' : 'text-slate-300 hover:text-white');
    $coreNav = [
        ['to' => '/',        'key' => 'nav.home',    'fallback' => 'Home'],
        ['to' => '/tools',   'key' => 'nav.tools',   'fallback' => 'Tools'],
        ['to' => '/pricing', 'key' => 'nav.pricing', 'fallback' => 'Pricing'],
        ['to' => '/contact', 'key' => 'nav.contact', 'fallback' => 'Contact'],
    ];
@endphp
<html lang="{{ webctx()->code() }}" dir="{{ webctx()->rtl() ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? $b['brand_name'] }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    {{-- Apply the saved theme before first paint so light mode never flashes dark --}}
    <script>
        try { if (localStorage.getItem('theme') === 'light') document.documentElement.classList.add('theme-light'); } catch (e) {}
        window.__THEME_COLORS = @json($b['theme_colors'] ?? []);
    </script>
    <link rel="stylesheet" href="{{ asset('assets/app.css') }}">
    <style>[x-cloak]{display:none !important}</style>
</head>
<body class="font-body">

@if (($b['loader_enabled'] ?? '0') === '1' || $b['loader_enabled'] === true)
    @include('partials.loader')
@endif

<div class="min-h-screen flex flex-col relative">
    @include('partials.fx-background')

    <header data-site-header x-data="{ mobile: false, userMenu: false }"
        class="sticky top-0 z-40 backdrop-blur bg-ink-950/60 border-b border-transparent transition-all duration-300">

        @php
            // Shared fragments as closures keep the three header styles readable.
        @endphp

        @if ($headerStyle === 'centered')
            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-3">
                <div class="relative flex items-center justify-center h-10">
                    <div class="absolute left-0">
                        <button type="button" class="md:hidden p-2 rounded-lg border border-ink-700 text-slate-300"
                            @click="mobile = !mobile" aria-label="Menu">
                            <span x-show="!mobile"><x-icon name="Menu" :size="18" /></span>
                            <span x-show="mobile" x-cloak><x-icon name="X" :size="18" /></span>
                        </button>
                    </div>
                    <a href="{{ route('home') }}" class="flex items-center gap-2">
                        @include('partials.brand-mark')
                        <span class="font-display font-bold text-white">{{ $b['brand_name'] }}</span>
                    </a>
                    <div class="absolute right-0 flex items-center gap-2 sm:gap-3">
                        @if ($b['theme_toggle'] !== false)<span class="hidden sm:block">@include('partials.theme-toggle')</span>@endif
                        @if ($showHeaderSwitcher)<span class="hidden sm:block">@include('partials.lang-switcher')</span>@endif
                        @include('partials.user-actions')
                    </div>
                </div>
                <nav class="hidden md:flex items-center justify-center gap-8 pt-3">
                    @include('partials.nav-links')
                </nav>
            </div>
        @else
            <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-3">
                <a href="{{ route('home') }}" class="flex items-center gap-2 min-w-0">
                    @include('partials.brand-mark')
                    <span class="font-display font-bold text-white truncate">{{ $b['brand_name'] }}</span>
                </a>
                @if ($headerStyle === 'minimal')
                    <div class="flex items-center gap-2 sm:gap-6">
                        <nav class="hidden md:flex items-center gap-6">@include('partials.nav-links')</nav>
                        <div class="flex items-center gap-2 sm:gap-3">
                            @if ($b['theme_toggle'] !== false)<span class="hidden sm:block">@include('partials.theme-toggle')</span>@endif
                            @if ($showHeaderSwitcher)<span class="hidden sm:block">@include('partials.lang-switcher')</span>@endif
                            @include('partials.user-actions')
                        </div>
                        <button type="button" class="md:hidden p-2 rounded-lg border border-ink-700 text-slate-300"
                            @click="mobile = !mobile" aria-label="Menu">
                            <span x-show="!mobile"><x-icon name="Menu" :size="18" /></span>
                            <span x-show="mobile" x-cloak><x-icon name="X" :size="18" /></span>
                        </button>
                    </div>
                @else
                    <nav class="hidden md:flex items-center gap-8">@include('partials.nav-links')</nav>
                    <div class="flex items-center gap-2 sm:gap-3">
                        @if ($b['theme_toggle'] !== false)<span class="hidden sm:block">@include('partials.theme-toggle')</span>@endif
                        @if ($showHeaderSwitcher)<span class="hidden sm:block">@include('partials.lang-switcher')</span>@endif
                        @include('partials.user-actions')
                        <button type="button" class="md:hidden p-2 rounded-lg border border-ink-700 text-slate-300"
                            @click="mobile = !mobile" aria-label="Menu">
                            <span x-show="!mobile"><x-icon name="Menu" :size="18" /></span>
                            <span x-show="mobile" x-cloak><x-icon name="X" :size="18" /></span>
                        </button>
                    </div>
                @endif
            </div>
        @endif

        {{-- Mobile menu --}}
        <nav x-show="mobile" x-cloak
            class="md:hidden border-t border-ink-700/60 bg-ink-950/95 backdrop-blur px-4 py-3 space-y-1 animate-pop-in">
            @if ($menu->count())
                @foreach ($menu as $item)
                    @if ($item->children->count())
                        <p class="px-3 pt-2 pb-1 text-[11px] uppercase tracking-widest text-slate-500">{{ webctx()->menuLabel($item) }}</p>
                        @foreach ($item->children as $child)
                            <a href="{{ webctx()->isExternal($child) ? $child->target : url(webctx()->itemPath($child)) }}"
                                @if (webctx()->isExternal($child)) target="_blank" rel="noreferrer" @endif
                                class="block px-5 py-2 rounded-lg text-sm {{ $isActive(webctx()->itemPath($child)) ? 'bg-brand/15 text-brand' : 'text-slate-300 hover:bg-ink-800' }}">
                                {{ webctx()->menuLabel($child) }}
                            </a>
                        @endforeach
                    @else
                        <a href="{{ webctx()->isExternal($item) ? $item->target : url(webctx()->itemPath($item)) }}"
                            @if (webctx()->isExternal($item)) target="_blank" rel="noreferrer" @endif
                            class="block px-3 py-2.5 rounded-lg text-sm {{ $isActive(webctx()->itemPath($item)) ? 'bg-brand/15 text-brand' : 'text-slate-300 hover:bg-ink-800' }}">
                            {{ webctx()->menuLabel($item) }}
                        </a>
                    @endif
                @endforeach
            @else
                @foreach ($coreNav as $n)
                    <a href="{{ url($n['to']) }}"
                        class="block px-3 py-2.5 rounded-lg text-sm {{ $isActive($n['to']) ? 'bg-brand/15 text-brand' : 'text-slate-300 hover:bg-ink-800' }}">
                        {{ t($n['key'], $n['fallback']) }}
                    </a>
                @endforeach
            @endif
            @if ($b['theme_toggle'] !== false)<div class="px-3 py-2">@include('partials.theme-toggle')</div>@endif
            @guest
                <a href="{{ route('login') }}" class="block px-3 py-2.5 rounded-lg text-sm text-slate-300 hover:bg-ink-800">
                    {{ t('nav.signin', 'Sign in') }}
                </a>
            @endguest
            @if ($showHeaderSwitcher)<div class="px-3 py-2">@include('partials.lang-switcher', ['compact' => true])</div>@endif
        </nav>
    </header>

    <main class="relative z-10 flex-1 animate-page-in">
        @yield('content')
    </main>

    <footer class="relative z-10 mt-24 fx-footer">
        <div class="h-px w-full" style="background: linear-gradient(90deg, transparent, rgb(var(--brand) / .6), rgb(var(--accent) / .5), transparent); background-size: 200% 100%; animation: gradientShift 6s linear infinite"></div>
        <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-full max-w-[680px] h-64 pointer-events-none" aria-hidden="true"
            style="background: radial-gradient(ellipse at center, rgb(var(--brand) / .14), transparent 70%); filter: blur(10px)"></div>

        @if ($footerStyle === 'columns')
            <div class="relative max-w-6xl mx-auto px-4 sm:px-6 pt-14 pb-6">
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-10">
                    <div>
                        <div class="flex items-center gap-2.5 mb-3">
                            @include('partials.brand-mark')
                            <span class="font-display font-bold text-white text-xl animate-gradient-text">{{ $b['brand_name'] }}</span>
                        </div>
                        <p class="text-sm text-slate-400 leading-relaxed">
                            {{ t('footer.tagline', 'Images, words, and voice — every AI tool you need in one workspace, under one subscription.') }}
                        </p>
                        {{-- Newsletter → lands in the admin Messages inbox --}}
                        <div class="mt-5" x-data="newsletterBox()">
                            <p class="footer-heading">{{ t('footer.newsletter', 'Stay in the loop') }}</p>
                            <template x-if="state === 'ok'">
                                <p class="text-sm text-brand">{{ t('footer.subscribed', "You're subscribed — welcome aboard!") }}</p>
                            </template>
                            <template x-if="state !== 'ok'">
                                <div>
                                    <div class="flex gap-2">
                                        <input class="input !py-2 text-sm flex-1" type="email" placeholder="you@email.com"
                                            x-model="email" @keydown.enter="send()">
                                        <button type="button" class="btn-brand !px-4 !py-2 text-sm magnetic" @click="send()"
                                            :disabled="state === 'busy'" x-text="state === 'busy' ? '…' : '{{ t('footer.join', 'Join') }}'"></button>
                                    </div>
                                    <p class="text-xs text-red-400 mt-1.5" x-show="state && state !== 'busy'" x-text="state" x-cloak></p>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div>
                        <p class="footer-heading">{{ t('footer.explore', 'Explore') }}</p>
                        <ul class="space-y-2 text-sm">
                            @php
                                $explore = array_values(array_filter($footerMenu, fn ($m) => ! in_array((string) $m->target, ['faq', 'terms', 'privacy-policy'])));
                            @endphp
                            @if (count($explore))
                                @foreach ($explore as $item)
                                    <li>
                                        <a href="{{ webctx()->isExternal($item) ? $item->target : url(webctx()->itemPath($item)) }}"
                                            @if (webctx()->isExternal($item)) target="_blank" rel="noreferrer" @endif
                                            class="footer-link">{{ webctx()->menuLabel($item) }}</a>
                                    </li>
                                @endforeach
                            @else
                                <li><a href="{{ route('tools') }}" class="footer-link">{{ t('nav.tools', 'Tools') }}</a></li>
                                <li><a href="{{ route('pricing') }}" class="footer-link">{{ t('nav.pricing', 'Pricing') }}</a></li>
                                <li><a href="{{ route('contact') }}" class="footer-link">{{ t('nav.contact', 'Contact') }}</a></li>
                            @endif
                        </ul>
                    </div>
                    <div>
                        <p class="footer-heading">{{ t('footer.info', 'Info') }}</p>
                        <ul class="space-y-2 text-sm">
                            <li><a href="{{ url('/p/faq') }}" class="footer-link">{{ t('footer.faq', 'FAQ') }}</a></li>
                            <li><a href="{{ url('/p/terms') }}" class="footer-link">{{ t('footer.terms', 'Terms & Conditions') }}</a></li>
                            <li><a href="{{ url('/p/privacy-policy') }}" class="footer-link">{{ t('footer.privacy', 'Privacy Policy') }}</a></li>
                            @auth
                                <li><a href="{{ url('/account') }}" class="footer-link">{{ t('footer.account', 'My account') }}</a></li>
                            @else
                                <li><a href="{{ route('register') }}" class="footer-link">{{ t('footer.create', 'Create account') }}</a></li>
                            @endauth
                        </ul>
                    </div>
                    <div>
                        <p class="footer-heading">{{ t('footer.contact', 'Get in touch') }}</p>
                        <ul class="space-y-2.5 text-sm">
                            @if ($b['business']['email'] ?? null)
                                <li><a href="mailto:{{ $b['business']['email'] }}" class="footer-link break-all">{{ $b['business']['email'] }}</a></li>
                            @endif
                            @if ($b['business']['phone'] ?? null)
                                <li><a href="tel:{{ str_replace(' ', '', $b['business']['phone']) }}" class="footer-link">{{ $b['business']['phone'] }}</a></li>
                            @endif
                            @if ($b['business']['address'] ?? null)
                                <li class="text-slate-500 text-[13px] leading-relaxed">{{ $b['business']['address'] }}</li>
                            @endif
                            <li><a href="{{ route('contact') }}" class="text-brand hover:underline text-[13px]">{{ t('footer.message', 'Send us a message →') }}</a></li>
                        </ul>
                    </div>
                </div>
                <div class="flex flex-col items-center gap-3 mt-12 pt-5 pb-1 footer-divider">
                    <p class="text-xs text-slate-500 text-center">
                        © {{ date('Y') }} <span class="text-slate-400 font-medium">{{ $b['brand_name'] }}</span>. {{ t('footer.rights', 'All rights reserved.') }}
                    </p>
                    @if ($showFooterSwitcher)@include('partials.lang-switcher', ['compact' => true])@endif
                </div>
            </div>
        @elseif ($footerStyle === 'minimal')
            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-8 flex flex-col items-center gap-3 text-sm text-slate-500">
                @if (count($footerMenu))
                    <nav class="flex flex-wrap justify-center gap-x-5 gap-y-1.5 text-sm text-slate-400">
                        @foreach ($footerMenu as $item)
                            <a href="{{ webctx()->isExternal($item) ? $item->target : url(webctx()->itemPath($item)) }}"
                                @if (webctx()->isExternal($item)) target="_blank" rel="noreferrer" @endif
                                class="hover:text-white">{{ webctx()->menuLabel($item) }}</a>
                        @endforeach
                    </nav>
                @endif
                <span>© {{ date('Y') }} {{ $b['brand_name'] }}</span>
                @if ($showFooterSwitcher)@include('partials.lang-switcher', ['compact' => true])@endif
            </div>
        @else
            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-10 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2 text-slate-400">
                    @include('partials.brand-mark', ['size' => 16])
                    <span class="text-sm">{{ $b['brand_name'] }} — © {{ date('Y') }}</span>
                </div>
                <nav class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-sm text-slate-400">
                    @forelse ($footerMenu as $item)
                        <a href="{{ webctx()->isExternal($item) ? $item->target : url(webctx()->itemPath($item)) }}"
                            @if (webctx()->isExternal($item)) target="_blank" rel="noreferrer" @endif
                            class="hover:text-white">{{ webctx()->menuLabel($item) }}</a>
                    @empty
                        <a href="{{ route('tools') }}" class="hover:text-white">{{ t('nav.tools', 'Tools') }}</a>
                        <a href="{{ route('pricing') }}" class="hover:text-white">{{ t('nav.pricing', 'Pricing') }}</a>
                        <a href="{{ route('contact') }}" class="hover:text-white">{{ t('nav.contact', 'Contact') }}</a>
                    @endforelse
                    @if ($showFooterSwitcher)@include('partials.lang-switcher', ['compact' => true])@endif
                </nav>
            </div>
        @endif
    </footer>

    @if ($showFloatSwitcher)
        @php $langs = webctx()->languages(); $cur = webctx()->current(); @endphp
        @if (count($langs) >= 2)
            <div class="fixed bottom-5 right-5 rtl:right-auto rtl:left-5 z-50" x-data="{ open: false }" @click.outside="open = false">
                <div x-show="open" x-cloak class="absolute bottom-14 right-0 rtl:right-auto rtl:left-0 w-44 card p-2 shadow-2xl animate-pop-in">
                    @foreach ($langs as $l)
                        <a href="{{ route('lang', $l->code) }}"
                            class="block w-full text-left px-3 py-2 rounded-lg text-sm {{ $l->code === $cur?->code ? 'text-brand bg-brand/10' : 'text-slate-300 hover:bg-ink-800' }}">
                            {{ $l->native_name }}
                        </a>
                    @endforeach
                </div>
                <button type="button" @click="open = !open" aria-label="Change language"
                    class="w-12 h-12 rounded-full grid place-items-center bg-gradient-to-br from-[rgb(var(--brand))] to-[rgb(var(--accent))] text-ink-950 shadow-lg shadow-[rgb(var(--brand))]/30 hover:scale-105 transition">
                    <x-icon name="Globe" :size="20" />
                </button>
            </div>
        @endif
    @endif
</div>

<script>
    function newsletterBox() {
        return {
            email: '', state: null,
            async send() {
                if (!this.email.includes('@')) { this.state = @json(t('footer.bad_email', 'Enter a valid email address.')); return; }
                this.state = 'busy';
                try {
                    const res = await fetch('/api/contact', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify({ name: 'Newsletter signup', email: this.email, subject: 'Newsletter subscription', message: 'Please add ' + this.email + ' to the newsletter list.' }),
                    });
                    if (!res.ok) throw new Error();
                    this.state = 'ok';
                } catch (e) {
                    this.state = @json(t('footer.sub_failed', 'Could not subscribe right now — please try again.'));
                }
            },
        };
    }
</script>
<script defer src="{{ asset('assets/alpine.min.js') }}"></script>
<script src="{{ asset('assets/app.js') }}"></script>
@stack('scripts')
</body>
</html>
