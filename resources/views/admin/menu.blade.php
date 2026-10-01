@extends('layouts.admin')

@section('content')
<div class="w-full" x-data="adminMenu()" x-init="load()">
    <h1 class="font-display text-xl sm:text-2xl font-bold text-white mb-1">Menu</h1>
    <p class="text-slate-400 text-sm mb-8">
        Build the site navigation like in WordPress: add core pages, your own pages, or custom
        links; drag rows by the grip to reorder (or use the arrows); nest an item under another to create a dropdown submenu.
    </p>

    <div class="flex justify-end mb-4">
        <button type="button" class="btn-brand !py-2" @click="adding = true; addError = ''"><x-icon name="Plus" :size="15" /> Add menu item</button>
    </div>

    <div class="space-y-2">
        <template x-for="item in menu" :key="item.id">
            <div>
                {{-- Top-level row --}}
                <div class="card p-3 flex flex-wrap items-center gap-2 transition"
                    :class="dragId === item.id ? 'opacity-40 border-brand/60' : ''"
                    draggable="true"
                    @dragstart="dragId = item.id"
                    @dragover.prevent
                    @drop="dropOn(item, null)">
                    <x-icon name="GripVertical" :size="15" class="text-slate-500 shrink-0 cursor-grab" />
                    <input class="input !w-40 !py-1.5 text-sm" :value="item.label"
                        @blur="$event.target.value !== item.label && update(item, { label: $event.target.value })">
                    <span class="text-xs text-slate-500 flex-1 min-w-24 truncate"
                        x-text="item.type === 'page' ? `page: /p/${item.target}` : item.target"></span>

                    <select class="input !w-36 !py-1.5 text-xs" x-show="(item.children ?? []).length === 0"
                        @change="$event.target.value && update(item, { parent_id: parseInt($event.target.value, 10) })">
                        <option value="">Nest under…</option>
                        <template x-for="p in menu.filter((m) => m.id !== item.id)" :key="p.id">
                            <option :value="p.id" x-text="p.label"></option>
                        </template>
                    </select>

                    <button type="button" class="p-1.5 rounded-lg border border-ink-700 text-slate-400 hover:text-brand" @click="move(item, 'up')"><x-icon name="ArrowUp" :size="13" /></button>
                    <button type="button" class="p-1.5 rounded-lg border border-ink-700 text-slate-400 hover:text-brand" @click="move(item, 'down')"><x-icon name="ArrowDown" :size="13" /></button>
                    <button type="button" @click="update(item, { enabled: !item.enabled })"
                        class="w-20 text-center text-[11px] uppercase tracking-wider rounded-full px-2 py-1.5 border"
                        :class="item.enabled ? 'text-brand border-brand/40' : 'text-slate-500 border-ink-700'"
                        x-text="item.enabled ? 'Shown' : 'Hidden'"></button>
                    <button type="button" class="p-1.5 rounded-lg border border-ink-700 text-slate-400 hover:text-red-400" @click="remove(item)"><x-icon name="Trash2" :size="13" /></button>
                </div>

                {{-- Children --}}
                <template x-for="child in item.children ?? []" :key="child.id">
                    <div class="ml-8 mt-2 flex items-start gap-2">
                        <x-icon name="CornerDownRight" :size="15" class="text-slate-600 mt-4 shrink-0" />
                        <div class="flex-1">
                            <div class="card p-3 flex flex-wrap items-center gap-2 transition"
                                :class="dragId === child.id ? 'opacity-40 border-brand/60' : ''"
                                draggable="true"
                                @dragstart="dragId = child.id"
                                @dragover.prevent
                                @drop="dropOn(child, item.id)">
                                <x-icon name="GripVertical" :size="15" class="text-slate-500 shrink-0 cursor-grab" />
                                <input class="input !w-40 !py-1.5 text-sm" :value="child.label"
                                    @blur="$event.target.value !== child.label && update(child, { label: $event.target.value })">
                                <span class="text-xs text-slate-500 flex-1 min-w-24 truncate"
                                    x-text="child.type === 'page' ? `page: /p/${child.target}` : child.target"></span>
                                <button type="button" class="text-xs text-slate-400 hover:text-brand" @click="update(child, { parent_id: null })">Un-nest</button>
                                <button type="button" class="p-1.5 rounded-lg border border-ink-700 text-slate-400 hover:text-brand" @click="move(child, 'up')"><x-icon name="ArrowUp" :size="13" /></button>
                                <button type="button" class="p-1.5 rounded-lg border border-ink-700 text-slate-400 hover:text-brand" @click="move(child, 'down')"><x-icon name="ArrowDown" :size="13" /></button>
                                <button type="button" @click="update(child, { enabled: !child.enabled })"
                                    class="w-20 text-center text-[11px] uppercase tracking-wider rounded-full px-2 py-1.5 border"
                                    :class="child.enabled ? 'text-brand border-brand/40' : 'text-slate-500 border-ink-700'"
                                    x-text="child.enabled ? 'Shown' : 'Hidden'"></button>
                                <button type="button" class="p-1.5 rounded-lg border border-ink-700 text-slate-400 hover:text-red-400" @click="remove(child)"><x-icon name="Trash2" :size="13" /></button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </template>
    </div>

    {{-- Add item modal --}}
    <template x-teleport="body">
    <div x-show="adding" x-cloak class="fixed inset-0 z-50 grid place-items-center p-4 bg-ink-950/60 backdrop-blur-md" @click="adding = false">
        <div class="relative card p-7 w-full max-w-md animate-pop-in" @click.stop>
            <button type="button" @click="adding = false" aria-label="Close"
                class="absolute top-4 right-4 p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-ink-800 transition">
                <x-icon name="X" :size="18" />
            </button>
            <h2 class="font-display font-semibold text-white mb-5">Add menu item</h2>

            <div class="flex rounded-xl border border-ink-700 overflow-hidden mb-4">
                <template x-for="[v, l] in [['page', 'Site page'], ['core', 'Core page'], ['link', 'Custom link']]" :key="v">
                    <button type="button" @click="addType = v; addForm.target = ''"
                        class="flex-1 px-3 py-2 text-xs"
                        :class="addType === v ? 'bg-brand text-ink-950 font-semibold' : 'text-slate-300 hover:bg-ink-800'"
                        x-text="l"></button>
                </template>
            </div>

            <div class="space-y-3">
                <select class="input" x-show="addType === 'page'" x-model="addForm.target">
                    <option value="">Choose a page…</option>
                    <template x-for="p in pages" :key="p.id">
                        <option :value="p.slug" x-text="p.title"></option>
                    </template>
                </select>
                <select class="input" x-show="addType === 'core'" x-model="addForm.target">
                    <option value="">Choose a core page…</option>
                    <template x-for="c in CORE" :key="c.target">
                        <option :value="c.target" x-text="c.label"></option>
                    </template>
                </select>
                <input class="input" x-show="addType === 'link'" placeholder="https://example.com" x-model="addForm.target">
                <input class="input" placeholder="Label (shown in the menu)" x-model="addForm.label">
                <select class="input" x-model="addForm.parent_id">
                    <option value="">Top level</option>
                    <template x-for="m in menu" :key="m.id">
                        <option :value="m.id" x-text="`Inside \u201C${m.label}\u201D`"></option>
                    </template>
                </select>
                <p class="text-sm text-red-400" x-show="addError" x-text="addError" x-cloak></p>
                <button type="button" class="btn-brand w-full" @click="add()">Add to menu</button>
            </div>
        </div>
    </div>
    </template>
