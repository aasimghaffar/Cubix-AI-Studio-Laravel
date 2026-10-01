@extends('layouts.app')

@section('content')
@php
    $user = auth()->user();
    $hasPlan = (bool) $user?->activeSubscription;
    $langCount = max(count(webctx()->languages()), 1);
    $typePhrases = [
        t('hero.type1', 'a neon cyberpunk city, ultra detailed…'),
        t('hero.type2', 'a 1,500-word article about smart homes…'),
        t('hero.type3', 'translate my pitch into Japanese…'),
        t('hero.type4', 'summarize this 40-page report…'),
        t('hero.type5', 'a calm voice-over for my video…'),
    ];
@endphp

{{-- ── Hero: full-width background straight under the menu ── --}}
<section class="relative overflow-hidden">
    <div class="absolute inset-0 bg-noise">
        <img src="{{ asset('art/ai-banner.png') }}" alt="" aria-hidden="true"
            class="w-full h-full object-cover opacity-50 select-none pointer-events-none">
        <div class="absolute inset-0 bg-gradient-to-b from-ink-950/35 via-ink-950/65 to-ink-950"></div>
        <div class="absolute inset-0 bg-grid-pattern"></div>
    </div>
    <div class="aurora aurora-a w-[420px] h-[420px] -top-32 -left-24"></div>
    <div class="aurora aurora-b w-[380px] h-[380px] top-10 -right-24"></div>

    <div class="relative max-w-6xl mx-auto px-4 sm:px-6 pt-16 sm:pt-24 pb-14 grid lg:grid-cols-2 gap-12 items-center">
        <div class="min-w-0 text-center lg:text-left hero-reveal">
            <p class="inline-flex items-center gap-2 text-xs uppercase tracking-widest text-brand border border-brand/30 rounded-full px-4 py-1.5 mb-6 glass">
                <x-icon name="Zap" :size="13" /> {{ t('hero.badge', 'All your AI tools, one workspace') }}
            </p>
            <h1 class="font-display text-4xl sm:text-5xl lg:text-6xl font-bold text-white leading-[1.08] tracking-tight">
                {{ t('hero.title', 'Create images, words & voice with') }}
                <span class="animate-gradient-text flip-in inline-block">{{ brand('brand_name') }}</span>
            </h1>
            <p class="mt-4 font-body text-sm sm:text-base text-slate-300">
                <span class="text-brand font-semibold">{{ t('hero.prompt', 'Prompt:') }}</span>
                <span class="type-caret" data-typewriter='@json($typePhrases)'></span>
            </p>
            <p class="text-slate-400 text-base sm:text-lg mt-5 max-w-xl mx-auto lg:mx-0">
                {{ t('hero.subtitle2', 'A complete creative suite powered by leading AI models: generate striking visuals, write publication-ready content, translate naturally between languages, question your documents, remove image backgrounds in one click, and turn text into lifelike voiceovers — all from a single dashboard, under one simple subscription.') }}
            </p>
            <div class="flex flex-wrap items-center justify-center lg:justify-start gap-4 mt-9">
                @if ($user)
                    <a href="{{ route('tools') }}" class="btn-brand !px-7 !py-3 magnetic">
                        {{ $hasPlan ? t('hero.open_tools', 'Open your tools') : t('hero.explore', 'Explore the tools') }}
                        <x-icon name="ArrowRight" :size="16" />
                    </a>
                    @unless ($hasPlan)
                        <a href="{{ route('pricing') }}" class="btn-ghost !px-7 !py-3">{{ t('hero.see_plans', 'See plans') }}</a>
                    @endunless
                @else
                    <a href="{{ route('register') }}" class="btn-brand !px-7 !py-3 magnetic">{{ t('hero.start', 'Start creating') }} <x-icon name="ArrowRight" :size="16" /></a>
                    <a href="{{ route('tools') }}" class="btn-ghost !px-7 !py-3">{{ t('hero.explore', 'Explore the tools') }}</a>
                @endif
            </div>
        </div>

        <div class="min-w-0 animate-fade-up tilt" style="animation-delay: 300ms">
            <div class="glass-window p-3">
                <div class="gw-dots flex items-center gap-1.5 px-2 pb-2.5">
                    <span style="background:#ff5f57"></span><span style="background:#febc2e"></span><span style="background:#28c840"></span>
                    <span class="ml-3 text-[11px] text-slate-500 font-body tracking-wide">{{ t('hero.window', 'Live output') }}</span>
                </div>
                @include('partials.showcase-slider')
            </div>
        </div>
    </div>
</section>

