@extends('layouts.admin')

@section('content')
<div x-data="adminSubs()" x-init="load()">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <h1 class="font-display text-xl sm:text-2xl font-bold text-white">Subscriptions</h1>
        <select class="input !w-44" x-model="status" @change="load()">
            <option value="">All statuses</option>
            <option value="active">Active</option>
            <option value="canceled">Canceled</option>
            <option value="past_due">Past due</option>
            <option value="expired">Expired</option>
        </select>
    </div>
    <div class="card overflow-x-auto">
        <table class="w-full text-sm min-w-[640px]">
            <thead class="text-left text-slate-400 border-b border-ink-700/60">
                <tr>
                    <th class="p-4">Customer</th>
                    <th class="p-4">Package</th>
                    <th class="p-4">Gateway</th>
                    <th class="p-4">Status</th>
                    <th class="p-4">Expires</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="sub in rows" :key="sub.id">
                    <tr class="border-b border-ink-700/40 last:border-0">
                        <td class="p-4">
                            <p class="text-white" x-text="sub.user?.name"></p>
                            <p class="text-slate-500 text-xs" x-text="sub.user?.email"></p>
                        </td>
                        <td class="p-4 text-slate-300">
                            <span x-text="sub.package?.name"></span>
                            <span class="text-slate-500" x-text="` ($${sub.package?.price}/${sub.package?.billing_cycle === 'yearly' ? 'yr' : 'mo'})`"></span>
                        </td>
                        <td class="p-4 text-slate-400 capitalize" x-text="sub.gateway"></td>
                        <td class="p-4">
                            <span class="text-[11px] uppercase tracking-wider rounded-full px-2.5 py-1 border"
                                :class="{
                                    active: 'text-brand border-brand/40',
                                    canceled: 'text-red-400 border-red-400/40',
                                    past_due: 'text-amber-400 border-amber-400/40',
                                }[sub.status] ?? 'text-slate-500 border-ink-700'"
                                x-text="sub.status"></span>
                        </td>
                        <td class="p-4 text-slate-400" x-text="sub.expires_at ? new Date(sub.expires_at).toLocaleDateString() : '—'"></td>
                    </tr>
                </template>
                <tr x-show="loaded && rows.length === 0" x-cloak>
                    <td colspan="5" class="p-8 text-center text-slate-500">No subscriptions found.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
function adminSubs() {
    return {
        rows: [], status: '', loaded: false,
        async load() {
            const res = await fetch(`/api/admin/subscriptions${this.status ? `?status=${this.status}` : ''}`, { headers: { 'Accept': 'application/json' } });
            if (res.ok) { const p = await res.json(); this.rows = p.data ?? []; }
            this.loaded = true;
        },
    };
}
</script>
@endpush
@endsection
