@extends('layouts.app')

@section('content')
@php
    $width = match ($page->layout ?? 'narrow') {
        'wide'  => 'max-w-6xl',
        'full'  => 'max-w-none px-0',
        default => 'max-w-3xl',
    };
    // Server-side shortcodes: swap tokens for rendered partials.
    $segments = preg_split('/(\[pricing\]|\[tools\]|\[stats\]|\[cta\])/', t("page.{$page->slug}.content", $page->content ?? ''), -1, PREG_SPLIT_DELIM_CAPTURE);
    $gateways = array_keys(array_filter([
        'stripe' => (brand('payments')['stripe'] ?? false),
        'paypal' => (brand('payments')['paypal'] ?? false),
    ]));
@endphp
<div class="{{ $width }} mx-auto px-4 sm:px-6 py-14"
    x-data="toolGate(@js(auth()->check()), @js((bool) auth()->user()?->activeSubscription), @js($gateways))">
    <h1 class="font-display text-3xl md:text-4xl font-bold text-white mb-8">{{ t("page.{$page->slug}.title", $page->title) }}</h1>
    <div class="page-content text-slate-300 leading-relaxed space-y-4">
        @foreach ($segments as $seg)
            @if ($seg === '[pricing]')
                <div class="my-12 not-prose shortcode-bleed"><div class="shortcode-inner">
                    @include('partials.pricing-grid', ['packages' => $packages])
                </div></div>
            @elseif ($seg === '[tools]')
                @if ($tools->count())
                    <div class="my-12 not-prose shortcode-bleed"><div class="shortcode-inner">
                        @include('partials.tool-gate')
                        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                            @foreach ($tools as $tool)
                                <button type="button" @click="openTool('{{ $tool->slug }}', @js((bool) $tool->free_enabled))"
                                    class="card-glow glow-hover border-anim card-laminate p-6 text-center group w-full">
                                    <div class="flex justify-center gap-1.5 mb-4 min-h-[26px]">
                                        @if ($tool->free_enabled)
                                            <span class="text-[10px] uppercase tracking-widest text-ink-950 font-bold bg-brand rounded-full px-2.5 py-1">
                                                {{ t('free.badge', 'Free') }}
                                            </span>
                                        @endif
                                    </div>
                                    <span class="icon-tile mb-4 transition duration-300 group-hover:scale-110 group-hover:-translate-y-1"><x-icon :name="$tool->icon" :size="22" /></span>
                                    <h2 class="font-display font-semibold text-white text-lg">{{ t("tool.{$tool->slug}.name", $tool->name) }}</h2>
                                    @if ($tool->taxonomy)
                                        <span class="text-[10px] uppercase tracking-widest text-slate-500">{{ t("taxonomy.{$tool->taxonomy->slug}", $tool->taxonomy->name) }}</span>
                                    @endif
                                    <p class="text-sm text-slate-400 mt-1.5 mb-5">{{ t("tool.{$tool->slug}.desc", $tool->description) }}</p>
                                    <span class="inline-flex items-center gap-1.5 text-sm text-brand font-medium">
                                        {{ t('toolkit.open', 'Open tool') }} <x-icon name="ArrowRight" :size="14" class="transition group-hover:translate-x-1" />
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    </div></div>
                @endif
            @elseif ($seg === '[stats]')
                <div class="my-10 not-prose shortcode-bleed"><div class="shortcode-inner grid grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach ([
                        [$tools->count() . '+', t('stats.tools', 'Specialised AI tools included')],
                        [max(count(webctx()->languages()), 1) . '+', t('stats.langs', 'Interface languages supported')],
                        ['1', t('stats.sub', 'Simple all-inclusive subscription')],
                        ['∞', t('stats.ideas', 'Ideas you can bring to life')],
                    ] as [$value, $label])
                        <div class="card gradient-ring p-6 text-center">
                            <p class="font-display text-3xl font-bold animate-gradient-text">{{ $value }}</p>
                            <p class="text-xs text-slate-400 mt-2 leading-relaxed">{{ $label }}</p>
                        </div>
                    @endforeach
                </div></div>
            @elseif ($seg === '[cta]')
                <div class="my-10 not-prose shortcode-bleed"><div class="shortcode-inner relative overflow-hidden p-10 sm:p-12 text-center rounded-3xl"
                    style="background: radial-gradient(120% 140% at 15% 0%, #16213a 0%, #0b1220 55%, #080e1a 100%);
                           border: 1px solid rgb(var(--brand) / .38);
                           box-shadow: 0 30px 70px -28px rgb(var(--brand) / .45), inset 0 1px 0 rgb(255 255 255 / .07);">
                    <div class="absolute -top-24 -right-16 w-72 h-72 rounded-full pointer-events-none"
                        style="background: radial-gradient(circle, rgb(var(--brand) / .38), transparent 70%); filter: blur(18px)"></div>
                    <div class="absolute -bottom-28 -left-20 w-72 h-72 rounded-full pointer-events-none"
                        style="background: radial-gradient(circle, rgb(var(--accent) / .3), transparent 70%); filter: blur(18px)"></div>
                    <div class="absolute inset-0 pointer-events-none opacity-[.07] bg-grid-pattern"></div>
                    <div class="relative">
                        <span class="inline-flex items-center gap-1.5 text-[11px] uppercase tracking-[0.2em] rounded-full px-3.5 py-1 mb-5"
                            style="color: rgb(var(--brand)); border: 1px solid rgb(var(--brand) / .35); background: rgb(var(--brand) / .1)">
                            <x-icon name="Sparkles" :size="11" /> {{ t('cta.badge', 'Get started') }}
                        </span>
                        <h3 class="font-display text-2xl sm:text-3xl font-bold mb-3 !text-white">
                            {{ t('cta.title', 'Start creating with') }}
                            <span class="animate-gradient-text">{{ brand('brand_name') }}</span>
                        </h3>
                        <p class="text-sm sm:text-base mb-8 max-w-md mx-auto leading-relaxed" style="color: #cbd5e1">
                            {{ t('cta.subtitle', 'Set up your account in under a minute.') }}
                        </p>
                        <a href="{{ route('register') }}"
                            class="inline-flex items-center gap-2 rounded-xl px-8 py-3.5 text-sm font-bold transition hover:-translate-y-0.5 magnetic"
                            style="background: linear-gradient(135deg, rgb(var(--brand)), rgb(var(--accent))); color: #08111f; box-shadow: 0 14px 34px -10px rgb(var(--brand) / .6)">
                            {{ t('cta.button', 'Create free account') }} →
                        </a>
                    </div>
                </div></div>
            @else
                {!! $seg !!}
            @endif
        @endforeach
    </div>
</div>
@endsection