{{-- ── Stats band ── --}}
<section class="border-y border-ink-700/60 bg-ink-900/40">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-10 grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach ([
            ['Wand2', ($tools->count() ?: 9).'+', t('stats2.tools', 'Specialised AI tools included'), t('stats2.tools_sub', 'Images, writing, translation, documents, audio & more')],
            ['Globe2', $langCount.'+', t('stats2.languages', 'Interface languages supported'), t('stats2.languages_sub', 'Serve customers worldwide, right-to-left included')],
            ['BadgeCheck', '1', t('stats2.subscription', 'Simple all-inclusive subscription'), t('stats2.subscription_sub', 'Every tool unlocked — no add-ons, no surprises')],
            ['Infinity', '∞', t('stats2.ideas', 'Ideas you can bring to life'), t('stats2.ideas_sub', 'Fresh credits every cycle to keep you creating')],
        ] as [$icon, $value, $label, $sub])
            <div class="card gradient-ring p-5 text-center glow-hover">
                <x-icon :name="$icon" :size="18" class="mx-auto text-brand mb-2" />
                <p class="font-display text-3xl font-bold animate-gradient-text">{{ $value }}</p>
                <p class="text-xs text-white font-medium mt-1.5">{{ $label }}</p>
                <p class="text-[11px] text-slate-500 mt-1 leading-relaxed hidden sm:block">{{ $sub }}</p>
            </div>
        @endforeach
    </div>
</section>

{{-- ── Tool showcase ── --}}
<section class="relative overflow-hidden">
    <div class="absolute inset-0 pointer-events-none">
        <img src="{{ asset('art/toolkit-art.png') }}" alt="" aria-hidden="true" class="w-full h-full object-cover opacity-35 select-none">
        <div class="absolute inset-0 bg-gradient-to-b from-ink-950/85 via-ink-950/70 to-ink-950"></div>
    </div>
    <div class="relative max-w-6xl mx-auto px-4 sm:px-6 py-16 sm:py-20 text-center"
        x-data="toolGate(@js(auth()->check()), @js($hasPlan), @js(array_keys(array_filter([
            'stripe' => (brand('payments')['stripe'] ?? false),
            'paypal' => (brand('payments')['paypal'] ?? false),
        ]))))">
        @include('partials.tool-gate')
        <h2 class="font-display text-2xl md:text-4xl font-bold text-white text-center mb-3">{{ t('toolkit.title', 'The toolkit') }}</h2>
        <p class="text-slate-400 text-center max-w-2xl mx-auto mb-12">{{ t('toolkit.subtitle2', 'Nine purpose-built AI workspaces, each tuned for a specific job. Open any of them to start creating — your credits and history follow you across every tool.') }}</p>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($tools as $tool)
                <button type="button" @click="openTool('{{ $tool->slug }}', @js((bool) $tool->free_enabled))"
                    class="card-glow glow-hover border-anim card-laminate p-6 text-center group block w-full">
                    <span class="icon-tile mb-4"><x-icon :name="$tool->icon" :size="22" /></span>
                    <h3 class="font-display font-semibold text-white">{{ t("tool.{$tool->slug}.name", $tool->name) }}</h3>
                    <p class="text-sm text-slate-400 mt-1 mb-4">{{ t("tool.{$tool->slug}.desc", $tool->description) }}</p>
                    <span class="inline-flex items-center gap-1.5 text-sm text-brand">
                        {{ t('toolkit.try', 'Try it') }}
                        <x-icon name="ArrowRight" :size="14" class="transition group-hover:translate-x-1" />
                    </span>
                </button>
            @endforeach
        </div>
    </div>
</section>

{{-- ── Powered-by logo marquee ── --}}
<section class="relative py-10">
    <p class="text-center text-[11px] uppercase tracking-[0.22em] text-slate-500 mb-6">
        {{ t('logos.title', 'Powered by the world’s leading AI models') }}
    </p>
    <div class="marquee">
        <div class="marquee-track">
            @php
                $logos = [
                    ['openai.png', 'OpenAI'], ['gemini.png', 'Google Gemini'], ['claude.png', 'Anthropic Claude'],
                    ['stability.png', 'Stability AI'], ['deepseek.png', 'DeepSeek'], ['mistral.png', 'Mistral AI'],
                    ['groq.png', 'Groq'], ['elevenlabs.png', 'ElevenLabs'],
                ];
            @endphp
            @foreach (array_merge($logos, $logos) as [$file, $alt])
                <span class="marquee-chip" title="{{ $alt }}">
                    <img src="{{ asset('art/logos/'.$file) }}" alt="{{ $alt }}" loading="lazy" draggable="false">
                </span>
            @endforeach
        </div>
    </div>
</section>

