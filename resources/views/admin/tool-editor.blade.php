@extends('layouts.admin')

@section('content')
<div x-data="toolEditor({{ (int) $id }})" x-init="load()">
    <a href="{{ url('/admin/tools') }}" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-brand mb-6 group">
        <x-icon name="ArrowLeft" :size="15" class="transition group-hover:-translate-x-1" /> All tools
    </a>

    <p class="text-slate-400" x-show="!form">Loading…</p>

    <template x-if="form">
        <div>
            <div class="flex items-center gap-3 mb-8">
                <span class="p-3 rounded-2xl bg-brand/15 text-brand" x-html="iconSvg(tool.icon)"></span>
                <div>
                    <h1 class="font-display text-xl sm:text-2xl font-bold text-white" x-text="tool.name"></h1>
                    <p class="text-xs text-slate-500" x-text="'/' + tool.slug"></p>
                </div>
            </div>

            <div class="grid lg:grid-cols-2 gap-6 items-start">
                <div class="card p-6 space-y-5">
                    <h2 class="font-display font-semibold text-white">Tool details</h2>
                    <div>
                        <label class="text-xs text-slate-400 block mb-1.5">Tool name (shown everywhere on the site)</label>
                        <input class="input" x-model="form.name" @input="saved = false">
                    </div>
                    <div>
                        <label class="text-xs text-slate-400 block mb-1.5">Short description (shown on tool cards and the tool page)</label>
                        <textarea class="input min-h-24" x-model="form.description" @input="saved = false"></textarea>
                    </div>
                    <div>
                        <label class="text-xs text-slate-400 block mb-1.5">Category (used for the filter tabs on the Tools page)</label>
                        <select class="input" x-model="form.taxonomy_id" @change="saved = false">
                            <option value="">No category</option>
                            <template x-for="c in cats" :key="c.id">
                                <option :value="c.id" x-text="c.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-slate-400 block mb-1.5">Status</label>
                        <select class="input" x-model="form.status" @change="saved = false">
                            <option value="active">Active — customers can use it</option>
                            <option value="inactive">Inactive — hidden from the site</option>
                            <option value="coming_soon">Coming soon — visible but locked</option>
                        </select>
                    </div>
                </div>

                <div class="card p-6 space-y-5">
                    <h2 class="font-display font-semibold text-white">Free credits (no plan needed)</h2>
                    <p class="text-xs text-slate-500 leading-relaxed -mt-2">
                        Let signed-in users WITHOUT a subscription try this tool for free. Their free uses
                        renew automatically — daily or monthly, your choice — and the tool page shows them
                        how many they have left and when they renew.
                    </p>
                    <label class="flex items-center gap-2.5 text-sm text-slate-300 cursor-pointer">
                        <input type="checkbox" class="accent-[rgb(var(--brand))]" x-model="form.free_enabled" @change="saved = false">
                        Enable free usage for signed-in users
                    </label>
                    <div x-show="form.free_enabled" class="space-y-5">
                        <div>
                            <label class="text-xs text-slate-400 block mb-1.5">Free uses per period (leave empty for unlimited)</label>
                            <input type="number" min="1" class="input" placeholder="e.g. 5" x-model="form.free_limit" @input="saved = false">
                        </div>
                        <div>
                            <label class="text-xs text-slate-400 block mb-1.5">Credits renew every…</label>
                            <select class="input" x-model="form.free_unit" @change="saved = false">
                                <option value="day">Day — resets at midnight</option>
                                <option value="month">Month — resets on the 1st</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div x-show="error" x-cloak class="mt-5 rounded-xl border border-red-500/40 bg-red-500/10 text-red-400 text-sm px-4 py-3" x-text="error"></div>
            <div x-show="saved" x-cloak class="mt-5 rounded-xl border border-brand/40 bg-brand/10 text-brand text-sm px-4 py-3">Saved — the site is updated.</div>

            <button type="button" @click="save()" :disabled="busy" class="btn-brand mt-6 !px-8">
                <x-icon name="Save" :size="15" /> <span x-text="busy ? 'Saving…' : 'Save changes'"></span>
            </button>
        </div>
    </template>

    <div class="hidden" x-ref="iconset">
        @foreach (['ImagePlus', 'PenLine', 'Languages', 'FileSearch', 'Eraser', 'AudioLines', 'MessageCircleMore', 'ScanText', 'Wand2'] as $ic)
            <span data-icon="{{ $ic }}"><x-icon :name="$ic" :size="24" /></span>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
function toolEditor(id) {
    const H = { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content };
    return {
        tool: null, form: null, cats: [], busy: false, saved: false, error: '',
        async load() {
            const [toolsRes, catsRes] = await Promise.all([
                fetch('/api/admin/tools', { headers: H }),
                fetch('/api/admin/taxonomies', { headers: H }),
            ]);
            if (catsRes.ok) this.cats = await catsRes.json();
            if (!toolsRes.ok) return;
            const list = await toolsRes.json();
            const found = list.find((x) => String(x.id) === String(id));
            if (!found) { location.href = '/admin/tools'; return; }
            this.tool = found;
            this.form = {
                name: found.name,
                description: found.description ?? '',
                taxonomy_id: found.taxonomy_id ?? '',
                status: found.status,
                free_enabled: !!found.free_enabled,
                free_limit: found.free_limit ?? '',
                free_unit: found.free_unit ?? 'month',
            };
        },
        iconSvg(name) {
            const el = this.$refs.iconset.querySelector(`[data-icon="${name}"]`) || this.$refs.iconset.querySelector('[data-icon="Wand2"]');
            return el ? el.innerHTML : '';
        },
        async save() {
            this.busy = true; this.error = '';
            try {
                const res = await fetch(`/api/admin/tools/${this.tool.id}`, {
                    method: 'PUT', headers: H,
                    body: JSON.stringify({
                        ...this.form,
                        taxonomy_id: this.form.taxonomy_id === '' ? null : parseInt(this.form.taxonomy_id, 10),
                        free_limit: this.form.free_limit === '' ? null : parseInt(this.form.free_limit, 10),
                    }),
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    this.error = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Could not save.');
                    return;
                }
                this.saved = true;
            } finally { this.busy = false; }
        },
    };
}
</script>
@endpush
@endsection
