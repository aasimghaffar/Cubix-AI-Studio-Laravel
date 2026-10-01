@extends('layouts.admin')

@section('content')
<div class="w-full" x-data="adminLanguages()" x-init="load()">
    <h1 class="font-display text-xl sm:text-2xl font-bold text-white mb-1">Languages</h1>
    <p class="text-slate-400 text-sm mb-6">
        Choose which languages visitors can switch to, where the switcher appears, and add
        your own languages. English is the fallback and can't be disabled.
    </p>

    {{-- Automatic whole-site translation --}}
    <div class="card gradient-ring p-6 mb-5">
        <h2 class="font-display font-semibold text-white mb-1">Automatic translation — no files needed</h2>
        <p class="text-xs text-slate-500 mb-4 leading-relaxed">
            Every language below has an <strong class="text-slate-300">Auto-translate</strong> button: one click
            machine-translates <strong class="text-slate-300">the entire site</strong> into that language and saves it —
            nothing to edit by hand. When you rename a tool or change its description, enabled languages are
            re-translated automatically too. New languages you add are filled in the same way.
            (Advanced: you can still download the full text as a file below, polish any wording, and upload it back —
            your edits are kept.)
        </p>
        <button type="button" class="btn-ghost !py-2 text-xs" @click="downloadTemplate()">
            Download all site text (optional)
        </button>
    </div>

    {{-- Switcher placement --}}
    <div class="card p-6 mb-5">
        <h2 class="font-display font-semibold text-white mb-1">Language switcher placement</h2>
        <p class="text-xs text-slate-500 mb-4">
            Where the <x-icon name="Globe" :size="11" class="inline" /> language icon appears on the site
            <span class="text-brand" x-show="posSaved" x-cloak>— saved ✓</span>
        </p>
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
            @foreach ([
                ['header', 'Header'], ['footer', 'Footer'], ['both', 'Header + footer'],
                ['float', 'Floating round button'], ['off', 'Disabled (hide switcher)'],
            ] as [$val, $label])
                <button type="button" @click="savePosition('{{ $val }}')"
                    class="rounded-xl border px-3 py-2.5 text-sm transition"
                    :class="position === '{{ $val }}' ? 'border-brand bg-brand/10 text-brand' : 'border-ink-700 text-slate-300 hover:border-brand/40'">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <h2 class="font-display font-semibold text-white">Available languages</h2>
        <div class="flex gap-2">
            <label class="btn-ghost !py-2 cursor-pointer">
                <x-icon name="Upload" :size="15" /> Upload language file
                <input type="file" accept=".json,application/json" class="hidden" @change="uploadFile($event)">
            </label>
            <button type="button" class="btn-brand !py-2" @click="openAdd()"><x-icon name="Plus" :size="15" /> Add language</button>
        </div>
    </div>

    <div class="space-y-3">
        <template x-for="lang in languages" :key="lang.id">
            <div class="card p-4 flex items-center gap-4">
                <span class="font-display font-bold text-brand w-10 uppercase" x-text="lang.code"></span>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-sm"><span x-text="lang.name"></span> <span class="text-slate-500" x-text="'· ' + lang.native_name"></span></p>
                    <p class="text-xs text-slate-500" x-text="statsLine(lang)"></p>
                </div>
                <button type="button" x-show="lang.code !== 'en'" @click="autoTranslate(lang)" :disabled="translating !== null"
                    class="inline-flex items-center gap-1.5 text-xs px-3 py-2 rounded-lg border border-brand/40 text-brand hover:bg-brand/10 transition disabled:opacity-50">
                    <x-icon name="Wand2" :size="13" />
                    <span x-text="translating === lang.id ? 'Translating…' : 'Auto-translate'"></span>
                </button>
                <button type="button" title="Download as file (edit offline, upload again)" @click="downloadLang(lang)"
                    class="p-2 rounded-lg border border-ink-700 text-slate-400 hover:text-brand">
                    <x-icon name="Upload" :size="14" class="rotate-180" />
                </button>
                <button type="button" title="Edit translations" @click="openEdit(lang)"
                    class="p-2 rounded-lg border border-ink-700 text-slate-400 hover:text-brand">
                    <x-icon name="Pencil" :size="14" />
                </button>
                <button type="button" x-show="lang.is_custom" title="Delete" @click="remove(lang)"
                    class="p-2 rounded-lg border border-ink-700 text-slate-400 hover:text-red-400">
                    <x-icon name="Trash2" :size="14" />
                </button>
                <button type="button" @click="toggle(lang)" :disabled="lang.code === 'en'"
                    class="w-24 text-center text-xs uppercase tracking-wider rounded-full px-3 py-1.5 border transition disabled:opacity-50"
                    :class="lang.enabled ? 'text-brand border-brand/40' : 'text-slate-500 border-ink-700'"
                    x-text="lang.enabled ? 'Enabled' : 'Disabled'"></button>
            </div>
        </template>
    </div>

    {{-- Add / edit language modal --}}
    <template x-teleport="body">
    <div x-show="modal" x-cloak class="fixed inset-0 z-50 grid place-items-center p-4 bg-ink-950/60 backdrop-blur-md" @click="modal = null">
        <div class="relative card p-7 w-full max-w-2xl max-h-[90vh] overflow-y-auto animate-pop-in" @click.stop>
            <button type="button" @click="modal = null" aria-label="Close"
                class="absolute top-4 right-4 p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-ink-800">
                <x-icon name="X" :size="18" />
            </button>
            <h2 class="font-display font-semibold text-white mb-1" x-text="modal?.lang ? `Edit ${modal.lang.name}` : 'Add a language'"></h2>
            <p class="text-sm text-slate-400 mb-5"
                x-text="modal?.lang
                    ? 'Edit the translated strings below. Missing keys fall back to English automatically.'
                    : 'The translations start as a copy of English — translate each value on the right side of the colons.'"></p>

            <div class="grid sm:grid-cols-2 gap-4 mb-4">
                <input class="input" placeholder="Code (e.g. de, tr, ur)" :disabled="!!modal?.lang"
                    :value="form.code" @input="form.code = $event.target.value.toLowerCase()">
                <select class="input" x-model="form.dir">
                    <option value="ltr">Left to right (LTR)</option>
                    <option value="rtl">Right to left (RTL)</option>
                </select>
                <input class="input" placeholder="English name (e.g. German)" x-model="form.name">
                <input class="input" placeholder="Native name (e.g. Deutsch)" x-model="form.native_name">
            </div>

            <label class="block text-sm text-slate-300 mb-1.5">Translations (key → text)</label>
            <textarea class="input font-mono !text-xs min-h-64" x-model="json" spellcheck="false"></textarea>

            <p class="text-sm text-red-400 mt-3" x-show="error" x-text="error" x-cloak></p>
            <button type="button" class="btn-brand w-full mt-4" @click="save()" :disabled="busy"
                x-text="busy ? 'Saving…' : modal?.lang ? 'Save changes' : 'Add language'"></button>
        </div>
    </div>
    </template>