{{-- ── How it works ── --}}
<div class="section-divider max-w-4xl mx-auto"></div>
<section class="relative overflow-hidden">
    <div class="absolute inset-0">
        <img src="{{ asset('art/workflow-art.png') }}" alt="" aria-hidden="true"
            class="w-full h-full object-cover opacity-45 select-none pointer-events-none">
        <div class="absolute inset-0 bg-gradient-to-b from-ink-950/85 via-ink-950/55 to-ink-950"></div>
    </div>
    <div class="relative max-w-6xl mx-auto px-4 sm:px-6 py-16 sm:py-20">
        <div class="aurora aurora-b w-[360px] h-[360px] -top-20 -right-32"></div>
        <h2 class="font-display text-2xl md:text-4xl font-bold text-white text-center mb-3">{{ t('how.title', 'How it works') }}</h2>
        <p class="text-slate-400 text-center max-w-2xl mx-auto mb-14">
            {{ t('how.subtitle', 'From idea to finished result in four simple steps — no design skills, no technical setup, no learning curve.') }}
        </p>

        <div class="relative grid md:grid-cols-4 gap-5">
            <div class="hidden md:block absolute top-9 left-[12%] right-[12%] h-0.5 step-connector rounded-full opacity-40"></div>
            @foreach ([
                ['UserPlus2', '01', t('how.s1.title', 'Create your account'), t('how.s1.text', 'Sign up free in under a minute — with your email or one click through Google. No credit card required to look around.')],
                ['MousePointerClick', '02', t('how.s2.title', 'Pick the right tool'), t('how.s2.text', 'Nine specialised workspaces: images, articles, translation, documents, background removal, voiceovers, chat, rewriting and summaries.')],
                ['WandSparkles', '03', t('how.s3.title', 'Describe what you want'), t('how.s3.text', 'Type a prompt, upload a file, or paste text. Fine-tune with styles, tones, languages, and voices until it feels right.')],
                ['Download', '04', t('how.s4.title', 'Use it anywhere'), t('how.s4.text', 'Download images and audio, export documents to Word or Markdown, or copy the text. Everything you create belongs to you.')],
            ] as $i => [$icon, $n, $stepTitle, $stepText])
                <div class="card glow-hover p-6 relative animate-fade-up text-center" style="animation-delay: {{ $i * 80 }}ms">
                    <div class="flex items-center justify-center gap-4 mb-4">
                        <span class="icon-tile !p-2.5"><x-icon :name="$icon" :size="18" /></span>
                        <span class="font-display text-3xl font-bold text-ink-700 select-none">{{ $n }}</span>
                    </div>
                    <h3 class="font-display font-semibold text-white">{{ $stepTitle }}</h3>
                    <p class="text-[13px] text-slate-400 mt-2 leading-relaxed">{{ $stepText }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid sm:grid-cols-3 gap-4 mt-8">
            @foreach ([
                ['ShieldCheck', t('how.f1', 'Your results are saved automatically — close the tab, come back, everything is still there.')],
                ['History', t('how.f2', 'Full history for every tool, grouped by day, with one-click clear when you want a fresh start.')],
                ['Zap', t('how.f3', 'Transparent credit meters on every tool — always know exactly how much you have left.')],
            ] as [$icon, $factText])
                <div class="glass rounded-2xl p-4 flex items-start gap-3">
                    <x-icon :name="$icon" :size="16" class="text-brand shrink-0 mt-0.5" />
                    <p class="text-xs text-slate-300 leading-relaxed">{{ $factText }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── Gallery ── --}}
<section class="max-w-6xl mx-auto px-4 sm:px-6 pb-16 mt-10">
    <h2 class="font-display text-2xl md:text-4xl font-bold text-white text-center mb-3">{{ t('gallery.title', 'See the tools in action') }}</h2>
    <p class="text-slate-400 text-center max-w-2xl mx-auto mb-10">
        {{ t('gallery.subtitle', 'A glimpse of what each workspace does — from pixel-perfect cutouts to documents that answer back.') }}
    </p>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ([
            ['bg-removal-demo.png', t('gallery.bg', 'Background removal'), t('gallery.bg_sub', 'Clean cutouts in one click')],
            ['chat-assistant-art.png', t('gallery.chat', 'AI chat assistant'), t('gallery.chat_sub', 'Answers, ideas & research 24/7')],
            ['summarizer-art.png', t('gallery.sum', 'Document intelligence'), t('gallery.sum_sub', 'Key points from any document')],
            ['rewriter-art.png', t('gallery.rw', 'Grammar & rewriting'), t('gallery.rw_sub', 'From rough draft to polished')],
        ] as $i => [$img, $galTitle, $galSub])
            <a href="{{ route('tools') }}" class="card glow-hover overflow-hidden group animate-fade-up block" style="animation-delay: {{ $i * 70 }}ms">
                <div class="relative h-40 overflow-hidden">
                    <img src="{{ asset('art/'.$img) }}" alt="{{ $galTitle }}" class="w-full h-full object-cover transition duration-500 group-hover:scale-105">
                    <div class="absolute inset-0 bg-gradient-to-t from-ink-900 via-transparent to-transparent"></div>
                </div>
                <div class="p-4">
                    <p class="font-display font-semibold text-white text-sm">{{ $galTitle }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $galSub }}</p>
                </div>
            </a>
        @endforeach
    </div>
