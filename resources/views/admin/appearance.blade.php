@extends('layouts.admin')

@section('content')
<div class="w-full" x-data="adminAppearance()" x-init="load()">
    <h1 class="font-display text-xl sm:text-2xl font-bold text-white mb-1">Appearance</h1>
    <p class="text-slate-400 text-sm mb-8">
        Branding and layout of the customer-facing site. Changes apply after visitors refresh.
    </p>

    {{-- ── Branding ── --}}
    <div class="card p-6 mb-5">
        <h2 class="font-display font-semibold text-white mb-4">Branding</h2>
        <div class="space-y-4">
            <div>
                <label class="block text-sm text-slate-300 mb-1.5">Brand name</label>
                <input class="input" x-model="values.brand_name" @input="saved = false">
            </div>
            <div>
                <label class="block text-sm text-slate-300 mb-1.5">Brand color</label>
                <div class="flex gap-3 items-center">
                    <input type="color" :value="values.brand_color || '#0ea5a4'" @input="values.brand_color = $event.target.value; saved = false"
                        class="h-10 w-14 rounded-lg bg-ink-800 border border-ink-700 cursor-pointer">
                    <input class="input" x-model="values.brand_color" placeholder="#0ea5a4" @input="saved = false">
                </div>
            </div>
            <div>
                <label class="block text-sm text-slate-300 mb-1.5">Site logo</label>
                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="h-16 w-40 rounded-xl bg-ink-800 border border-ink-700 grid place-items-center overflow-hidden shrink-0">
                        <img x-show="logo" :src="logo" alt="Site logo" class="max-h-12 max-w-[140px] object-contain">
                        <span x-show="!logo" class="text-xs text-slate-500">No logo yet</span>
                    </div>
                    <div class="flex gap-2">
                        <label class="btn-ghost cursor-pointer">
                            <span x-show="!logoBusy"><x-icon name="Upload" :size="15" /></span>
                            <span x-show="logoBusy" x-cloak class="inline-block animate-spin"><x-icon name="Loader2" :size="15" /></span>
                            Upload logo
                            <input type="file" class="hidden" accept=".png,.jpg,.jpeg,.svg,.webp"
                                @change="uploadLogo($event.target.files?.[0])">
                        </label>
                        <button type="button" x-show="logo" @click="removeLogo()" class="btn-ghost !px-3" title="Remove logo">
                            <x-icon name="Trash2" :size="15" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Header style ── --}}
    <div class="card p-6 mb-5">
        <h2 class="font-display font-semibold text-white mb-4">Header style</h2>
        <div class="grid sm:grid-cols-3 gap-4">
            @foreach ([
                ['classic', 'Classic', 'Logo left, menu center, buttons right'],
                ['centered', 'Centered', 'Logo centered, menu underneath'],
                ['minimal', 'Minimal', 'Logo left, everything else right'],
            ] as [$val, $label, $hint])
                <button type="button" @click="values.header_style = '{{ $val }}'; saved = false"
                    class="relative rounded-xl border p-3 text-left transition"
                    :class="values.header_style === '{{ $val }}' ? 'border-brand bg-brand/5' : 'border-ink-700 hover:border-brand/40'">
                    <span x-show="values.header_style === '{{ $val }}'" class="absolute top-2.5 right-2.5 text-brand"><x-icon name="CircleCheck" :size="16" /></span>
                    <div class="h-14 rounded-lg bg-ink-950/70 border border-ink-700/60 mb-3 overflow-hidden">
                        @if ($val === 'classic')
                            <div class="flex items-center justify-between px-3 h-full">
                                <span class="w-8 h-2.5 rounded bg-brand/80"></span>
                                <span class="flex gap-1.5"><span class="w-6 h-1.5 rounded bg-ink-700"></span><span class="w-6 h-1.5 rounded bg-ink-700"></span><span class="w-6 h-1.5 rounded bg-ink-700"></span></span>
                                <span class="w-10 h-3 rounded bg-brand/50"></span>
                            </div>
                        @elseif ($val === 'centered')
                            <div class="flex flex-col items-center justify-center gap-1.5 h-full">
                                <span class="w-10 h-2.5 rounded bg-brand/80"></span>
                                <span class="flex gap-1.5"><span class="w-5 h-1.5 rounded bg-ink-700"></span><span class="w-5 h-1.5 rounded bg-ink-700"></span><span class="w-5 h-1.5 rounded bg-ink-700"></span><span class="w-5 h-1.5 rounded bg-ink-700"></span></span>
                            </div>
                        @else
                            <div class="flex items-center justify-between px-3 h-full">
                                <span class="w-8 h-2.5 rounded bg-brand/80"></span>
                                <span class="flex gap-1.5 items-center">
                                    <span class="w-5 h-1.5 rounded bg-ink-700"></span><span class="w-5 h-1.5 rounded bg-ink-700"></span><span class="w-5 h-1.5 rounded bg-ink-700"></span>
                                    <span class="w-8 h-3 rounded bg-brand/50"></span>
                                </span>
                            </div>
                        @endif
                    </div>
                    <p class="text-sm font-medium text-white">{{ $label }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $hint }}</p>
                </button>
            @endforeach
        </div>
    </div>

    {{-- ── Footer style ── --}}
    <div class="card p-6 mb-5">
        <h2 class="font-display font-semibold text-white mb-4">Footer style</h2>
        <div class="grid sm:grid-cols-3 gap-4">
            @foreach ([
                ['simple', 'Simple', 'One row: brand left, links right'],
                ['columns', 'Columns', 'Brand blurb + link columns'],
                ['minimal', 'Minimal', 'Single centered line'],
            ] as [$val, $label, $hint])
                <button type="button" @click="values.footer_style = '{{ $val }}'; saved = false"
                    class="relative rounded-xl border p-3 text-left transition"
                    :class="values.footer_style === '{{ $val }}' ? 'border-brand bg-brand/5' : 'border-ink-700 hover:border-brand/40'">
                    <span x-show="values.footer_style === '{{ $val }}'" class="absolute top-2.5 right-2.5 text-brand"><x-icon name="CircleCheck" :size="16" /></span>
                    <div class="h-14 rounded-lg bg-ink-950/70 border border-ink-700/60 mb-3 overflow-hidden">
                        @if ($val === 'simple')
                            <div class="flex items-center justify-between px-3 h-full">
                                <span class="w-10 h-2 rounded bg-brand/60"></span>
                                <span class="flex gap-1.5"><span class="w-5 h-1.5 rounded bg-ink-700"></span><span class="w-5 h-1.5 rounded bg-ink-700"></span><span class="w-5 h-1.5 rounded bg-ink-700"></span></span>
                            </div>
                        @elseif ($val === 'columns')
                            <div class="grid grid-cols-3 gap-2 px-3 py-2 h-full">
                                <div class="space-y-1"><span class="block w-8 h-2 rounded bg-brand/60"></span><span class="block w-full h-1 rounded bg-ink-700"></span><span class="block w-3/4 h-1 rounded bg-ink-700"></span></div>
                                <div class="space-y-1"><span class="block w-2/3 h-1 rounded bg-ink-700"></span><span class="block w-2/3 h-1 rounded bg-ink-700"></span><span class="block w-2/3 h-1 rounded bg-ink-700"></span></div>
                                <div class="space-y-1"><span class="block w-2/3 h-1 rounded bg-ink-700"></span><span class="block w-2/3 h-1 rounded bg-ink-700"></span><span class="block w-2/3 h-1 rounded bg-ink-700"></span></div>
                            </div>
                        @else
                            <div class="grid place-items-center h-full">
                                <span class="w-16 h-1.5 rounded bg-ink-700"></span>
                            </div>
                        @endif
                    </div>
                    <p class="text-sm font-medium text-white">{{ $label }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $hint }}</p>
                </button>
            @endforeach
        </div>
    </div>

    {{-- ── Theme & colors ── --}}
    <div class="card p-6 mb-5">
        <h2 class="font-display font-semibold text-white mb-1">Theme &amp; colors</h2>
        <p class="text-xs text-slate-500 mb-5">
            Give visitors a dark/light switch, and optionally set your own background and
            text colors for each mode. Leave a color empty to use the built-in design.
        </p>

        <label class="flex items-start gap-2.5 cursor-pointer mb-5">
            <input type="checkbox" class="accent-[rgb(var(--brand))] mt-0.5"
                :checked="values.theme_toggle_enabled === '1'"
                @change="values.theme_toggle_enabled = $event.target.checked ? '1' : '0'; saved = false">
            <span class="text-sm text-slate-200">Show the dark / light mode switch to visitors</span>
        </label>

        <div class="grid sm:grid-cols-2 gap-4">
            @foreach ([
                ['theme_dark_bg', 'Dark mode — background color'],
                ['theme_dark_text', 'Dark mode — text color'],
                ['theme_light_bg', 'Light mode — background color'],
                ['theme_light_text', 'Light mode — text color'],
            ] as [$key, $label])
                <div>
                    <label class="text-xs text-slate-400 block mb-1.5">{{ $label }}</label>
                    <div class="flex items-center gap-2">
                        <input type="color" class="w-11 h-11 rounded-lg bg-transparent border border-ink-700 cursor-pointer p-1"
                            :value="values.{{ $key }} || '#0b1220'" @input="values.{{ $key }} = $event.target.value; saved = false">
                        <input class="input flex-1" placeholder="Leave empty for the default" x-model="values.{{ $key }}" @input="saved = false">
                        <button type="button" title="Clear" x-show="values.{{ $key }}" @click="values.{{ $key }} = ''; saved = false"
                            class="p-2 rounded-lg border border-ink-700 text-slate-400 hover:text-red-400">
                            <x-icon name="Trash2" :size="14" />
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ── Loader ── --}}
    <div class="card p-6 mb-5">
        <h2 class="font-display font-semibold text-white mb-1">Loader</h2>
        <p class="text-xs text-slate-500 mb-5">
            An optional splash screen shown while the site loads. It appears once per
            visit, fades out on its own, and never delays the page by more than a moment.
        </p>

        <label class="flex items-start gap-2.5 cursor-pointer mb-6">
            <input type="checkbox" class="accent-[rgb(var(--brand))] mt-0.5"
                :checked="values.loader_enabled === '1'"
                @change="values.loader_enabled = $event.target.checked ? '1' : '0'; saved = false">
            <span>
                <span class="block text-sm text-white">Show the loading screen</span>
                <span class="block text-xs text-slate-500 mt-0.5">Turn this off and visitors go straight to the site.</span>
            </span>
        </label>

        <p class="text-xs uppercase tracking-widest text-slate-500 mb-3">Choose a style</p>
        <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4" :class="values.loader_enabled === '1' ? '' : 'opacity-50 pointer-events-none'">
            @foreach ([
                ['neural', 'Neural network', 'Nodes firing along connecting lines — signature AI look.'],
                ['node', 'AI node', 'Glass card with a pulsing core, orbiting dot and waveform.'],
                ['orbit', 'Orbit rings', 'Three counter-rotating rings around a glowing core.'],
                ['pulse', 'Sonar pulse', 'Calm expanding rings radiating from a bright centre.'],
                ['prism', 'Prism fold', 'Three squares folding through each other in sequence.'],
            ] as [$lid, $lname, $ldesc])
                <button type="button" @click="values.loader_style = '{{ $lid }}'; saved = false"
                    class="relative rounded-2xl border-2 p-4 text-left transition"
                    :class="values.loader_style === '{{ $lid }}' ? 'border-brand bg-brand/5' : 'border-ink-700 hover:border-brand/50'">
                    <span x-show="values.loader_style === '{{ $lid }}'"
                        class="absolute top-3 right-3 w-5 h-5 rounded-full grid place-items-center text-ink-950 z-10"
                        style="background: linear-gradient(135deg, rgb(var(--brand)), rgb(var(--accent)))">
                        <x-icon name="Check" :size="12" />
                    </span>
                    {{-- the real loader animation, running live (same markup as the site loader) --}}
                    <div class="rounded-xl bg-ink-950/70 grid place-items-center h-40 mb-3 overflow-hidden">
                        <div class="scale-[0.72] sl-preview">
                            @include('partials.loader-art', ['loaderStyle' => $lid, 'loaderName' => 'Preview'])
                        </div>
                    </div>
                    <p class="text-sm font-semibold text-white">{{ $lname }}</p>
                    <p class="text-xs text-slate-500 mt-0.5 leading-snug">{{ $ldesc }}</p>
                </button>
            @endforeach
        </div>
    </div>

    <p class="text-sm text-red-400 mb-4" x-show="error" x-text="error" x-cloak></p>

    {{-- One save button for the whole page --}}
    <div class="sticky bottom-4 z-10">
        <button type="button" class="btn-brand !px-8 shadow-2xl" @click="save()" :disabled="busy"
            x-text="busy ? 'Saving…' : saved ? 'Saved ✓' : 'Save appearance'"></button>
    </div>
