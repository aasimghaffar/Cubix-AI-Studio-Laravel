{{-- Server-rendered port of PricingGrid.jsx + CycleTabs. Expects $packages.
     The monthly/yearly toggle is Alpine state; both sets render and x-show
     switches between them — no fetch needed. Choose → register (guests) or
     the pricing page (signed-in users), where checkout lives. --}}
@php
    $FEATURE_LABELS = [
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
    $canon = array_keys($FEATURE_LABELS);
    $orderFeatures = function (array $features) use ($canon) {
        uksort($features, function ($a, $b) use ($canon) {
            $ia = array_search($a, $canon); $ib = array_search($b, $canon);
            return (($ia === false ? 99 : $ia) <=> ($ib === false ? 99 : $ib));
        });
        return $features;
    };
    $symbol = brand('currency')['symbol'] ?? '$';
    $fmtPrice = fn ($p) => $symbol.number_format((float) $p);
    $fmtLimit = fn ($v) => (int) $v === -1 ? t('pricing.unlimited', 'Unlimited') : number_format((float) $v);

    $cycles  = $packages->pluck('billing_cycle')->unique();
    $hasBoth = $cycles->contains('monthly') && $cycles->contains('yearly');
    $user    = auth()->user();
    $chooseUrl = $user ? route('pricing') : route('register');
    // $checkout: buttons start a real checkout via the page-level Alpine scope.
    // $withCompare: render the side-by-side comparison under the grid.
    $checkout    = $checkout ?? false;
    $withCompare = $withCompare ?? false;
@endphp

<div x-data="{ cycle: 'monthly' }">
    @if ($hasBoth)
        <div class="flex justify-center mb-10">
            <div class="relative glass rounded-full p-1.5 inline-flex">
                <span aria-hidden="true"
                    class="absolute top-1.5 bottom-1.5 w-[calc(50%-6px)] rounded-full transition-all duration-300 ease-out"
                    :style="`inset-inline-start: ${cycle === 'monthly' ? '6px' : 'calc(50%)'}; background: linear-gradient(90deg, rgb(var(--brand)), rgb(var(--accent)))`"></span>
                <button type="button" @click="cycle = 'monthly'"
                    class="relative z-10 w-32 sm:w-36 py-2.5 rounded-full text-sm font-medium transition-colors duration-300"
                    :class="cycle === 'monthly' ? 'text-ink-950 font-semibold' : 'text-slate-300 hover:text-white'">
                    {{ t('pricing.month_tab', 'Monthly') }}
                </button>
                <button type="button" @click="cycle = 'yearly'"
                    class="relative z-10 w-32 sm:w-36 py-2.5 rounded-full text-sm font-medium transition-colors duration-300"
                    :class="cycle === 'yearly' ? 'text-ink-950 font-semibold' : 'text-slate-300 hover:text-white'">
                    <span class="inline-flex items-center gap-1.5">
                        {{ t('pricing.year_tab', 'Yearly') }}
                        <span class="text-[9px] uppercase tracking-wider font-bold rounded-full px-1.5 py-0.5"
                            :class="cycle === 'yearly' ? 'bg-ink-950/20 text-ink-950' : 'bg-brand/15 text-brand'">
                            {{ t('pricing.save', 'Save') }}
                        </span>
                    </span>
                </button>
            </div>
        </div>
    @endif

    @foreach (['monthly', 'yearly'] as $cyc)
        @php $visible = $hasBoth ? $packages->where('billing_cycle', $cyc)->values() : $packages->values(); @endphp
        <div class="grid md:grid-cols-3 gap-5"
            @if ($hasBoth) x-show="cycle === '{{ $cyc }}'" @if ($cyc === 'yearly') x-cloak @endif @endif>
            @foreach ($visible as $i => $pkg)
                @php
                    $popular = ! $pkg->is_custom && $i === 1 && $visible->count() >= 3;
                    $mine    = (bool) $pkg->is_custom;
                @endphp
                <div class="card card-laminate glow-hover spotlight tilt relative overflow-hidden flex flex-col h-full animate-slide-up {{ $popular || $mine ? 'border-brand/60 gradient-ring border-anim border-anim-on' : 'border-anim' }} p-8 pt-12"
                    style="animation-delay: {{ $i * 90 }}ms">
                    @if ($pkg->discount_percent > 0)
                        <span class="ribbon-off z-20">{{ $pkg->discount_percent }}% off</span>
                    @endif
                    @if ($popular || $mine)
                        <span class="absolute z-10 top-0 inset-x-0 h-8 flex items-center justify-center gap-1.5 text-[11px] font-bold uppercase tracking-[0.18em] text-ink-950"
                            style="background: linear-gradient(90deg, rgb(var(--brand)), rgb(var(--accent)))">
                            @if ($mine)
                                <x-icon name="Sparkles" :size="12" /> {{ t('pricing.custom', 'Your custom plan') }}
                            @else
                                ★ {{ t('pricing.popular', 'Most popular') }}
                            @endif
                        </span>
                    @endif

                    <h3 class="font-display font-semibold text-white text-lg">{{ t("package.{$pkg->id}.name", $pkg->name) }}</h3>
                    <div class="mt-3 mb-6">
                        @if ($pkg->discount_percent > 0)
                            <span class="block text-sm text-slate-500 line-through mb-0.5">
                                {{ $fmtPrice(round($pkg->price / (1 - $pkg->discount_percent / 100))) }}
                            </span>
                        @endif
                        <span class="flex items-baseline gap-1.5 flex-wrap">
                            <span class="font-display text-4xl font-bold text-white">{{ $fmtPrice($pkg->price) }}</span>
                            <span class="text-slate-400 text-sm">
                                / {{ $pkg->billing_cycle === 'yearly' ? t('pricing.year', 'year') : t('pricing.month', 'month') }}
                            </span>
                        </span>
                    </div>

                    <ul class="space-y-2.5 mb-7 flex-1">
                        @foreach ($orderFeatures($pkg->features ?? []) as $key => $value)
                            <li class="flex items-start gap-2.5 text-sm">
                                <x-icon name="Check" :size="15" class="text-brand mt-0.5 shrink-0" />
                                <span>
                                    <strong class="{{ (int) $value === -1 ? 'animate-gradient-text' : 'text-white' }}">{{ $fmtLimit($value) }}</strong>
                                    <span class="text-slate-400">{{ t("feature.{$key}", $FEATURE_LABELS[$key] ?? $key) }}</span>
                                </span>
                            </li>
                        @endforeach
                        @if ($pkg->max_sessions)
                            <li class="flex items-start gap-2.5 text-sm">
                                <x-icon name="Check" :size="15" class="text-brand mt-0.5 shrink-0" />
                                <span>
                                    <strong class="text-white">{{ $pkg->max_sessions }}</strong>
                                    <span class="text-slate-400">{{ t('pricing.sessions', $pkg->max_sessions === 1 ? 'browser login' : 'simultaneous browser logins') }}</span>
                                </span>
                            </li>
                        @endif
                    </ul>

                    @if ($user?->activeSubscription?->package_id === $pkg->id)
                        <button disabled
                            class="w-full mt-auto rounded-xl py-2.5 text-sm font-semibold border-2 border-brand/60 text-brand bg-brand/10 cursor-default inline-flex items-center justify-center gap-2">
                            <x-icon name="Check" :size="15" /> {{ t('pricing.current', 'Current plan') }}
                        </button>
                    @elseif ($checkout && $user)
                        <button type="button" @click="choose({{ $pkg->id }})" :disabled="busyId !== null"
                            class="btn-brand w-full mt-auto disabled:opacity-70">
                            <span x-show="busyId !== {{ $pkg->id }}">{{ $buttonLabel ?? t('pricing.choose', 'Choose plan') }}</span>
                            <span x-show="busyId === {{ $pkg->id }}" x-cloak class="inline-flex items-center gap-2">
                                <x-icon name="Loader2" :size="15" class="animate-spin" /> {{ t('pay.redirecting_btn', 'Redirecting…') }}
                            </span>
                        </button>
                    @else
                        <a href="{{ $chooseUrl }}" class="btn-brand w-full mt-auto text-center">
                            {{ $buttonLabel ?? t('pricing.choose', 'Choose plan') }}
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    @endforeach

    {{-- Side-by-side comparison (port of PricingCompare.jsx) — follows the
         same monthly/yearly toggle. Enabled with $withCompare. --}}
    @if ($withCompare ?? false)
        @foreach (['monthly', 'yearly'] as $cyc)
            @php
                $visible = $hasBoth ? $packages->where('billing_cycle', $cyc)->values() : $packages->values();
                $featureKeys = $visible->flatMap(fn ($p) => array_keys($p->features ?? []))->unique()->values()->all();
                usort($featureKeys, function ($a, $b) use ($canon) {
                    $ia = array_search($a, $canon); $ib = array_search($b, $canon);
                    return (($ia === false ? 99 : $ia) <=> ($ib === false ? 99 : $ib));
                });
            @endphp
            @if ($visible->count() >= 2)
                <div class="mt-16 animate-fade-up"
                    @if ($hasBoth) x-show="cycle === '{{ $cyc }}'" @if ($cyc === 'yearly') x-cloak @endif @endif>
                    <h2 class="font-display text-xl md:text-2xl font-bold text-white text-center mb-8">
                        {{ t('pricing.compare', 'Compare plans side by side') }}
                    </h2>
                    <div class="card overflow-x-auto p-0">
                        <table class="w-full text-sm min-w-[560px]">
                            <thead>
                                <tr class="border-b border-ink-700/60">
                                    <th class="text-left px-5 py-4 text-slate-400 font-normal">{{ t('pricing.feature', 'What you get') }}</th>
                                    @foreach ($visible as $i => $pkg)
                                        <th class="px-5 py-4 text-center {{ $i === 1 && $visible->count() >= 3 ? 'bg-brand/5' : '' }}">
                                            <span class="font-display font-semibold text-white block">{{ t("package.{$pkg->id}.name", $pkg->name) }}</span>
                                            <span class="text-brand font-bold">{{ $fmtPrice($pkg->price) }}</span>
                                            <span class="text-xs text-slate-500"> /{{ $pkg->billing_cycle === 'yearly' ? t('pricing.year', 'year') : t('pricing.month', 'month') }}</span>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($featureKeys as $r => $key)
                                    <tr class="{{ $r % 2 ? '' : 'bg-ink-800/25' }}">
                                        <td class="px-5 py-3 text-slate-300">{{ t("feature.{$key}", $FEATURE_LABELS[$key] ?? $key) }}</td>
                                        @foreach ($visible as $i => $pkg)
                                            @php $v = ($pkg->features ?? [])[$key] ?? null; @endphp
                                            <td class="px-5 py-3 text-center {{ $i === 1 && $visible->count() >= 3 ? 'bg-brand/5' : '' }}">
                                                @if ($v === null || (int) $v === 0)
                                                    <x-icon name="Minus" :size="14" class="inline text-slate-600" />
                                                @else
                                                    <span class="{{ (int) $v === -1 ? 'font-semibold animate-gradient-text' : 'text-white font-medium' }}">{{ $fmtLimit($v) }}</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                                <tr class="border-t border-ink-700/60">
                                    <td class="px-5 py-3 text-slate-300">{{ t('pricing.sessions_row', 'Simultaneous browser logins') }}</td>
                                    @foreach ($visible as $i => $pkg)
                                        <td class="px-5 py-3 text-center {{ $i === 1 && $visible->count() >= 3 ? 'bg-brand/5' : '' }}">
                                            <span class="text-white font-medium">{{ $pkg->max_sessions ?? t('pricing.unlimited', 'Unlimited') }}</span>
                                        </td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td class="px-5 py-3 text-slate-300">{{ t('pricing.all_tools', 'Access to every AI tool') }}</td>
                                    @foreach ($visible as $i => $pkg)
                                        <td class="px-5 py-3 text-center {{ $i === 1 && $visible->count() >= 3 ? 'bg-brand/5' : '' }}">
                                            <x-icon name="Check" :size="15" class="inline text-brand" />
                                        </td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endforeach
    @endif
</div>
