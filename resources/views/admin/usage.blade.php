@extends('layouts.admin')

@section('content')
<div x-data="adminUsage()" x-init="load(); loadTools()">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <h1 class="font-display text-xl sm:text-2xl font-bold text-white">Usage logs</h1>
        <select class="input !w-56" x-model="tool" @change="load()">
            <option value="">All tools</option>
            <template x-for="t in tools" :key="t.slug">
                <option :value="t.slug" x-text="t.name"></option>
            </template>
        </select>
    </div>
    <div class="card overflow-x-auto">
        <table class="w-full text-sm min-w-[640px]">
            <thead class="text-left text-slate-400 border-b border-ink-700/60">
                <tr>
                    <th class="p-4">When</th>
                    <th class="p-4">Customer</th>
                    <th class="p-4">Tool</th>
                    <th class="p-4 text-right">Credits</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="log in rows" :key="log.id">
                    <tr class="border-b border-ink-700/40 last:border-0">
                        <td class="p-4 text-slate-400 whitespace-nowrap" x-text="new Date(log.created_at).toLocaleString()"></td>
                        <td class="p-4">
                            <p class="text-white" x-text="log.user?.name ?? '—'"></p>
                            <p class="text-slate-500 text-xs" x-text="log.user?.email"></p>
                        </td>
                        <td class="p-4 text-slate-300" x-text="log.tool_slug"></td>
                        <td class="p-4 text-right text-slate-300" x-text="Number(log.amount).toLocaleString()"></td>
                    </tr>
                </template>
                <tr x-show="loaded && rows.length === 0" x-cloak>
                    <td colspan="4" class="p-8 text-center text-slate-500">No usage recorded yet.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
function adminUsage() {
    return {
        rows: [], tools: [], tool: '', loaded: false,
        async load() {
            const res = await fetch(`/api/admin/usage-logs${this.tool ? `?tool=${this.tool}` : ''}`, { headers: { 'Accept': 'application/json' } });
            if (res.ok) { const p = await res.json(); this.rows = p.data ?? []; }
            this.loaded = true;
        },
        async loadTools() {
            const res = await fetch('/api/admin/tools', { headers: { 'Accept': 'application/json' } });
            if (res.ok) this.tools = await res.json();
        },
    };
}
</script>
@endpush
@endsection
