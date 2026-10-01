{{-- Tool access gate — port of useToolGate + ToolAccessModal.
     Include ONCE on any page with tool cards, inside an element that has
     x-data="toolGate(...)". Cards call openTool(slug, isFree).
     Guests get the sign-in popup; signed-in users without a plan get the
     plans popup with live checkout; everyone else goes straight in. --}}
@php
    $GATE_FEATURE_LABELS = [
        'image_generation_credits' => 'AI image generations',
        'content_writer_credits'   => 'Written articles',
        'translation_credits'      => 'Translations',
        'document_query_credits'   => 'Document queries',
        'background_removal_credits' => 'Background removals',
        'audio_character_limit'    => 'Audio characters',
        'chat_credits'             => 'Chat questions',
        'rewriter_credits'         => 'Text rewrites',
        'summarizer_credits'       => 'Summaries',
    ];
    $gateCanon = array_keys($GATE_FEATURE_LABELS);
    $gateOrder = function (array $features) use ($gateCanon) {
        uksort($features, fn ($a, $b) => ((array_search($a, $gateCanon) === false ? 99 : array_search($a, $gateCanon))
            <=> (array_search($b, $gateCanon) === false ? 99 : array_search($b, $gateCanon))));
        return $features;
    };
    $gateSymbol = brand('currency')['symbol'] ?? '$';
    $gateFmtPrice = fn ($p) => $gateSymbol.number_format((float) $p);
    $gateFmtLimit = fn ($v) => (int) $v === -1 ? t('pricing.unlimited', 'Unlimited') : number_format((float) $v);
    $gateCycles = $packages->pluck('billing_cycle')->unique();
    $gateHasBoth = $gateCycles->contains('monthly') && $gateCycles->contains('yearly');
    $gatePayments = brand('payments') ?? [];
@endphp