</div>

@push('scripts')
<script>
function adminLanguages() {
    const H = { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content };
    const downloadJson = (data, filename) => {
        const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = filename;
        a.click();
        URL.revokeObjectURL(a.href);
    };
    return {
        languages: [], position: 'header', posSaved: false, translating: null,
        modal: null, form: { code: '', name: '', native_name: '', dir: 'ltr' }, json: '{}', error: '', busy: false,

        async load() {
            const res = await fetch('/api/admin/languages', { headers: H });
            if (res.ok) this.languages = await res.json();
            const br = await fetch('/api/branding', { headers: H }).catch(() => null);
            if (br && br.ok) { const b = await br.json(); this.position = b.language_switcher ?? 'header'; }
        },

        statsLine(lang) {
            const total = Object.keys(this.languages.find((l) => l.code === 'en')?.translations ?? {}).length;
            const n = Object.keys(lang.translations ?? {}).length;
            let s;
            if (lang.code === 'en' || !total) s = `${n} strings`;
            else s = n >= total ? `${n} strings · fully translated ✓` : `${n} of ${total} translated`;
            return `${lang.dir.toUpperCase()} · ${s}${lang.is_custom ? ' · custom' : ''}`;
        },

        async savePosition(value) {
            this.position = value;
            await fetch('/api/admin/settings', {
                method: 'PUT', headers: H,
                body: JSON.stringify({ settings: [{ key: 'language_switcher', value, group: 'languages' }] }),
            });
            this.posSaved = true;
            setTimeout(() => { this.posSaved = false; }, 2000);
        },

        async autoTranslate(lang) {
            this.translating = lang.id;
            try {
                const res = await fetch(`/api/admin/languages/${lang.id}/auto-translate`, { method: 'POST', headers: H });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) throw new Error(data.message || 'Failed');
                alert(data.message);
                this.load();
            } catch (e) {
                alert('Auto-translate failed: ' + e.message + '\n(The server needs internet access for this.)');
            } finally { this.translating = null; }
        },

        async toggle(lang) {
            const res = await fetch(`/api/admin/languages/${lang.id}`, { method: 'PUT', headers: H, body: JSON.stringify({ enabled: !lang.enabled }) });
            if (!res.ok) { const d = await res.json().catch(() => ({})); alert(d.message || 'Could not update.'); }
            this.load();
        },
        async remove(lang) {
            if (!confirm(`Delete "${lang.name}"?`)) return;
            await fetch(`/api/admin/languages/${lang.id}`, { method: 'DELETE', headers: H });
            this.load();
        },

        async downloadTemplate() {
            const res = await fetch('/api/admin/languages/template', { headers: H });
            if (res.ok) downloadJson(await res.json(), 'translation-template.json');
        },
        downloadLang(lang) {
            downloadJson({ code: lang.code, name: lang.name, native_name: lang.native_name, dir: lang.dir, translations: lang.translations }, `language-${lang.code}.json`);
        },

        uploadFile(e) {
            const file = e.target.files?.[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = () => {
                try {
                    const data = JSON.parse(reader.result);
                    const isFull = data.translations && typeof data.translations === 'object';
                    this.openAdd({
                        code: isFull ? (data.code ?? '') : '',
                        name: isFull ? (data.name ?? '') : '',
                        native_name: isFull ? (data.native_name ?? '') : '',
                        dir: isFull ? (data.dir ?? 'ltr') : 'ltr',
                        translations: isFull ? data.translations : data,
                    });
                } catch {
                    alert('That file is not valid JSON. Export a language, edit it, and upload it again.');
                }
                e.target.value = '';
            };
            reader.readAsText(file);
        },

        openAdd(preset = null) {
            const english = this.languages.find((l) => l.code === 'en');
            this.form = {
                code: preset?.code ?? '',
                name: preset?.name ?? '',
                native_name: preset?.native_name ?? '',
                dir: preset?.dir ?? 'ltr',
            };
            this.json = JSON.stringify(preset?.translations ?? english?.translations ?? {}, null, 2);
            this.error = '';
            this.modal = { lang: null };
        },
        openEdit(lang) {
            this.form = { code: lang.code, name: lang.name, native_name: lang.native_name, dir: lang.dir };
            this.json = JSON.stringify(lang.translations ?? {}, null, 2);
            this.error = '';
            this.modal = { lang };
        },

        async save() {
            this.busy = true; this.error = '';
            let translations;
            try {
                translations = JSON.parse(this.json);
            } catch {
                this.error = 'Translations must be valid JSON (check for a missing comma or quote).';
                this.busy = false;
                return;
            }
            const isEdit = !!this.modal?.lang;
            const url = isEdit ? `/api/admin/languages/${this.modal.lang.id}` : '/api/admin/languages';
            const res = await fetch(url, { method: isEdit ? 'PUT' : 'POST', headers: H, body: JSON.stringify({ ...this.form, translations }) });
            const data = await res.json().catch(() => ({}));
            this.busy = false;
            if (!res.ok) {
                this.error = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Could not save.');
                return;
            }
            this.modal = null;
            this.load();
        },
    };
}
</script>
@endpush
@endsection