</div>

@push('scripts')
<script>
function adminAppearance() {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const H = { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf };
    return {
        values: {
            brand_name: '', brand_color: '#0ea5a4', header_style: 'classic', footer_style: 'columns',
            theme_toggle_enabled: '1', theme_dark_bg: '', theme_dark_text: '',
            theme_light_bg: '', theme_light_text: '',
            loader_enabled: '1', loader_style: 'neural',
        },
        logo: null, busy: false, logoBusy: false, saved: false, error: '',

        async load() {
            const res = await fetch('/api/branding', { headers: H });
            if (!res.ok) return;
            const b = await res.json();
            this.values = {
                brand_name: b.brand_name ?? '',
                brand_color: b.brand_color ?? '#0ea5a4',
                header_style: b.header_style ?? 'classic',
                footer_style: b.footer_style ?? 'columns',
                theme_toggle_enabled: b.theme_toggle === false ? '0' : '1',
                theme_dark_bg: b.theme_colors?.dark_bg ?? '',
                theme_dark_text: b.theme_colors?.dark_text ?? '',
                theme_light_bg: b.theme_colors?.light_bg ?? '',
                theme_light_text: b.theme_colors?.light_text ?? '',
                loader_enabled: (b.loader_enabled === '1' || b.loader_enabled === true) ? '1' : '0',
                loader_style: b.loader_style ?? 'neural',
            };
            this.logo = b.brand_logo;
        },

        async save() {
            this.busy = true; this.saved = false; this.error = '';
            try {
                const res = await fetch('/api/admin/settings', {
                    method: 'PUT', headers: H,
                    body: JSON.stringify({ settings: Object.entries(this.values).map(([key, value]) => ({ key, value, group: 'branding' })) }),
                });
                if (!res.ok) { const d = await res.json().catch(() => ({})); this.error = d.message || 'Could not save.'; return; }
                this.saved = true;
                setTimeout(() => { this.saved = false; }, 2500);
            } finally { this.busy = false; }
        },

        async uploadLogo(file) {
            if (!file) return;
            this.logoBusy = true; this.error = '';
            try {
                const form = new FormData();
                form.append('logo', file);
                const res = await fetch('/api/admin/settings/logo', {
                    method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }, body: form,
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) { this.error = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Upload failed.'); return; }
                this.logo = data.brand_logo;
            } finally { this.logoBusy = false; }
        },

        async removeLogo() {
            this.logoBusy = true;
            await fetch('/api/admin/settings/logo/remove', { method: 'POST', headers: H }).catch(() => {});
            this.logo = null;
            this.logoBusy = false;
        },
    };
}
</script>
@endpush
@endsection