<template x-teleport="body">
<div x-show="gate" x-cloak @keydown.escape.window="gate = null"
    class="fixed inset-0 z-50 grid place-items-center p-4 bg-ink-950/60 backdrop-blur-md" @click="gate = null">
    <div class="relative card p-8 w-full shadow-2xl border-brand/30 animate-pop-in max-h-[90vh] overflow-y-auto"
        :style="`max-width: ${gate === 'plans' ? '58rem' : '24rem'}`" @click.stop>
        <button type="button" @click="gate = null" aria-label="Close"
            class="absolute z-20 top-4 right-4 p-1.5 rounded-lg text-slate-300 bg-ink-950/30 hover:text-white hover:bg-ink-800 transition">
            <x-icon name="X" :size="18" />
        </button>

        {{-- Sign-in prompt (guests) --}}
        <div x-show="gate === 'login'" class="text-center">
            <span class="icon-tile mb-4"><x-icon name="Lock" :size="24" /></span>
            <h2 class="font-display font-semibold text-white text-lg">{{ t('gate.login.title', 'Sign in to use this tool') }}</h2>
            <p class="text-sm text-slate-400 mt-2 mb-6">
                {{ t('gate.login.text', 'Create a free account or sign in — you will be brought right back here.') }}
            </p>
            <div class="space-y-3">
                <a href="{{ route('login') }}" class="btn-brand w-full">{{ t('nav.signin', 'Sign in') }}</a>
                <a href="{{ route('register') }}" class="btn-ghost w-full">{{ t('gate.login.register', 'Create account') }}</a>
            </div>
        </div>

        {{-- Plans prompt (signed in, no plan) --}}
        <div x-show="gate === 'plans'" x-cloak>
            <div class="relative -m-7 mb-6 px-7 pt-8 pb-6 rounded-t-2xl overflow-hidden text-center">
                <div class="absolute inset-0" style="background: linear-gradient(135deg, rgb(var(--brand) / 0.25), rgb(var(--accent) / 0.2))"></div>
                <div class="absolute inset-0 bg-grid-pattern opacity-60"></div>
                <div class="relative">
                    <span class="icon-tile mb-3 animate-pulse-glow"><x-icon name="CreditCard" :size="22" /></span>
                    <h2 class="font-display font-bold text-white text-xl">{{ t('gate.plans.title', 'Choose a plan to unlock the tools') }}</h2>
                    <p class="text-sm text-slate-300 mt-1.5 max-w-md mx-auto">{{ t('gate.plans.text', 'Every plan includes every tool — pick the credit level that fits.') }}</p>
                </div>
            </div>

            @if ($gateHasBoth)
                <div class="flex justify-center mb-5">
                    <div class="relative glass rounded-full p-1 inline-flex">
                        <span aria-hidden="true"
                            class="absolute top-1 bottom-1 w-[calc(50%-4px)] rounded-full transition-all duration-300 ease-out"
                            :style="`left: ${gateCycle === 'monthly' ? '4px' : '50%'}; background: linear-gradient(90deg, rgb(var(--brand)), rgb(var(--accent)))`"></span>
                        <button type="button" @click="gateCycle = 'monthly'"
                            class="relative z-10 w-24 py-1.5 rounded-full text-xs font-medium capitalize transition-colors"
                            :class="gateCycle === 'monthly' ? 'text-ink-950 font-semibold' : 'text-slate-300'">{{ t('pricing.month_tab', 'Monthly') }}</button>
                        <button type="button" @click="gateCycle = 'yearly'"
                            class="relative z-10 w-24 py-1.5 rounded-full text-xs font-medium capitalize transition-colors"
                            :class="gateCycle === 'yearly' ? 'text-ink-950 font-semibold' : 'text-slate-300'">{{ t('pricing.year_tab', 'Yearly') }}</button>
                    </div>
                </div>
            @endif

            @foreach (['monthly', 'yearly'] as $gcyc)
                @php $gvisible = $gateHasBoth ? $packages->where('billing_cycle', $gcyc)->values() : $packages->values(); @endphp
                <div class="grid sm:grid-cols-3 gap-4"
                    @if ($gateHasBoth) x-show="gateCycle === '{{ $gcyc }}'" @if ($gcyc === 'yearly') x-cloak @endif @endif>
                    @foreach ($gvisible as $gi => $gpkg)
                        @php
                            $gpopular = ! $gpkg->is_custom && $gi === 1 && $gvisible->count() >= 3;
                            $gmine = (bool) $gpkg->is_custom;
                        @endphp
                        <div class="card card-laminate spotlight relative overflow-hidden flex flex-col p-6 pt-10 glow-hover animate-slide-up {{ $gpopular || $gmine ? 'border-brand/60 gradient-ring' : '' }}"
                            style="animation-delay: {{ $gi * 70 }}ms">
                            @if ($gpkg->discount_percent > 0)
                                <span class="ribbon-off z-20">{{ $gpkg->discount_percent }}% off</span>
                            @endif
                            @if ($gpopular || $gmine)
                                <span class="absolute z-10 top-0 inset-x-0 h-7 flex items-center justify-center gap-1.5 text-[10px] font-bold uppercase tracking-[0.18em] text-ink-950"
                                    style="background: linear-gradient(90deg, rgb(var(--brand)), rgb(var(--accent)))">
                                    {{ $gmine ? t('pricing.custom', 'Your custom plan') : '★ '.t('pricing.popular', 'Most popular') }}
                                </span>
                            @endif
                            <h3 class="font-display font-semibold text-white">{{ t("package.{$gpkg->id}.name", $gpkg->name) }}</h3>
                            <p class="mt-2 mb-4">
                                @if ($gpkg->discount_percent > 0)
                                    <span class="text-xs text-slate-500 line-through block">{{ $gateFmtPrice(round($gpkg->price / (1 - $gpkg->discount_percent / 100))) }}</span>
                                @endif
                                <span class="font-display text-3xl font-bold text-white">{{ $gateFmtPrice($gpkg->price) }}</span>
                                <span class="text-slate-400 text-sm"> / {{ $gpkg->billing_cycle === 'yearly' ? t('pricing.year', 'year') : t('pricing.month', 'month') }}</span>
                            </p>
                            <ul class="space-y-2 mb-5 flex-1">
                                @foreach ($gateOrder($gpkg->features ?? []) as $gk => $gv)
                                    <li class="flex items-start gap-2 text-[13px]">
                                        <x-icon name="Check" :size="13" class="text-brand mt-0.5 shrink-0" />
                                        <span>
                                            <strong class="{{ (int) $gv === -1 ? 'animate-gradient-text' : 'text-white' }}">{{ $gateFmtLimit($gv) }}</strong>
                                            <span class="text-slate-400">{{ t("feature.{$gk}", $GATE_FEATURE_LABELS[$gk] ?? $gk) }}</span>
                                        </span>
                                    </li>
                                @endforeach
                                @if ($gpkg->max_sessions)
                                    <li class="flex items-start gap-2 text-[13px]">
                                        <x-icon name="Check" :size="13" class="text-brand mt-0.5 shrink-0" />
                                        <span><strong class="text-white">{{ $gpkg->max_sessions }}</strong> <span class="text-slate-400">{{ t('pricing.sessions', $gpkg->max_sessions === 1 ? 'browser login' : 'simultaneous browser logins') }}</span></span>
                                    </li>
                                @endif
                            </ul>
                            <button type="button" @click="gateCheckout({{ $gpkg->id }})" :disabled="gateBusy !== null"
                                class="btn-brand w-full mt-auto disabled:opacity-70">
                                <span x-show="gateBusy !== {{ $gpkg->id }}">{{ t('pricing.choose', 'Choose plan') }}</span>
                                <span x-show="gateBusy === {{ $gpkg->id }}" x-cloak class="inline-flex items-center justify-center gap-2">
                                    <x-icon name="Loader2" :size="14" class="animate-spin" /> {{ t('pay.redirecting_btn', 'Redirecting…') }}
                                </span>
                            </button>
                        </div>
                    @endforeach
                </div>
            @endforeach

            <p class="text-sm text-red-400 text-center mt-4" x-show="gateError" x-text="gateError" x-cloak></p>
            <p class="text-center text-xs text-slate-500 mt-4">{{ t('gate.plans.note', 'Cancel anytime. Credits refresh every billing cycle.') }}</p>

            @if (($gatePayments['stripe'] ?? false) || ($gatePayments['paypal'] ?? false))
                <div class="flex items-center justify-center gap-3 mt-3">
                    <span class="text-[11px] uppercase tracking-widest text-slate-500">{{ t('pay.supported', 'Payment methods supported') }}</span>
                    @if ($gatePayments['stripe'] ?? false)
                        @include('partials.pay-logo', ['brand' => 'stripe'])
                    @endif
                    @if ($gatePayments['paypal'] ?? false)
                        @include('partials.pay-logo', ['brand' => 'paypal'])
                    @endif
                    <x-icon name="Lock" :size="12" class="text-brand" />
                </div>
            @endif

            <p class="text-center text-xs mt-4">
                <a href="{{ route('pricing') }}" class="text-brand hover:underline">{{ t('gate.plans.compare', 'Compare all plans in detail →') }}</a>
            </p>
        </div>
    </div>
