@extends('layouts.app')

@section('content')
@php
    $TOOL_TAGS = [
        'ai-image-generator' => ['badge.popular', 'Most popular'],
        'ai-content-writer'  => ['badge.new', 'New'],
        'ai-translator'      => ['badge.new', 'New'],
    ];
    $cats = $tools->filter(fn ($x) => $x->taxonomy)->map(fn ($x) => $x->taxonomy)->unique('slug')->values();
    $gateways = array_keys(array_filter([
        'stripe' => (brand('payments')['stripe'] ?? false),
        'paypal' => (brand('payments')['paypal'] ?? false),
    ]));
@endphp
<div class="relative overflow-hidden"
    x-data="toolGate(@js(auth()->check()), @js((bool) auth()->user()?->activeSubscription), @js($gateways), { cat: 'all', celebrate: false, plan: '',
        init() {
            const p = new URLSearchParams(location.search);
            if (p.get('checkout') === 'success') {
                this.plan = decodeURIComponent(p.get('plan') || '');
                this.celebrate = true;
                history.replaceState({}, '', '/tools');
            }
        } })">
    <div class="absolute inset-0 bg-noise">
        <img src="{{ asset('art/tools-hero.png') }}" alt="" aria-hidden="true"
            class="w-full h-full object-cover opacity-[0.22] select-none pointer-events-none">
        <div class="absolute inset-0 bg-gradient-to-b from-ink-950/55 via-ink-950/80 to-ink-950"></div>
        <div class="absolute inset-0 bg-grid-pattern"></div>
    </div>
    <div class="relative max-w-6xl mx-auto px-6 py-16">
        <div class="relative text-center mb-14 animate-fade-up">
            <p class="inline-flex items-center gap-2 text-xs uppercase tracking-widest text-brand border border-brand/30 rounded-full px-4 py-1.5 mb-5">
                <x-icon name="Sparkles" :size="13" />
                {{ $tools->count() ? $tools->count().' '.t('tools.count_suffix', 'tools · one subscription') : t('tools.count_fallback', 'One subscription · every tool') }}
            </p>
            <h1 class="font-display text-3xl md:text-5xl font-bold text-white">
                <span class="text-gradient">{{ t('tools.title', 'Pick a tool, start creating') }}</span>
            </h1>
            <p class="text-slate-400 mt-4 max-w-xl mx-auto">
                {{ t('tools.subtitle2', 'Every subscription unlocks the complete toolkit — one set of credits that works across images, writing, translation, documents, audio and more. Select any tool below to open its workspace and start creating in seconds.') }}
            </p>
        </div>

        @if ($cats->count())
            <div class="relative flex flex-wrap justify-center gap-2 mb-10 animate-fade-up">
                <button type="button" @click="cat = 'all'"
                    class="px-5 py-2 rounded-full text-sm transition"
                    :class="cat === 'all' ? 'bg-brand text-ink-950 font-semibold shadow-lg shadow-brand/25' : 'text-slate-300 border border-ink-700 hover:border-brand/50'">
                    {{ t('tools.all_cats', 'All tools') }}
                </button>
                @foreach ($cats as $c)
                    <button type="button" @click="cat = '{{ $c->slug }}'"
                        class="px-5 py-2 rounded-full text-sm transition"
                        :class="cat === '{{ $c->slug }}' ? 'bg-brand text-ink-950 font-semibold shadow-lg shadow-brand/25' : 'text-slate-300 border border-ink-700 hover:border-brand/50'">
                        {{ t("taxonomy.{$c->slug}", $c->name) }}
                    </button>
                @endforeach
            </div>
        @endif

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($tools as $i => $tool)
                <button type="button" @click="openTool('{{ $tool->slug }}', @js((bool) $tool->free_enabled))"
                    @if ($tool->taxonomy) x-show="cat === 'all' || cat === '{{ $tool->taxonomy->slug }}'" @endif
                    class="card-glow glow-hover border-anim card-laminate p-6 text-center group animate-fade-up block w-full"
                    style="animation-delay: {{ $i * 60 }}ms">
                    <div class="flex justify-center gap-1.5 mb-4 min-h-[26px]">
                        <span class="flex gap-1.5">
                            @if ($tool->free_enabled)
                                <span class="text-[10px] uppercase tracking-widest text-ink-950 font-bold bg-brand rounded-full px-2.5 py-1">
                                    {{ t('free.badge', 'Free') }}
                                </span>
                            @endif
                            @if (isset($TOOL_TAGS[$tool->slug]))
                                <span class="text-[10px] uppercase tracking-widest text-brand border border-brand/30 rounded-full px-2.5 py-1">
                                    {{ t($TOOL_TAGS[$tool->slug][0], $TOOL_TAGS[$tool->slug][1]) }}
                                </span>
                            @endif
                        </span>
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
    </div>
@include('partials.tool-gate')

    {{-- "You're in!" celebration after checkout — port of SubscribedModal --}}
    <template x-teleport="body">
    <div x-show="celebrate" x-cloak
        class="fixed inset-0 z-50 grid place-items-center p-4 bg-ink-950/70 backdrop-blur-md" @click="celebrate = false">
        <div class="relative card gradient-ring p-8 w-full max-w-md text-center animate-pop-in overflow-hidden" @click.stop>
            <div class="aurora aurora-a w-[260px] h-[260px] -top-24 -left-20 animate-float-slow"></div>
            <div class="aurora aurora-b w-[220px] h-[220px] -bottom-20 -right-16 animate-float"></div>
            <div class="relative">
                <span class="relative inline-grid place-items-center w-20 h-20 rounded-full mb-5"
                    style="background: linear-gradient(135deg, rgb(var(--brand)), rgb(var(--accent)))">
                    <span class="absolute inset-0 rounded-full animate-ping opacity-25"
                        style="background: linear-gradient(135deg, rgb(var(--brand)), rgb(var(--accent)))"></span>
                    <x-icon name="Check" :size="38" class="text-ink-950 relative" />
                </span>
                <p class="inline-flex items-center gap-1.5 text-[11px] uppercase tracking-[0.2em] text-brand font-bold mb-2">
                    <x-icon name="PartyPopper" :size="13" /> {{ t('sub.badge', 'Payment successful') }}
                </p>
                <h2 class="font-display text-2xl font-bold text-white">{{ t('sub.title', "You're all set!") }}</h2>
                <p class="text-slate-300 mt-2.5 leading-relaxed">
                    <template x-if="plan">
                        <span>{{ t('sub.text_plan', 'Your subscription to') }} <strong class="animate-gradient-text" x-text="plan"></strong> {{ t('sub.text_plan2', 'is active. Your credits are loaded — every tool below is unlocked.') }}</span>
                    </template>
                    <template x-if="!plan">
                        <span>{{ t('sub.text', 'Your subscription is active. Your credits are loaded — every tool below is unlocked.') }}</span>
                    </template>
                </p>
                <button type="button" class="btn-brand w-full mt-7 animate-pulse-glow" @click="celebrate = false">
                    <x-icon name="Sparkles" :size="15" /> {{ t('sub.cta', 'Start creating') }}
                </button>
            </div>
        </div>
    </div>
</template>
    </div>
@endsection
