@extends('layouts.admin')

@section('content')
<div x-data="adminCustomers()" x-init="load(); loadPackages()">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <h1 class="font-display text-xl sm:text-2xl font-bold text-white">Customers</h1>
        <button type="button" class="btn-brand" @click="modal = 'create'; resetCreate()">
            <x-icon name="UserPlus" :size="16" /> New customer
        </button>
    </div>

    <div class="relative mb-5 max-w-sm">
        <x-icon name="Search" :size="16" class="absolute left-3.5 top-3 text-slate-500" />
        <input class="input !pl-10 pl-10" placeholder="Search name or email…" x-model="search" @input="load()">
    </div>

    <div x-show="flash" x-cloak class="mb-4 rounded-xl border border-brand/40 bg-brand/10 text-brand text-sm px-4 py-3" x-text="flash"></div>

    <div class="card overflow-x-auto">
        <table class="w-full text-sm min-w-[640px]">
            <thead class="text-left text-slate-400 border-b border-ink-700/60">
                <tr>
                    <th class="p-4">Customer</th>
                    <th class="p-4">Package</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="u in rows" :key="u.id">
                    <tr class="border-b border-ink-700/40 last:border-0">
                        <td class="p-4">
                            <p class="text-white flex items-center gap-2">
                                <span x-text="u.name"></span>
                                <span x-show="u.role === 'admin'"
                                    class="text-[9px] uppercase tracking-wider rounded-full px-2 py-0.5 text-ink-950 font-bold"
                                    style="background: linear-gradient(90deg, rgb(var(--brand)), rgb(var(--accent)))">Admin</span>
                            </p>
                            <p class="text-slate-500 text-xs" x-text="u.email"></p>
                        </td>
                        <td class="p-4 text-slate-300">
                            <span x-show="u.active_subscription?.package?.name" x-text="u.active_subscription?.package?.name"></span>
                            <span x-show="!u.active_subscription?.package?.name" class="text-slate-500">No plan</span>
                        </td>
                        <td class="p-4">
                            <span x-show="u.is_blocked" class="text-red-400 text-sm">Blocked</span>
                            <select x-show="!u.is_blocked" :value="u.status ?? 'active'"
                                @change="setStatus(u, $event.target.value)"
                                class="bg-ink-800 border border-ink-700 rounded-lg px-2 py-1 text-xs"
                                :class="u.status === 'pending' ? 'text-amber-400' : 'text-brand'">
                                <option value="active">Active</option>
                                <option value="pending">Pending</option>
                            </select>
                        </td>
                        <td class="p-4 text-right space-x-2 whitespace-nowrap">
                            <button type="button" title="Edit details" @click="openEdit(u)"
                                class="p-2 rounded-lg border border-ink-700 text-slate-400 hover:text-brand">
                                <x-icon name="Pencil" :size="15" />
                            </button>
                            <button type="button" title="Email their current details" @click="informUser(u)" :disabled="informing === u.id"
                                class="p-2 rounded-lg border border-ink-700 text-slate-400 hover:text-brand disabled:opacity-50">
                                <span x-show="informing !== u.id"><x-icon name="MailCheck" :size="15" /></span>
                                <span x-show="informing === u.id" x-cloak class="inline-block animate-spin"><x-icon name="Loader2" :size="15" /></span>
                            </button>
                            <template x-if="u.role !== 'admin'">
                                <span class="space-x-2">
                                    <button type="button" title="Assign plan" @click="openAssign(u)"
                                        class="p-2 rounded-lg border border-ink-700 text-slate-400 hover:text-brand">
                                        <x-icon name="BadgePlus" :size="15" />
                                    </button>
                                    <button type="button" title="Adjust credits" @click="openCredits(u)"
                                        class="p-2 rounded-lg border border-ink-700 text-slate-400 hover:text-brand">
                                        <x-icon name="Coins" :size="15" />
                                    </button>
                                    <button type="button" :title="u.is_blocked ? 'Unblock' : 'Block'" @click="toggleBlock(u)"
                                        class="p-2 rounded-lg border border-ink-700 text-slate-400 hover:text-red-400">
                                        <span x-show="u.is_blocked"><x-icon name="CircleCheck" :size="15" /></span>
                                        <span x-show="!u.is_blocked"><x-icon name="Ban" :size="15" /></span>
                                    </button>
                                </span>
                            </template>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    {{-- New customer --}}
    <template x-teleport="body">
    <div x-show="modal === 'create'" x-cloak class="fixed inset-0 bg-black/60 backdrop-blur-sm grid place-items-center p-4 z-50" @click="closeModal()">
        <div class="relative card p-7 w-full max-w-sm animate-pop-in" @click.stop>
            <button type="button" @click="closeModal()" aria-label="Close"
                class="absolute top-4 right-4 p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-ink-800 transition">
                <x-icon name="X" :size="18" />
            </button>
            <h2 class="font-display font-semibold text-white mb-1">New customer</h2>
            <p class="text-sm text-slate-400 mb-5">A welcome email is sent automatically (if enabled in Settings).</p>
            <div class="space-y-4">
                <input class="input" placeholder="Full name" x-model="createForm.name">
                <input class="input" type="email" placeholder="Email" x-model="createForm.email">
                <input class="input" type="text" placeholder="Password (min 8 characters)" x-model="createForm.password">
                <p class="text-sm text-red-400" x-show="error" x-text="error" x-cloak></p>
                <button type="button" class="btn-brand w-full" @click="save('/api/admin/customers', 'POST', createForm)">Create customer</button>
            </div>
        </div>
    </div>

    </template>
    {{-- Assign plan --}}
    <template x-teleport="body">
    <div x-show="modal === 'assign'" x-cloak class="fixed inset-0 bg-black/60 backdrop-blur-sm grid place-items-center p-4 z-50" @click="closeModal()">
        <div class="relative card p-7 w-full max-w-sm animate-pop-in" @click.stop>
            <button type="button" @click="closeModal()" aria-label="Close"
                class="absolute top-4 right-4 p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-ink-800 transition">
                <x-icon name="X" :size="18" />
            </button>
            <h2 class="font-display font-semibold text-white mb-1">Assign a plan</h2>
            <p class="text-sm text-slate-400 mb-5"><span x-text="target?.name"></span> — replaces any current plan, no payment required.</p>
            <div class="space-y-4">
                <select class="input" x-model="assignForm.package_id">
                    <template x-for="p in packages.filter((p) => p.status === 'active')" :key="p.id">
                        <option :value="p.id" x-text="`${p.name} — $${p.price}/${p.billing_cycle === 'yearly' ? 'yr' : 'mo'}`"></option>
                    </template>
                </select>
                <div>
                    <label class="block text-sm text-slate-400 mb-1.5">Duration (months)</label>
                    <input class="input" type="number" min="1" max="36" x-model.number="assignForm.months">
                </div>
                <p class="text-sm text-red-400" x-show="error" x-text="error" x-cloak></p>
                <button type="button" class="btn-brand w-full" @click="save(`/api/admin/customers/${target.id}/assign-plan`, 'POST', assignForm)">Assign plan</button>
            </div>
        </div>
    </div>

    </template>
    {{-- Adjust credits --}}
    <template x-teleport="body">
    <div x-show="modal === 'credits'" x-cloak class="fixed inset-0 bg-black/60 backdrop-blur-sm grid place-items-center p-4 z-50" @click="closeModal()">
        <div class="relative card p-7 w-full max-w-sm animate-pop-in" @click.stop>
            <button type="button" @click="closeModal()" aria-label="Close"
                class="absolute top-4 right-4 p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-ink-800 transition">
                <x-icon name="X" :size="18" />
            </button>
            <h2 class="font-display font-semibold text-white mb-1">Adjust credits</h2>
            <p class="text-sm text-slate-400 mb-5"><span x-text="target?.name"></span> — bonus applies to the current cycle. Negative reduces.</p>
            <div class="space-y-4">
                <select class="input" x-model="creditsForm.feature_key">
                    <option value="image_generation_credits">Image credits</option>
                    <option value="content_writer_credits">Content writer credits</option>
                    <option value="translation_credits">Translation credits</option>
                    <option value="document_query_credits">Document query credits</option>
                    <option value="background_removal_credits">Background removal credits</option>
                    <option value="audio_character_limit">Audio characters</option>
                </select>
                <input class="input" type="number" x-model.number="creditsForm.bonus">
                <p class="text-sm text-red-400" x-show="error" x-text="error" x-cloak></p>
                <button type="button" class="btn-brand w-full" @click="save(`/api/admin/customers/${target.id}/credits`, 'POST', creditsForm)">Apply</button>
            </div>
        </div>
    </div>

    </template>
    {{-- Edit account --}}
    <template x-teleport="body">
    <div x-show="modal === 'edit'" x-cloak class="fixed inset-0 z-50 grid place-items-center p-4 bg-ink-950/70 backdrop-blur-md" @click="!busy && closeModal()">
        <div class="relative card gradient-ring p-7 w-full max-w-md animate-pop-in max-h-[90vh] overflow-y-auto" @click.stop>
            <button type="button" @click="closeModal()" :disabled="busy" aria-label="Close"
                class="absolute z-20 top-4 right-4 p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-ink-800 transition">
                <x-icon name="X" :size="18" />
            </button>
            <h2 class="font-display font-semibold text-white text-lg">Edit account</h2>
            <p class="text-xs text-slate-500 mt-1 mb-5">
                <span x-text="target?.role === 'admin' ? 'Administrator account' : 'Customer account'"></span> · <span x-text="target?.email"></span>
            </p>
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs text-slate-400 block mb-1.5">First name</label>
                        <input class="input" x-model="editForm.first_name">
                    </div>
                    <div>
                        <label class="text-xs text-slate-400 block mb-1.5">Last name</label>
                        <input class="input" x-model="editForm.last_name">
                    </div>
                </div>
                <div>
                    <label class="text-xs text-slate-400 block mb-1.5">Email address</label>
                    <input class="input" type="email" x-model="editForm.email">
                </div>
                <div x-show="target?.role === 'admin'">
                    <label class="text-xs text-slate-400 block mb-1.5">
                        Current password <span class="text-slate-600">(required to change an admin password)</span>
                    </label>
                    <input class="input" type="password" autocomplete="current-password" x-model="editForm.current_password">
                </div>
                <div>
                    <label class="text-xs text-slate-400 block mb-1.5">
                        New password <span class="text-slate-600">(leave empty to keep the current one)</span>
                    </label>
                    <input class="input" type="password" autocomplete="new-password" placeholder="At least 8 characters" x-model="editForm.password">
                </div>
                <label class="flex items-start gap-2.5 text-sm text-slate-300 cursor-pointer">
                    <input type="checkbox" class="accent-[rgb(var(--brand))] mt-0.5" x-model="editForm.notify">
                    <span>
                        Inform the user by email
                        <span class="block text-[11px] text-slate-500">
                            Sends their updated details<span x-show="editForm.password">, including the new password</span>.
                        </span>
                    </span>
                </label>
            </div>
            <div x-show="error" x-cloak class="mt-4 rounded-xl border border-red-500/40 bg-red-500/10 text-red-400 text-sm px-4 py-3" x-text="error"></div>
            <div class="flex gap-2 mt-6">
                <button type="button" @click="closeModal()" :disabled="busy"
                    class="flex-1 rounded-xl py-2.5 text-sm font-semibold border border-ink-700 text-slate-300 hover:border-brand/60 transition">
                    Cancel
                </button>
                <button type="button" @click="saveEdit()" :disabled="busy" class="btn-brand flex-1">
                    <span x-show="!busy">Save changes</span>
                    <span x-show="busy" x-cloak class="inline-flex items-center gap-2">
                        <span class="inline-block animate-spin"><x-icon name="Loader2" :size="14" /></span> Saving…
                    </span>
                </button>
            </div>
        </div>
    </div>