</section>

{{-- ── Testimonials ── --}}
@if ($testimonials->count())
    <section class="relative overflow-hidden">
        <div class="absolute inset-0 pointer-events-none">
            <img src="{{ asset('art/light-abstract.png') }}" alt="" aria-hidden="true" class="w-full h-full object-cover opacity-[0.10] select-none">
            <div class="absolute inset-0 bg-gradient-to-b from-ink-950 via-transparent to-ink-950"></div>
        </div>
        <div class="relative max-w-3xl mx-auto px-4 sm:px-6 py-14">
            <h2 class="font-display text-2xl md:text-4xl font-bold text-white text-center mb-10">{{ t('testimonials.title', 'Loved by creators') }}</h2>
            @include('partials.testimonials')
        </div>
    </section>
@endif

{{-- ── Why us ── --}}
<section class="max-w-6xl mx-auto px-4 sm:px-6 py-8">
    <div class="grid md:grid-cols-3 gap-5">
        @foreach ([
            ['Layers', t('why.one.title', 'One subscription'), t('why.one.text', 'Stop juggling five AI accounts. One plan covers images, words, and audio.')],
            ['Zap', t('why.speed.title', 'Built for speed'), t('why.speed.text', 'Type a prompt, get a result. Credit meters keep your usage transparent.')],
            ['ShieldCheck', t('why.limits.title', 'Fair limits'), t('why.limits.text', 'Credits reset every billing cycle, and you can upgrade or downgrade anytime.')],
        ] as [$icon, $whyTitle, $whyText])
            <div class="card p-6">
                <x-icon :name="$icon" :size="22" class="text-brand mb-3" />
                <h3 class="font-display font-semibold text-white">{{ $whyTitle }}</h3>
                <p class="text-sm text-slate-400 mt-1">{{ $whyText }}</p>
            </div>
        @endforeach
    </div>
</section>

{{-- ── Pricing preview (hidden for subscribed users) ── --}}
@unless ($hasPlan)
    <section class="max-w-6xl mx-auto px-4 sm:px-6 py-16 sm:py-20">
        <h2 class="font-display text-2xl md:text-4xl font-bold text-white text-center mb-3">{{ t('home_pricing.title', 'Simple pricing') }}</h2>
        <p class="text-slate-400 text-center max-w-xl mx-auto mb-10">
            {{ t('home_pricing.subtitle', 'Every plan unlocks every tool. Pick the credit level that fits — upgrade, downgrade, or cancel whenever you like.') }}
        </p>
        @include('partials.pricing-grid', ['buttonLabel' => auth()->check() ? null : t('hero.start', 'Get started')])
    </section>
@endunless

{{-- ── CTA (guests only) ── --}}
@guest
    <section class="max-w-4xl mx-auto px-4 sm:px-6 pb-10 text-center">
        <div class="card p-8 sm:p-12 relative overflow-hidden">
            <img src="{{ asset('art/toolkit-art.png') }}" alt="" aria-hidden="true"
                class="absolute inset-0 w-full h-full object-cover opacity-25 select-none pointer-events-none">
            <div class="absolute inset-0 bg-gradient-to-t from-ink-900 via-ink-900/70 to-ink-900/30"></div>
            <div class="absolute inset-0 opacity-40"
                style="background: radial-gradient(400px 200px at 50% 0%, rgb(var(--brand) / .25), transparent 70%)"></div>
            <div class="relative">
                <h2 class="font-display text-2xl md:text-3xl font-bold text-white">{{ t('cta.title', 'Ready to create?') }}</h2>
                <p class="text-slate-400 mt-3 mb-8">{{ t('cta.subtitle', 'Set up your account in under a minute — browse the studio free, upgrade when you are ready.') }}</p>
                <a href="{{ route('register') }}" class="btn-brand !px-8 !py-3">{{ t('cta.button', 'Create free account') }} <x-icon name="ArrowRight" :size="16" /></a>
            </div>
        </div>
    </section>
@endguest
@endsection
