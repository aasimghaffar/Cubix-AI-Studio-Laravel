@extends('layouts.app')

@section('content')
@php
    $isAuthed = auth()->check();
    $hasPlan = $isAuthed && (bool) auth()->user()->activeSubscription;
    $art = [
        'ai-image-generator' => asset('art/ai-workspace.png'),
        'ai-background-removal' => asset('art/bg-removal-demo.png'),
        'ai-chat-assistant' => asset('art/chat-assistant-art.png'),
        'ai-text-to-audio' => asset('art/text-to-audio-art.png'),
        'ai-text-rewriter' => asset('art/rewriter-art.png'),
        'ai-summarizer' => asset('art/summarizer-art.png'),
    ];
    $backdrop = $art[$tool->slug] ?? asset('art/tools-hero.png');
    $gateways = array_keys(array_filter([
        'stripe' => (brand('payments')['stripe'] ?? false),
        'paypal' => (brand('payments')['paypal'] ?? false),
    ]));
@endphp

<div class="relative overflow-hidden min-h-screen"
    x-data="toolWorkspace(@js($tool->slug), @js($isAuthed), @js($hasPlan), @js((bool) $tool->free_enabled), @js($gateways))"
    x-init="init()">

    {{-- Tool artwork as the whole workspace backdrop --}}
    <div class="absolute inset-0 pointer-events-none">
        <img src="{{ $backdrop }}" alt="" aria-hidden="true" class="w-full h-full object-cover opacity-[0.14] select-none">
        <div class="absolute inset-0 bg-gradient-to-b from-ink-950/70 via-ink-950/88 to-ink-950"></div>
        <div class="aurora aurora-a w-[380px] h-[380px] -top-24 -left-24 animate-float-slow"></div>
        <div class="aurora aurora-b w-[320px] h-[320px] bottom-0 -right-24 animate-float"></div>
    </div>

    {{-- Gated preview — blurred workspace behind the sign-in / plans popup --}}
    <template x-if="!authed || (!hasPlan && !isFree)">
        <div class="relative max-w-6xl mx-auto px-6 py-16 min-h-[70vh]">
            <div class="blur-sm pointer-events-none select-none opacity-50" aria-hidden="true">
                <div class="flex items-center gap-3 mb-8">
                    <span class="icon-tile"><x-icon :name="$tool->icon" :size="22" /></span>
                    <h1 class="font-display text-2xl font-bold text-white">{{ $tool->name }}</h1>
                </div>
                <div class="grid lg:grid-cols-[380px_1fr] gap-8">
                    <div class="card p-6 space-y-4">
                        <div class="h-24 rounded-xl bg-ink-800"></div>
                        <div class="h-10 rounded-xl bg-ink-800"></div>
                        <div class="h-10 rounded-xl bg-ink-800"></div>
                        <div class="h-11 rounded-xl bg-brand/40"></div>
                    </div>
                    <div class="card p-10"></div>
                </div>
            </div>
            <div x-init="gate = !authed ? 'login' : 'plans'"></div>
            @include('partials.tool-gate')
        </div>
    </template>

    {{-- Real workspace --}}
    <template x-if="authed && (hasPlan || isFree)">
        <div class="relative max-w-6xl mx-auto px-6 py-12">
            <a href="{{ route('tools') }}" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-brand mb-8 group">
                <x-icon name="ArrowLeft" :size="16" class="transition group-hover:-translate-x-1" /> {{ t('ws.back', 'All tools') }}
            </a>

            <div class="grid lg:grid-cols-[400px_1fr] gap-8 items-start">
                {{-- Form panel --}}
                <div class="card gradient-ring glass p-6 animate-slide-up lg:sticky lg:top-6">
                    <div class="flex items-center gap-3 mb-1.5">
                        <span class="icon-tile !p-2.5 animate-pulse-glow"><x-icon :name="$tool->icon" :size="20" /></span>
                        <div>
                            <h1 class="font-display text-xl font-bold text-white leading-tight">{{ t("tool.{$tool->slug}.name", $tool->name) }}</h1>
                            @if ($tool->taxonomy)
                                <span class="text-[10px] uppercase tracking-widest text-brand">{{ t("taxonomy.{$tool->taxonomy->slug}", $tool->taxonomy->name) }}</span>
                            @endif
                        </div>
                    </div>
                    <p class="text-sm text-slate-400 mb-5">{{ t("tool.{$tool->slug}.desc", $tool->description) }}</p>

                    {{-- Credit meter --}}
                    <template x-if="meter">
                        <div class="mb-6">
                            <p class="text-xs text-slate-400 mb-1" x-text="meterLabel()"></p>
                            <template x-if="meter && Number(meter.limit) === -1">
                                <p class="text-xs font-semibold animate-gradient-text inline-block">∞ Unlimited</p>
                            </template>
                            <template x-if="meter && (meter.limit === null || meter.limit === undefined)">
                                <p class="text-xs text-brand" x-text="freeLine()"></p>
                            </template>
                            <template x-if="meter && meter.limit !== null && meter.limit !== undefined && Number(meter.limit) !== -1">
                                <div>
                                    <div class="h-2 rounded-full bg-ink-700 overflow-hidden">
                                        <div class="h-full rounded-full transition-all" :class="meterPct() >= 90 ? 'bg-red-500' : 'bg-brand'" :style="`width: ${meterPct()}%`"></div>
                                    </div>
                                    <p class="text-xs text-slate-500 mt-1" x-text="`${Number(meter?.used ?? 0).toLocaleString()} / ${Number(meter?.limit ?? 0).toLocaleString()} used`"></p>
                                </div>
                            </template>
                            <p class="text-[11px] text-slate-500 mt-1" x-show="meter?.free"
                                x-text="meter?.renews === 'day' ? @js(t('ws.free_renews_day', 'Free credits renew every day at midnight.')) : @js(t('ws.free_renews_month', 'Free credits renew on the 1st of every month.'))"></p>
                        </div>
                    </template>

                    {{-- Dynamic form (port of DynamicForm.jsx) --}}
                    <div class="space-y-5">
                        <template x-for="f in (schema?.fields ?? [])" :key="f.name">
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1.5">
                                    <span x-text="f.label"></span> <span class="text-brand" x-show="f.required">*</span>
                                </label>

                                <template x-if="f.type === 'textarea'">
                                    <textarea class="input min-h-28" :placeholder="f.placeholder" :maxlength="f.max_length"
                                        :value="values[f.name] ?? ''" @input="values[f.name] = $event.target.value"></textarea>
                                </template>
                                <template x-if="f.type === 'select'">
                                    <select class="input" @change="values[f.name] = $event.target.value">
                                        <option value="" disabled>Choose…</option>
                                        <template x-for="o in (f.options ?? [])" :key="o.value">
                                            <option :value="o.value" :selected="(values[f.name] ?? '') === o.value" x-text="o.label"></option>
                                        </template>
                                    </select>
                                </template>
                                <template x-if="f.type === 'file'">
                                    <label class="flex items-center gap-3 input cursor-pointer hover:border-brand/60">
                                        <x-icon name="UploadCloud" :size="18" class="text-brand shrink-0" />
                                        <span class="text-slate-400 text-sm truncate" x-text="values[f.name]?.name || `Upload ${(f.accept_extensions ?? []).join(', ').toUpperCase() || 'FILE'}`"></span>
                                        <input type="file" class="hidden" :accept="(f.accept_extensions ?? []).map((e) => '.' + e).join(',')"
                                            @change="values[f.name] = $event.target.files?.[0]">
                                    </label>
                                </template>
                                <template x-if="f.type === 'range'">
                                    <div class="flex items-center gap-4">
                                        <input type="range" :min="f.min" :max="f.max" :step="f.step ?? 0.1"
                                            :value="values[f.name] ?? f.default ?? f.min"
                                            @input="values[f.name] = parseFloat($event.target.value)"
                                            class="w-full accent-[rgb(var(--brand))]">
                                        <span class="text-sm text-slate-300 w-10 text-right" x-text="values[f.name] ?? f.default"></span>
                                    </div>
                                </template>
                                <template x-if="!f.type || (f.type !== 'textarea' && f.type !== 'select' && f.type !== 'file' && f.type !== 'range')">
                                    <input class="input" :placeholder="f.placeholder" :maxlength="f.max_length"
                                        :value="values[f.name] ?? ''" @input="values[f.name] = $event.target.value">
                                </template>

                                <p class="text-xs text-red-400 mt-1" x-show="fieldErrors[f.name]" x-text="fieldErrors[f.name]" x-cloak></p>
                            </div>
                        </template>

                        <p class="text-xs text-slate-500" x-show="schema?.credit_note" x-text="schema?.credit_note"></p>

                        <button type="button" @click="submit()" :disabled="busy" class="btn-brand w-full">
                            <span x-show="busy" class="inline-block animate-spin"><x-icon name="Loader2" :size="16" /></span>
                            <span x-text="busy ? 'Working…' : (schema?.submit_label || 'Run')"></span>
                        </button>
                    </div>
                </div>

                {{-- Results panel --}}
                <div class="space-y-4">
                    <div class="card p-5 space-y-3" x-show="busy" x-cloak>
                        <p class="text-sm text-slate-400">Generating…</p>
                        <div class="shimmer h-4 w-3/4"></div>
                        <div class="shimmer h-4 w-1/2"></div>
                        <div class="shimmer h-32"></div>
                    </div>

                    <div class="card overflow-hidden text-center animate-fade-up" x-show="!busy && historyLoaded && outputs.length === 0" x-cloak>
                        @if (isset($art[$tool->slug]))
                            <div class="relative h-44 sm:h-56">
                                <img src="{{ $art[$tool->slug] }}" alt="" aria-hidden="true" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-gradient-to-t from-ink-900 via-ink-900/30 to-transparent"></div>
                            </div>
                        @endif
                        <div class="{{ isset($art[$tool->slug]) ? 'p-8 pt-4' : 'p-14' }}">
                            @unless (isset($art[$tool->slug]))
                                <span class="icon-tile mb-4 opacity-60"><x-icon :name="$tool->icon" :size="24" /></span>
                            @endunless
                            <p class="text-slate-400 text-sm">Your results will appear here.</p>
                            <p class="text-slate-600 text-xs mt-1">Fill in the form and hit the button to create your first one.</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between" x-show="outputs.length > 0">
                        <p class="text-xs text-slate-500" x-text="`${outputs.length} saved result${outputs.length > 1 ? 's' : ''}`"></p>
                        <button type="button" @click="clearAll()" class="inline-flex items-center gap-1.5 text-xs text-slate-400 hover:text-red-400 transition">
                            <x-icon name="Trash2" :size="13" /> Clear all history
                        </button>
                    </div>

                    {{-- Image gallery grid --}}
                    <div class="card p-4 animate-fade-up" x-show="outputs.length > 0 && outputs[0]?.type === 'image'" x-cloak>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <template x-for="(url, i) in galleryUrls()" :key="i">
                                <div class="group relative aspect-square rounded-xl overflow-hidden bg-ink-800 cursor-pointer" @click="lightbox = url">
                                    <img :src="url" :alt="`Generated ${i + 1}`" loading="lazy"
                                        class="w-full h-full object-cover transition duration-300 group-hover:scale-105 group-hover:brightness-50">
                                    <div class="absolute inset-0 grid place-items-center opacity-0 group-hover:opacity-100 transition">
                                        <div class="flex items-center gap-2">
                                            <span class="p-2.5 rounded-full bg-ink-950/70 text-white backdrop-blur border border-white/15" title="View large">
                                                <x-icon name="Eye" :size="17" />
                                            </span>
                                            <a :href="url" download @click.stop class="p-2.5 rounded-full bg-ink-950/70 text-white backdrop-blur border border-white/15 hover:text-brand" title="Download">
                                                <x-icon name="Download" :size="17" />
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <p class="text-xs text-slate-500 mt-3 inline-flex items-center gap-1.5">
                            <x-icon name="History" :size="12" /> Your previous generations stay here — hover an image to view or download it.
                        </p>
                    </div>

                    {{-- Day-grouped text/audio results --}}
                    <div class="space-y-4" x-show="outputs.length > 0 && outputs[0]?.type !== 'image'" x-cloak>
                        <template x-for="(g, gi) in dayGroups()" :key="g.day">
                            <div>
                                <button type="button" @click="dayOpen[g.day] = !(dayOpen[g.day] ?? gi === 0)"
                                    class="w-full flex items-center justify-between text-left px-1 py-1.5 mb-2">
                                    <span class="text-xs uppercase tracking-widest text-slate-500 inline-flex items-center gap-2">
                                        <x-icon name="History" :size="12" /> <span x-text="`${fmtDay(g.day)} · ${g.items.length}`"></span>
                                    </span>
                                    <span :class="(dayOpen[g.day] ?? gi === 0) ? 'rotate-180' : ''" class="text-slate-500 transition">
                                        <x-icon name="ChevronDown" :size="14" />
                                    </span>
                                </button>
                                <div class="space-y-4" x-show="dayOpen[g.day] ?? gi === 0">
                                    <template x-for="(out, oi) in g.items" :key="gi + '-' + oi">
                                        <div>
                                            {{-- Audio output --}}
                                            <div class="card p-5 animate-fade-up" x-show="out.type === 'audio'">
                                                <audio controls :src="out.url" class="w-full"></audio>
                                                <a :href="out.url" download class="inline-flex items-center gap-2 text-sm text-brand mt-3">
                                                    <x-icon name="Download" :size="15" /> Download MP3
                                                </a>
                                            </div>
                                            {{-- Text output --}}
                                            <div class="card p-5 animate-fade-up" :class="(gi === 0 && oi === 0) ? '' : 'opacity-90'" x-show="out.type !== 'audio'">
                                                <p class="text-[11px] text-slate-500 mb-2 inline-flex items-center gap-1" x-show="out._at && !(gi === 0 && oi === 0)">
                                                    <x-icon name="History" :size="11" /> <span x-text="out._at ? new Date(out._at).toLocaleString() : ''"></span>
                                                </p>
                                                <div class="mb-4 rounded-xl bg-ink-800/70 border border-ink-700/60 px-4 py-2.5"
                                                    x-show="'{{ $tool->slug }}' === 'ai-document-assistant' && (out._input?.prompt || out._input?.message || out._input?.topic)">
                                                    <p class="text-[11px] uppercase tracking-widest text-slate-500 mb-0.5">You asked</p>
                                                    <p class="text-sm text-slate-300" x-text="String(out._input?.prompt || out._input?.message || out._input?.topic || '')"></p>
                                                </div>
                                                <div class="relative">
                                                    <p class="text-sm leading-relaxed whitespace-pre-wrap" x-text="shownText(out)"></p>
                                                    <div class="absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-ink-900 to-transparent pointer-events-none" x-show="!out._expanded && answerText(out).length > 420"></div>
                                                </div>
                                                <button type="button" x-show="answerText(out).length > 420" @click="out._expanded = !out._expanded"
                                                    class="inline-flex items-center gap-1 text-xs text-brand mt-2">
                                                    <span :class="out._expanded ? 'rotate-180' : ''" class="transition"><x-icon name="ChevronDown" :size="13" /></span>
                                                    <span x-text="out._expanded ? 'See less' : 'See more'"></span>
                                                </button>
                                                <div class="flex items-center gap-5 mt-4">
                                                    <button type="button" @click="copyOut(out)" class="inline-flex items-center gap-2 text-sm text-brand">
                                                        <x-icon name="Copy" :size="14" /> <span x-text="out._copied ? @js(t('ws.copied', 'Copied!')) : @js(t('ws.copy', 'Copy text'))"></span>
                                                    </button>
                                                    <div class="relative" x-show="'{{ $tool->slug }}' === 'ai-document-assistant'" x-data="{ dlOpen: false }">
                                                        <button type="button" @click="dlOpen = !dlOpen" class="inline-flex items-center gap-2 text-sm text-brand">
                                                            <x-icon name="FileDown" :size="14" /> {{ t('ws.download', 'Download') }}
                                                        </button>
                                                        <div x-show="dlOpen" x-cloak @click.outside="dlOpen = false" class="absolute bottom-full mb-2 left-0 card p-1.5 w-40 shadow-xl animate-pop-in z-20">
                                                            <button type="button" @click="downloadText(answerText(out), 'txt'); dlOpen = false" class="block w-full text-left px-3 py-2 rounded-lg text-xs text-slate-300 hover:bg-ink-800">Text file (.txt)</button>
                                                            <button type="button" @click="downloadText(answerText(out), 'doc'); dlOpen = false" class="block w-full text-left px-3 py-2 rounded-lg text-xs text-slate-300 hover:bg-ink-800">Word (.doc)</button>
                                                            <button type="button" @click="downloadText(answerText(out), 'md'); dlOpen = false" class="block w-full text-left px-3 py-2 rounded-lg text-xs text-slate-300 hover:bg-ink-800">Markdown (.md)</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Lightbox --}}
            <template x-teleport="body">
            <div x-show="lightbox" x-cloak @keydown.escape.window="lightbox = null"
                class="fixed inset-0 z-50 grid place-items-center p-4 sm:p-8 bg-ink-950/85 backdrop-blur-md animate-pop-in" @click="lightbox = null">
                <div class="relative max-w-4xl w-full" @click.stop>
                    <div class="absolute -top-12 right-0 flex items-center gap-2">
                        <a :href="lightbox" download class="p-2.5 rounded-full bg-ink-800 border border-ink-700 text-slate-200 hover:text-brand transition" title="Download">
                            <x-icon name="Download" :size="18" />
                        </a>
                        <button type="button" @click="lightbox = null" aria-label="Close" class="p-2.5 rounded-full bg-ink-800 border border-ink-700 text-slate-200 hover:text-white transition">
                            <x-icon name="X" :size="18" />
                        </button>
                    </div>
                    <img :src="lightbox" alt="Generated result — full size" class="w-full max-h-[80vh] object-contain rounded-2xl border border-ink-700/60">
                </div>
            </div>
            </template>

            {{-- Error modal --}}
            <template x-teleport="body">
            <div x-show="error" x-cloak @keydown.escape.window="error = ''"
                class="fixed inset-0 z-50 grid place-items-center p-4 bg-ink-950/60 backdrop-blur-md" @click="error = ''">
                <div class="relative card p-7 w-full max-w-sm text-center animate-pop-in border-red-400/30" @click.stop>
                    <button type="button" @click="error = ''" aria-label="Close" class="absolute top-4 right-4 p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-ink-800 transition">
                        <x-icon name="X" :size="18" />
                    </button>
                    <span class="inline-flex p-3 rounded-2xl bg-red-500/15 text-red-400 mb-4"><x-icon name="TriangleAlert" :size="24" /></span>
                    <h2 class="font-display font-semibold text-white mb-2" x-text="isLimitError ? 'Limit reached' : 'Generation failed'"></h2>
                    <p class="text-sm text-slate-400 mb-6" x-text="error"></p>
                    <div class="space-y-3" x-show="isLimitError">
                        <a href="{{ route('pricing') }}" class="btn-brand w-full block text-center">See plans</a>
                        <button type="button" @click="error = ''" class="btn-ghost w-full">Not now</button>
                    </div>
                    <button type="button" x-show="!isLimitError" @click="error = ''" class="btn-brand w-full">Got it</button>
                </div>
            </div>
            </template>
        </div>
    </template>