</template>
    </div>

@push('scripts')
<script>
function adminCustomers() {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const H = { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf };
    const errText = (data) => data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Something went wrong.');

    return {
        rows: [], packages: [], search: '',
        modal: null, target: null, error: '', flash: '', informing: null, busy: false,
        createForm: { name: '', email: '', password: '' },
        assignForm: { package_id: '', months: 1 },
        creditsForm: { feature_key: 'image_generation_credits', bonus: 10 },
        editForm: { first_name: '', last_name: '', email: '', password: '', current_password: '', notify: true },

        async load() {
            const res = await fetch(`/api/admin/customers?search=${encodeURIComponent(this.search)}`, { headers: { 'Accept': 'application/json' } });
            if (res.ok) { const page = await res.json(); this.rows = page.data ?? []; }
        },
        async loadPackages() {
            const res = await fetch('/api/admin/packages', { headers: { 'Accept': 'application/json' } });
            if (res.ok) this.packages = await res.json();
        },

        resetCreate() { this.createForm = { name: '', email: '', password: '' }; this.error = ''; },
        openAssign(u) { this.target = u; this.assignForm = { package_id: this.packages.find((p) => p.status === 'active')?.id ?? '', months: 1 }; this.error = ''; this.modal = 'assign'; },
        openCredits(u) { this.target = u; this.creditsForm = { feature_key: 'image_generation_credits', bonus: 10 }; this.error = ''; this.modal = 'credits'; },
        openEdit(u) {
            this.target = u;
            const [first = '', ...rest] = (u.name ?? '').split(' ');
            this.editForm = { first_name: first, last_name: rest.join(' '), email: u.email ?? '', password: '', current_password: '', notify: true };
            this.error = ''; this.modal = 'edit';
        },
        closeModal() { this.modal = null; this.error = ''; },

        async save(url, method, body) {
            this.error = '';
            try {
                const res = await fetch(url, { method, headers: H, body: JSON.stringify(body) });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) { this.error = errText(data); return; }
                this.closeModal(); this.load();
            } catch (e) { this.error = 'Something went wrong.'; }
        },

        async saveEdit() {
            this.busy = true; this.error = '';
            try {
                const res = await fetch(`/api/admin/customers/${this.target.id}`, { method: 'PUT', headers: H, body: JSON.stringify(this.editForm) });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) { this.error = errText(data); return; }
                this.closeModal(); this.load();
            } catch (e) { this.error = 'Could not save.'; }
            finally { this.busy = false; }
        },

        async setStatus(u, status) {
            await fetch(`/api/admin/customers/${u.id}/status`, { method: 'POST', headers: H, body: JSON.stringify({ status }) });
            this.load();
        },

        async toggleBlock(u) {
            if (!u.is_blocked && !confirm(`Block ${u.name}? They will be signed out and emailed (if enabled).`)) return;
            await fetch(`/api/admin/customers/${u.id}/block`, { method: 'POST', headers: H });
            this.load();
        },

        async informUser(u) {
            this.informing = u.id;
            try {
                const res = await fetch(`/api/admin/customers/${u.id}/notify`, { method: 'POST', headers: H });
                const data = await res.json().catch(() => ({}));
                this.flash = res.ok ? data.message : (data.message || 'Could not send the email.');
                setTimeout(() => { this.flash = ''; }, res.ok ? 4000 : 5000);
            } catch (e) {
                this.flash = 'Could not send the email.';
                setTimeout(() => { this.flash = ''; }, 5000);
            } finally {
                this.informing = null;
            }
        },
    };
}
</script>
@endpush
@endsection
