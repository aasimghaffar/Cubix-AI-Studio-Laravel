@extends('layouts.admin')

@section('content')
<div x-data="adminPages()" x-init="load()">
    <h1 class="font-display text-xl sm:text-2xl font-bold text-white mb-1">Pages</h1>
    <p class="text-slate-400 text-sm mb-8">
        Terms, Privacy Policy, and any custom pages. Style the text with the toolbar — headings,
        bullet points, font sizes, links, and open/close sections. Add pages to the navigation
        from the <strong class="text-slate-300">Menu</strong> tab.
    </p>

    <div class="grid lg:grid-cols-[260px_1fr] gap-6 items-start">
        {{-- Page list --}}
        <div class="card p-3">
            <template x-for="page in pages" :key="page.id">
                <div class="flex items-center gap-2 rounded-xl px-3 py-2.5 cursor-pointer"
                    :class="selected?.id === page.id ? 'bg-brand/10 text-brand' : 'text-slate-300 hover:bg-ink-800'"
                    @click="select(page)">
                    <x-icon name="FileText" :size="15" class="shrink-0" />
                    <span class="flex-1 text-sm truncate" x-text="page.title"></span>
                    <span x-show="!page.published" title="Hidden"><x-icon name="EyeOff" :size="13" class="text-slate-500" /></span>
                </div>
            </template>
            <button type="button" @click="create()" class="w-full mt-2 btn-ghost !py-2 text-sm"><x-icon name="Plus" :size="15" /> New page</button>
        </div>

        {{-- Editor --}}
        <div>
            <template x-if="selected">
                <div class="space-y-4">
                    <div class="flex flex-wrap items-center gap-3">
                        <input class="input flex-1 !w-auto min-w-40 font-display font-semibold" x-model="title">
                        <select x-model="layout" @change="saved = false" title="Page width"
                            class="bg-ink-800 border border-ink-700 rounded-xl px-3 py-2.5 text-sm text-slate-200">
                            <option value="narrow">Narrow — reading width (Terms, Privacy)</option>
                            <option value="wide">Wide — landing width (FAQ, features)</option>
                            <option value="full">Full width — edge to edge</option>
                        </select>
                        <a :href="'/p/' + selected.slug" target="_blank" rel="noreferrer" class="btn-ghost !py-2 text-xs" title="View on site">
                            <x-icon name="ExternalLink" :size="14" /> <span x-text="'/p/' + selected.slug"></span>
                        </a>
                        <button type="button" @click="togglePublish(selected)" class="btn-ghost !py-2 text-xs">
                            <span x-show="selected.published" class="inline-flex items-center gap-1.5"><x-icon name="EyeOff" :size="14" /> Unpublish</span>
                            <span x-show="!selected.published" class="inline-flex items-center gap-1.5"><x-icon name="Eye" :size="14" /> Publish</span>
                        </button>
                        <button type="button" x-show="!selected.is_system" @click="remove(selected)" class="btn-ghost !py-2 text-xs hover:!text-red-400">
                            <x-icon name="Trash2" :size="14" />
                        </button>
                    </div>

                    {{-- Rich text editor (port of RichEditor.jsx — plain HTML output) --}}
                    <div class="rounded-2xl border border-ink-700 overflow-hidden">
                        <div class="flex flex-wrap items-center gap-0.5 border-b border-ink-700 bg-ink-800/60 px-2 py-1.5">
                            <button type="button" class="re-btn" title="Bold" @click="cmd('bold')"><x-icon name="Bold" :size="15" /></button>
                            <button type="button" class="re-btn" title="Italic" @click="cmd('italic')"><x-icon name="Italic" :size="15" /></button>
                            <button type="button" class="re-btn" title="Underline" @click="cmd('underline')"><x-icon name="Underline" :size="15" /></button>
                            <span class="w-px h-5 bg-ink-700 mx-1"></span>
                            <button type="button" class="re-btn" title="Heading" @click="cmd('formatBlock', '<h2>')"><x-icon name="Heading2" :size="15" /></button>
                            <button type="button" class="re-btn" title="Sub-heading" @click="cmd('formatBlock', '<h3>')"><x-icon name="Heading3" :size="15" /></button>
                            <button type="button" class="re-btn" title="Normal paragraph" @click="cmd('formatBlock', '<p>')"><x-icon name="Pilcrow" :size="15" /></button>
                            <span class="w-px h-5 bg-ink-700 mx-1"></span>
                            <button type="button" class="re-btn" title="Bullet list" @click="cmd('insertUnorderedList')"><x-icon name="List" :size="15" /></button>
                            <button type="button" class="re-btn" title="Numbered list" @click="cmd('insertOrderedList')"><x-icon name="ListOrdered" :size="15" /></button>
                            <span class="w-px h-5 bg-ink-700 mx-1"></span>
                            <span class="inline-flex items-center gap-1 px-1" title="Font size">
                                <x-icon name="Type" :size="14" class="text-slate-400" />
                                <select class="bg-transparent text-xs text-slate-300 outline-none cursor-pointer"
                                    @change="if ($event.target.value) { cmd('fontSize', $event.target.value); $event.target.value = '' }">
                                    <option value="" disabled selected>Size</option>
                                    <option value="2">Small</option>
                                    <option value="3">Normal</option>
                                    <option value="5">Large</option>
                                    <option value="6">Huge</option>
                                </select>
                            </span>
                            <span class="w-px h-5 bg-ink-700 mx-1"></span>
                            <button type="button" class="re-btn" title="Insert link" @click="insertLink()"><x-icon name="Link2" :size="15" /></button>
                            <button type="button" class="re-btn inline-flex items-center gap-1.5 !px-2.5 text-xs"
                                title="Insert an open/close section" @click="insertAccordion()">
                                <x-icon name="ChevronDownSquare" :size="15" /> Open/close section
                            </button>
                            <span class="flex-1"></span>
                            <button type="button" class="re-btn" title="Clear formatting" @click="cmd('removeFormat')"><x-icon name="RemoveFormatting" :size="15" /></button>
                        </div>
                        <div x-ref="canvas" contenteditable="true"
                            class="rich-editor-canvas p-5 text-sm text-slate-200 bg-ink-900/60"
                            @input="content = $refs.canvas.innerHTML; saved = false"
                            @blur="content = $refs.canvas.innerHTML"></div>
                    </div>

                    <button type="button" class="btn-brand" @click="save()" :disabled="busy"
                        x-text="busy ? 'Saving…' : saved ? 'Saved ✓' : 'Save page'"></button>
                </div>
            </template>
            <template x-if="!selected">
                <div class="card p-14 text-center text-slate-500 text-sm">Select a page to edit.</div>
            </template>
        </div>
    </div>
