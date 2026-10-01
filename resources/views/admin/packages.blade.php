@extends('layouts.admin')

@section('content')
<div x-data="adminPackages()" x-init="load(); loadCustomers()">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-8">
        <h1 class="font-display text-xl sm:text-2xl font-bold text-white">Packages</h1>
        <button type="button" class="btn-brand" @click="openNew()"><x-icon name="Plus" :size="16" /> New package</button>
    </div>

    <div class="grid md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-5">
        <template x-for="(pkg, pi) in packages" :key="pkg.id">
            <div class="card card-laminate spotlight p-0 overflow-hidden flex flex-col animate-slide-up" :style="`animation-delay: ${pi * 60}ms`">
                <div class="relative px-6 pt-5 pb-4"
                    style="background: linear-gradient(135deg, rgb(var(--brand) / .16), rgb(var(--accent) / .1))">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <h2 class="font-display font-semibold text-white truncate" x-text="pkg.name"></h2>
                            <p class="mt-1">
                                <span class="font-display text-xl sm:text-2xl font-bold text-white" x-text="'$' + pkg.price"></span>
                                <span class="text-xs text-slate-400" x-text="' / ' + (pkg.billing_cycle === 'yearly' ? 'year' : 'month')"></span>
                            </p>
                        </div>
                        <div class="flex flex-col items-end gap-1.5 shrink-0">
                            <span class="text-[10px] uppercase tracking-wider rounded-full px-2.5 py-1 border"
                                :class="pkg.status === 'active' ? 'text-brand border-brand/40 bg-brand/10' : 'text-slate-500 border-ink-700'"
                                x-text="pkg.status"></span>
                            <span x-show="pkg.is_custom"
                                class="text-[10px] uppercase tracking-wider rounded-full px-2.5 py-1 text-ink-950 font-bold"
                                style="background: linear-gradient(90deg, rgb(var(--brand)), rgb(var(--accent)))">Custom</span>
                            <span x-show="pkg.discount_percent > 0"
                                class="text-[10px] font-bold text-amber-300 border border-amber-400/40 rounded-full px-2 py-0.5"
                                x-text="pkg.discount_percent + '% off'"></span>
                        </div>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1.5 flex items-center gap-3">
                        <span x-text="`👥 ${pkg.subscriptions_count} subscriber${pkg.subscriptions_count === 1 ? '' : 's'}`"></span>
                        <span x-text="`🖥 ${pkg.max_sessions ?? '∞'} browser${pkg.max_sessions === 1 ? '' : 's'}`"></span>
                    </p>
                </div>

                <ul class="text-sm space-y-1.5 px-6 py-4 flex-1">
                    <template x-for="[k, v] in Object.entries(pkg.features ?? {})" :key="k">
                        <li class="flex justify-between items-center gap-3">
                            <span class="flex items-center gap-2 text-slate-400 text-[13px]">
                                <x-icon name="Check" :size="13" class="text-brand shrink-0" />
                                <span x-text="LABELS[k] ?? k"></span>
                            </span>
                            <span :class="Number(v) === -1 ? 'font-semibold animate-gradient-text text-[13px]' : 'text-slate-200 text-[13px] tabular-nums'"
                                x-text="Number(v) === -1 ? '∞ Unlimited' : Number(v).toLocaleString()"></span>
                        </li>
                    </template>
                </ul>
                <div class="flex gap-2 px-6 pb-5">
                    <button type="button" @click="openEdit(pkg)" class="btn-brand flex-1"><x-icon name="Pencil" :size="14" /> Edit</button>
                    <button type="button" @click="remove(pkg)" class="p-2.5 rounded-xl border border-ink-700 text-slate-400 hover:text-red-400">
                        <x-icon name="Trash2" :size="16" />
                    </button>
                </div>
            </div>
        </template>
    </div>

    {{-- Edit / create modal --}}
    <template x-teleport="body">
    <div x-show="editing" x-cloak class="fixed inset-0 bg-black/60 grid place-items-center p-4 z-50" @click="editing = null">
        <div class="relative card p-7 w-full max-w-lg max-h-[90vh] overflow-y-auto animate-pop-in" @click.stop>
            <button type="button" @click="editing = null" aria-label="Close"
                class="absolute top-4 right-4 p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-ink-800 transition">
                <x-icon name="X" :size="18" />
            </button>
            <h2 class="font-display font-semibold text-white text-lg mb-5" x-text="editing?.id ? 'Edit package' : 'New package'"></h2>
            <template x-if="editing">
                <div class="space-y-4">
                    <input class="input" placeholder="Package name" x-model="editing.name">
                    <div class="grid grid-cols-2 gap-4">
                        <input class="input" type="number" min="0" step="0.01" placeholder="Price" x-model="editing.price">
                        <select class="input" x-model="editing.billing_cycle">
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                    <input class="input" placeholder="Stripe price ID (price_…)" x-model="editing.stripe_plan_id">

                    <div class="flex items-center justify-between gap-4">
                        <label class="text-sm text-slate-400">Discount badge (% OFF shown on pricing — leave empty for none)</label>
                        <input class="input !w-24" type="number" min="0" max="90" placeholder="—"
                            :value="editing.discount_percent ?? ''"
                            @input="editing.discount_percent = $event.target.value === '' ? null : parseInt($event.target.value, 10)">
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <label class="text-sm text-slate-400">Browser logins allowed at the same time (empty = unlimited)</label>
                        <input class="input !w-24" type="number" min="1" max="100" placeholder="∞"
                            :value="editing.max_sessions ?? ''"
                            @input="editing.max_sessions = $event.target.value === '' ? null : parseInt($event.target.value, 10)">
                    </div>

                    <div class="rounded-xl border border-ink-700 p-4 space-y-3">
                        <label class="flex items-center gap-2.5 text-sm text-slate-300 cursor-pointer">
                            <input type="checkbox" class="accent-[rgb(var(--brand))]" :checked="editing.is_custom"
                                @change="editing.is_custom = $event.target.checked; if (!editing.is_custom) editing.user_id = null">
                            Custom package — private to one customer
                        </label>
                        <select class="input" x-show="editing.is_custom" x-model.number="editing.user_id">
                            <option value="">Choose the customer…</option>
                            <template x-for="c in customers" :key="c.id">
                                <option :value="c.id" x-text="`${c.name} — ${c.email}`"></option>
                            </template>
                        </select>
                        <p class="text-xs text-slate-500" x-show="editing.is_custom">
                            Only this customer will see and be able to buy this package. It never appears on the public pricing page for anyone else.
                        </p>
                    </div>

                    <p class="text-sm font-medium text-slate-300 pt-2">Tool limits per billing cycle</p>
                    <template x-for="key in Object.keys(LABELS)" :key="key">
                        <div class="flex items-center justify-between gap-3">
                            <label class="text-sm text-slate-400 flex-1" x-text="LABELS[key]"></label>
                            <span class="text-sm font-semibold animate-gradient-text" x-show="editing.features[key] === -1">∞ Unlimited</span>
                            <input class="input !w-28" type="number" min="0" x-show="editing.features[key] !== -1"
                                :value="editing.features[key] ?? 0"
                                @input="editing.features[key] = parseInt($event.target.value || 0, 10)">
                            <button type="button" title="Toggle unlimited credits"
                                @click="editing.features[key] = editing.features[key] === -1 ? 0 : -1"
                                class="px-2.5 py-2 rounded-lg border text-xs font-bold transition"
                                :class="editing.features[key] === -1 ? 'border-brand text-brand bg-brand/10' : 'border-ink-700 text-slate-400 hover:border-brand/50'">
                                ∞
                            </button>
                        </div>
                    </template>

                    <select class="input" x-model="editing.status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>

                    <p class="text-sm text-red-400" x-show="error" x-text="error" x-cloak></p>
                    <div class="flex gap-3 pt-2">
                        <button type="button" class="btn-brand flex-1" @click="save()">Save package</button>
                        <button type="button" class="px-5 py-2.5 rounded-xl border border-ink-700 text-slate-300 text-sm" @click="editing = null">Cancel</button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>
    </div>

