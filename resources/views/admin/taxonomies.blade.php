@extends('layouts.admin')

@section('content')
<div class="w-full" x-data="adminTaxonomies()" x-init="load()">
    <h1 class="font-display text-xl sm:text-2xl font-bold text-white mb-1">Tool categories</h1>
    <p class="text-slate-400 text-sm mb-8">
        Group your AI tools into categories — the public Tools page shows them as filter tabs and
        grouped sections. Assign each tool to a category in <strong class="text-slate-300">AI Settings → AI tools</strong>.
    </p>

    <div class="flex gap-3 mb-6">
        <input class="input flex-1" placeholder="New category name (e.g. Creative Studio)" x-model="name" @keydown.enter="add()">
        <button type="button" class="btn-brand" @click="add()"><x-icon name="Plus" :size="15" /> Add</button>
    </div>
    <p class="text-sm text-red-400 mb-4" x-show="error" x-text="error" x-cloak></p>

    <div class="space-y-2">
        <template x-for="(cat, i) in cats" :key="cat.id">
            <div class="card p-3.5 flex items-center gap-3">
                <x-icon name="Tags" :size="15" class="text-brand shrink-0" />
                <input class="input !py-1.5 flex-1" :value="cat.name" @blur="rename(cat, $event.target.value.trim())">
                <span class="text-xs text-slate-500 shrink-0" x-text="`${cat.tools_count} tool${cat.tools_count === 1 ? '' : 's'}`"></span>
                <button type="button" class="p-1.5 rounded-lg border border-ink-700 text-slate-400 hover:text-brand" @click="move(i, 'up')"><x-icon name="ArrowUp" :size="13" /></button>
                <button type="button" class="p-1.5 rounded-lg border border-ink-700 text-slate-400 hover:text-brand" @click="move(i, 'down')"><x-icon name="ArrowDown" :size="13" /></button>
                <button type="button" class="p-1.5 rounded-lg border border-ink-700 text-slate-400 hover:text-red-400" @click="remove(cat)"><x-icon name="Trash2" :size="13" /></button>
            </div>
        </template>
        <p class="text-sm text-slate-500" x-show="loaded && cats.length === 0" x-cloak>
            No categories yet — run <code class="text-slate-400">php artisan db:seed --class=DemoDataSeeder</code> for a ready-made set, or add your own above.
        </p>
    </div>
</div>

@push('scripts')
<script>
function adminTaxonomies() {
    const H = { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content };
    return {
        cats: [], name: '', error: '', loaded: false,
        async load() {
            const res = await fetch('/api/admin/taxonomies', { headers: H });
            if (res.ok) this.cats = await res.json();
            this.loaded = true;
        },
        async add() {
            if (!this.name.trim()) return;
            this.error = '';
            const res = await fetch('/api/admin/taxonomies', { method: 'POST', headers: H, body: JSON.stringify({ name: this.name.trim() }) });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) { this.error = data.message || 'Could not add.'; return; }
            this.name = ''; this.load();
        },
        async rename(cat, newName) {
            if (!newName || newName === cat.name) return;
            await fetch(`/api/admin/taxonomies/${cat.id}`, { method: 'PUT', headers: H, body: JSON.stringify({ name: newName }) });
            this.load();
        },
        async move(i, dir) {
            const next = [...this.cats];
            const swap = dir === 'up' ? i - 1 : i + 1;
            if (swap < 0 || swap >= next.length) return;
            [next[i], next[swap]] = [next[swap], next[i]];
            await Promise.all(next.map((c, idx) =>
                fetch(`/api/admin/taxonomies/${c.id}`, { method: 'PUT', headers: H, body: JSON.stringify({ sort_order: idx + 1 }) })));
            this.load();
        },
        async remove(cat) {
            if (!confirm(`Delete "${cat.name}"? Tools in it will show under "General".`)) return;
            await fetch(`/api/admin/taxonomies/${cat.id}`, { method: 'DELETE', headers: H });
            this.load();
        },
    };
}
</script>
@endpush
@endsection