</div>

@push('scripts')
<script>
function adminPages() {
    const H = { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content };
    return {
        pages: [], selected: null, title: '', content: '', layout: 'narrow', saved: false, busy: false,
        async load() {
            const res = await fetch('/api/admin/pages', { headers: H });
            if (!res.ok) return;
            this.pages = await res.json();
            if (!this.selected && this.pages.length) this.select(this.pages[0]);
        },
        select(page) {
            this.selected = page;
            this.title = page.title;
            this.content = page.content ?? '';
            this.layout = page.layout ?? 'narrow';
            this.saved = false;
            this.$nextTick(() => { if (this.$refs.canvas) this.$refs.canvas.innerHTML = this.content; });
        },
        cmd(command, arg = null) {
            this.$refs.canvas.focus();
            document.execCommand(command, false, arg);
            this.content = this.$refs.canvas.innerHTML;
            this.saved = false;
        },
        insertLink() {
            const url = prompt('Link URL (https://…):');
            if (url) this.cmd('createLink', url);
        },
        insertAccordion() {
            this.$refs.canvas.focus();
            document.execCommand('insertHTML', false,
                '<details><summary>Section title — click to open/close</summary><p>Section content…</p></details><p><br></p>');
            this.content = this.$refs.canvas.innerHTML;
            this.saved = false;
        },
        async save() {
            this.busy = true; this.saved = false;
            const res = await fetch(`/api/admin/pages/${this.selected.id}`, {
                method: 'PUT', headers: H,
                body: JSON.stringify({ title: this.title, content: this.content, layout: this.layout }),
            });
            this.busy = false;
            if (!res.ok) return;
            const updated = await res.json();
            this.saved = true;
            this.pages = this.pages.map((x) => x.id === updated.id ? updated : x);
            this.selected = updated;
            setTimeout(() => { this.saved = false; }, 2500);
        },
        async create() {
            const name = prompt('Page title:');
            if (!name) return;
            const res = await fetch('/api/admin/pages', {
                method: 'POST', headers: H,
                body: JSON.stringify({ title: name, content: `<h2>${name}</h2><p>Write your content here…</p>` }),
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) { alert(data.message || 'Could not create the page.'); return; }
            await this.load();
            this.select(data);
        },
        async togglePublish(page) {
            const res = await fetch(`/api/admin/pages/${page.id}`, {
                method: 'PUT', headers: H, body: JSON.stringify({ published: !page.published }),
            });
            if (!res.ok) return;
            const updated = await res.json();
            this.pages = this.pages.map((x) => x.id === updated.id ? updated : x);
            if (this.selected?.id === updated.id) this.selected = updated;
        },
        async remove(page) {
            if (!confirm(`Delete "${page.title}"? It will also be removed from the menu.`)) return;
            const res = await fetch(`/api/admin/pages/${page.id}`, { method: 'DELETE', headers: H });
            if (!res.ok) { const d = await res.json().catch(() => ({})); alert(d.message || 'Could not delete.'); return; }
            this.selected = null;
            this.load();
        },
    };
}
</script>
@endpush
@endsection