@push('scripts')
<script>
function adminPackages() {
    const H = { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content };
    return {
        LABELS: {
            image_generation_credits: 'Image credits',
            content_writer_credits: 'Content writer credits',
            translation_credits: 'Translation credits',
            document_query_credits: 'Document query credits',
            background_removal_credits: 'Background removal credits',
            audio_character_limit: 'Audio character limit',
            chat_credits: 'Chat assistant credits',
            rewriter_credits: 'Rewriter credits',
            summarizer_credits: 'Summarizer credits',
        },
        packages: [], customers: [], editing: null, error: '',
        async load() {
            const res = await fetch('/api/admin/packages', { headers: H });
            if (res.ok) this.packages = await res.json();
        },
        async loadCustomers() {
            try {
                const res = await fetch('/api/admin/customers', { headers: H });
                if (res.ok) { const d = await res.json(); this.customers = Array.isArray(d) ? d : d.data ?? []; }
            } catch (e) { /* optional */ }
        },
        openNew() {
            this.error = '';
            this.editing = { name: '', price: 0, billing_cycle: 'monthly', status: 'active', stripe_plan_id: '', paypal_plan_id: '', features: {} };
        },
        openEdit(pkg) {
            this.error = '';
            this.editing = JSON.parse(JSON.stringify(pkg));
            this.editing.features = this.editing.features ?? {};
        },
        async save() {
            this.error = '';
            const path = this.editing.id ? `/api/admin/packages/${this.editing.id}` : '/api/admin/packages';
            const res = await fetch(path, { method: this.editing.id ? 'PUT' : 'POST', headers: H, body: JSON.stringify(this.editing) });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) {
                this.error = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Could not save.');
                return;
            }
            this.editing = null; this.load();
        },
        async remove(pkg) {
            if (!confirm(`Delete "${pkg.name}"?`)) return;
            await fetch(`/api/admin/packages/${pkg.id}`, { method: 'DELETE', headers: H });
            this.load();
        },
    };
}
</script>
@endpush
@endsection