</div>

@push('scripts')
<script>
function toolWorkspace(slug, authed, hasPlan, isFree, gateways) {
    const H = { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content };
    return Object.assign({
        slug, authed, hasPlan, isFree,
        tool: null, schema: null, meter: null, meterKey: null,
        values: {}, fieldErrors: {}, busy: false,
        outputs: [], historyLoaded: false, dayOpen: {},
        lightbox: null, error: '', isLimitError: false,

        async init() {
            if (!this.authed || (!this.hasPlan && !this.isFree)) return; // gated preview only
            await this.load();
            try {
                const res = await fetch(`/api/tools/${slug}/history`, { headers: H });
                if (res.ok) {
                    const rows = await res.json();
                    this.outputs = rows.map((r) => Object.assign({}, r.output, { _input: r.input, _at: r.created_at }));
                }
            } catch (e) { /* ignore */ }
            this.historyLoaded = true;
        },

        async load() {
            const res = await fetch('/api/tools', { headers: H });
            if (!res.ok) return;
            const d = await res.json();
            const t = d.tools.find((x) => x.slug === slug);
            if (!t || t.status !== 'active') { window.location.href = '/tools'; return; }
            this.tool = t;
            this.schema = t.input_schema;
            this.meterKey = t.feature_key;
            this.meter = d.meters[t.feature_key];
            this.applyDefaults();
        },
        applyDefaults() {
            const defaults = {};
            for (const f of (this.schema?.fields ?? [])) {
                if (f.default !== undefined) defaults[f.name] = f.default;
                else if (f.type === 'select' && f.options?.length) defaults[f.name] = f.options[0].value;
            }
            this.values = defaults;
            this.fieldErrors = {};
        },

        meterLabel() {
            const LABELS = {
                image_generation_credits: 'AI images', content_writer_credits: 'Written articles',
                translation_credits: 'Translations', document_query_credits: 'Document queries',
                background_removal_credits: 'Background removals', audio_character_limit: 'Audio characters',
                chat_credits: 'Chat questions', rewriter_credits: 'Rewrites', summarizer_credits: 'Summaries',
            };
            return LABELS[this.meterKey] ?? this.meterKey ?? '';
        },
        meterPct() {
            if (!this.meter || !(this.meter.limit > 0)) return 0;
            return Math.min(100, Math.round((this.meter.used / this.meter.limit) * 100));
        },
        freeLine() {
            if (!this.meter) return '';
            const freeLabel = this.meter.free ? (this.meter.renews === 'day' ? 'free uses today' : 'free uses this month') : 'Free';
            return `${freeLabel} — unlimited${this.meter.renews ? ` · renews ${this.meter.renews === 'day' ? 'daily' : 'monthly'}` : ''}`;
        },

        async submit() {
            const errs = {};
            for (const f of (this.schema?.fields ?? [])) {
                if (f.required && !this.values[f.name]) errs[f.name] = `${f.label} is required.`;
            }
            this.fieldErrors = errs;
            if (Object.keys(errs).length) return;

            this.busy = true; this.error = '';
            try {
                const form = new FormData();
                Object.entries(this.values).forEach(([k, v]) => { if (v !== undefined) form.append(k, v); });
                const res = await fetch(`/api/tools/${slug}/process`, { method: 'POST', headers: H, body: form });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    if (res.status === 422 && data.errors) {
                        const fe = {};
                        Object.entries(data.errors).forEach(([k, v]) => { fe[k] = Array.isArray(v) ? v[0] : v; });
                        this.fieldErrors = fe;
                    } else {
                        this.isLimitError = res.status === 402;
                        this.error = data.message || 'Something went wrong — please try again.';
                    }
                    return;
                }
                this.outputs = [Object.assign({}, data.result, { _input: Object.assign({}, this.values), _at: new Date().toISOString() }), ...this.outputs];
                this.load();
            } catch (e) {
                this.error = 'Could not reach the server — please try again.';
            } finally {
                this.busy = false;
            }
        },

        async clearAll() {
            if (!confirm('Delete ALL of your saved results for this tool? This cannot be undone.')) return;
            await fetch(`/api/tools/${slug}/history`, { method: 'DELETE', headers: H }).catch(() => {});
            this.outputs = [];
        },

        galleryUrls() {
            return this.outputs.flatMap((o) => o.urls ?? (o.url ? [o.url] : []));
        },
        dayGroups() {
            const groups = [];
            for (const out of this.outputs) {
                const day = out._at ? new Date(out._at).toDateString() : 'Earlier';
                const last = groups[groups.length - 1];
                if (last && last.day === day) last.items.push(out);
                else groups.push({ day, items: [out] });
            }
            return groups;
        },
        fmtDay(d) {
            const today = new Date().toDateString();
            return d === today ? 'Today' : new Date(d).toLocaleDateString(undefined, { weekday: 'short', day: 'numeric', month: 'short' });
        },
        answerText(out) {
            return typeof out.answer === 'string' ? out.answer : JSON.stringify(out.answer ?? '', null, 2);
        },
        shownText(out) {
            const a = this.answerText(out);
            if (out._expanded || a.length <= 420) return a;
            return a.slice(0, 420) + '…';
        },
        copyOut(out) {
            navigator.clipboard?.writeText(this.answerText(out));
            out._copied = true;
            setTimeout(() => { out._copied = false; }, 1500);
        },
        downloadText(text, format) {
            let blob, name;
            if (format === 'doc') {
                const html = `<html><head><meta charset="utf-8"></head><body>${text.split('\n').map((l) => `<p>${l.replace(/&/g, '&amp;').replace(/</g, '&lt;')}</p>`).join('')}</body></html>`;
                blob = new Blob([html], { type: 'application/msword' }); name = 'document-answer.doc';
            } else if (format === 'md') {
                blob = new Blob([text], { type: 'text/markdown' }); name = 'document-answer.md';
            } else {
                blob = new Blob([text], { type: 'text/plain' }); name = 'document-answer.txt';
            }
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = name;
            a.click();
            URL.revokeObjectURL(a.href);
        },
    }, toolGate(authed, hasPlan, gateways, {}));
}
</script>
@endpush
@endsection