</div>
</template>

{{-- Gateway chooser for checkout started inside the gate --}}
<template x-teleport="body">
<div x-show="gateChoosing !== null" x-cloak
    class="fixed inset-0 z-50 grid place-items-center p-4 bg-ink-950/60 backdrop-blur-md" @click="gateChoosing = null">
    <div class="relative card p-7 w-full max-w-sm animate-pop-in" @click.stop>
        <button type="button" aria-label="Close" @click="gateChoosing = null"
            class="absolute top-4 right-4 p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-ink-800 transition">
            <x-icon name="X" :size="18" />
        </button>
        <h2 class="font-display font-semibold text-white mb-1">{{ t('pay.choose_title', 'How would you like to pay?') }}</h2>
        <p class="text-xs text-slate-500 mb-5">{{ t('pay.choose_sub', 'Both options are secure — pick whichever you prefer.') }}</p>
        <div class="space-y-3">
            <button type="button" @click="gateGo('stripe')" :disabled="gateBusy !== null"
                class="w-full flex items-center gap-3 rounded-xl border border-ink-700 hover:border-brand/60 bg-ink-800/50 px-4 py-3 text-sm text-slate-200 transition disabled:opacity-60">
                @include('partials.pay-logo', ['brand' => 'stripe', 'size' => 'sm'])
                {{ t('pay.card', 'Pay by card (Stripe)') }}
            </button>
            <button type="button" @click="gateGo('paypal')" :disabled="gateBusy !== null"
                class="w-full flex items-center gap-3 rounded-xl border border-ink-700 hover:border-brand/60 bg-ink-800/50 px-4 py-3 text-sm text-slate-200 transition disabled:opacity-60">
                @include('partials.pay-logo', ['brand' => 'paypal', 'size' => 'sm'])
                {{ t('pay.paypal', 'Pay with PayPal') }}
            </button>
        </div>
    </div>
</div>
</template>