</div>

@push('scripts')
<script>
function adminMenu() {
    const H = { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content };
    return {
        CORE: [
            { label: 'Home', target: '/' }, { label: 'Tools', target: '/tools' },
            { label: 'Pricing', target: '/pricing' }, { label: 'Contact', target: '/contact' },
        ],
        menu: [], pages: [], dragId: null,
        adding: false, addType: 'page', addForm: { label: '', target: '', parent_id: '' }, addError: '',

        async load() {
            const res = await fetch('/api/admin/menu', { headers: H });
            if (res.ok) this.menu = await res.json();
            const pr = await fetch('/api/admin/pages', { headers: H }).catch(() => null);
            if (pr && pr.ok) this.pages = await pr.json();
        },
        flatAll() { return this.menu.flatMap((m) => [m, ...(m.children ?? [])]); },

        async dropOn(overItem, parentId) {
            if (!this.dragId || this.dragId === overItem.id) { this.dragId = null; return; }
            const siblings = (parentId
                ? (this.menu.find((m) => m.id === parentId)?.children ?? [])
                : this.menu
            ).filter((s) => s.id !== this.dragId);
            const overIndex = siblings.findIndex((s) => s.id === overItem.id);
            const dragged = this.flatAll().find((s) => s.id === this.dragId);
            if (!dragged) { this.dragId = null; return; }
            siblings.splice(overIndex, 0, dragged);
            const res = await fetch('/api/admin/menu/reorder', {
                method: 'POST', headers: H,
                body: JSON.stringify({ items: siblings.map((s, i) => ({ id: s.id, sort_order: i + 1, parent_id: parentId ?? null })) }),
            });
            if (!res.ok) { const d = await res.json().catch(() => ({})); alert(d.message || 'Could not reorder.'); }
            this.dragId = null;
            this.load();
        },

        async update(item, body) {
            const res = await fetch(`/api/admin/menu/${item.id}`, { method: 'PUT', headers: H, body: JSON.stringify(body) });
            if (!res.ok) { const d = await res.json().catch(() => ({})); alert(d.message || 'Could not update.'); }
            this.load();
        },
        async move(item, direction) {
            await fetch(`/api/admin/menu/${item.id}/move`, { method: 'POST', headers: H, body: JSON.stringify({ direction }) });
            this.load();
        },
        async remove(item) {
            if (!confirm(`Remove "${item.label}" from the menu? (Pages themselves are not deleted.)`)) return;
            await fetch(`/api/admin/menu/${item.id}`, { method: 'DELETE', headers: H });
            this.load();
        },

        async add() {
            this.addError = '';
            let target = this.addForm.target;
            let label = this.addForm.label;
            if (this.addType === 'core') {
                const core = this.CORE.find((c) => c.target === target);
                if (!core) { this.addError = 'Pick a core page.'; return; }
                label = label || core.label;
            }
            if (this.addType === 'page') {
                const page = this.pages.find((p) => p.slug === target);
                if (!page) { this.addError = 'Pick a page.'; return; }
                label = label || page.title;
            }
            if (this.addType === 'link' && !/^https?:\/\//i.test(target)) {
                this.addError = 'Custom links must start with http:// or https://'; return;
            }
            if (!label) { this.addError = 'Give the item a label.'; return; }
            const res = await fetch('/api/admin/menu', {
                method: 'POST', headers: H,
                body: JSON.stringify({ label, type: this.addType, target, parent_id: this.addForm.parent_id || null }),
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) { this.addError = data.message || 'Could not add.'; return; }
            this.adding = false;
            this.addForm = { label: '', target: '', parent_id: '' };
            this.load();
        },
    };
}
</script>
@endpush
@endsection
