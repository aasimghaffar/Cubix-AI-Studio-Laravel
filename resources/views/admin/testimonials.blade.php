@extends('layouts.admin')

@section('content')
<div class="w-full" x-data="adminTestimonials()" x-init="load()">
    <h1 class="font-display text-xl sm:text-2xl font-bold text-white mb-1">Testimonials</h1>
    <p class="text-slate-400 text-sm mb-8">
        These rotate in the "Loved by creators" section on the homepage. Add real customer
        feedback here — hidden ones stay saved but don't show on the site.
    </p>

    <div class="flex justify-end mb-4">
        <button type="button" class="btn-brand !py-2" @click="editing = { name: '', role: '', quote: '', rating: 5 }; error = ''">
            <x-icon name="Plus" :size="15" /> Add testimonial
        </button>
    </div>

    <div class="space-y-3">
        <template x-for="item in items" :key="item.id">
            <div class="card p-5" :class="item.enabled ? '' : 'opacity-50'">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 cursor-pointer" @click="editing = JSON.parse(JSON.stringify(item)); error = ''">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="text-white text-sm font-medium" x-text="item.name"></p>
                            <p class="text-xs text-slate-500" x-show="item.role" x-text="'— ' + item.role"></p>
                            <span class="inline-flex">
                                <template x-for="i in item.rating"><x-icon name="Star" :size="11" class="text-brand fill-current" /></template>
                            </span>
                        </div>
                        <p class="text-sm text-slate-400 mt-1.5 line-clamp-2" x-text='`"${item.quote}"`'></p>
                    </div>
                    <div class="flex gap-2 shrink-0">
                        <button type="button" class="p-1.5 rounded-lg border border-ink-700 text-slate-400 hover:text-brand" @click="toggle(item)">
                            <span x-show="item.enabled"><x-icon name="Eye" :size="13" /></span>
                            <span x-show="!item.enabled"><x-icon name="EyeOff" :size="13" /></span>
                        </button>
                        <button type="button" class="p-1.5 rounded-lg border border-ink-700 text-slate-400 hover:text-red-400" @click="remove(item)">
                            <x-icon name="Trash2" :size="13" />
                        </button>
                    </div>
                </div>
            </div>
        </template>
        <p class="text-sm text-slate-500" x-show="loaded && items.length === 0" x-cloak>
            Nothing yet — run <code class="text-slate-400">php artisan db:seed --class=DemoDataSeeder</code> for sample testimonials, or add your own.
        </p>
    </div>

    <template x-teleport="body">
    <div x-show="editing" x-cloak class="fixed inset-0 z-50 grid place-items-center p-4 bg-ink-950/60 backdrop-blur-md" @click="editing = null">
        <div class="relative card p-7 w-full max-w-md animate-pop-in" @click.stop>
            <button type="button" @click="editing = null" aria-label="Close"
                class="absolute top-4 right-4 p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-ink-800 transition">
                <x-icon name="X" :size="18" />
            </button>
            <h2 class="font-display font-semibold text-white mb-5" x-text="editing?.id ? 'Edit testimonial' : 'New testimonial'"></h2>
            <template x-if="editing">
            <div class="space-y-3">
                <input class="input" placeholder="Customer name" x-model="editing.name">
                <input class="input" placeholder="Role / company (optional)" x-model="editing.role">
                <textarea class="input min-h-28" placeholder="What they said…" x-model="editing.quote"></textarea>
                <div class="flex items-center gap-2">
                    <span class="text-sm text-slate-400">Rating:</span>
                    <template x-for="n in 5" :key="n">
                        <button type="button" @click="editing.rating = n">
                            <x-icon name="Star" :size="18" ::class="n <= (editing.rating ?? 5) ? 'text-brand fill-current' : 'text-slate-600'" />
                        </button>
                    </template>
                </div>
                <p class="text-sm text-red-400" x-show="error" x-text="error" x-cloak></p>
                <div class="flex gap-3 pt-2">
                    <button type="button" class="btn-brand flex-1" @click="save()">Save</button>
                    <button type="button" class="btn-ghost flex-1" @click="editing = null">Cancel</button>
                </div>
            </div>
            </template>
        </div>
    </div>
</template>
    </div>

@push('scripts')
<script>
function adminTestimonials() {
    const H = { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content };
    return {
        items: [], editing: null, error: '', loaded: false,
        async load() {
            const res = await fetch('/api/admin/testimonials', { headers: H });
            if (res.ok) this.items = await res.json();
            this.loaded = true;
        },
        async save() {
            this.error = '';
            const url = this.editing.id ? `/api/admin/testimonials/${this.editing.id}` : '/api/admin/testimonials';
            const res = await fetch(url, { method: this.editing.id ? 'PUT' : 'POST', headers: H, body: JSON.stringify(this.editing) });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) { this.error = data.message || 'Could not save.'; return; }
            this.editing = null; this.load();
        },
        async toggle(item) {
            await fetch(`/api/admin/testimonials/${item.id}`, { method: 'PUT', headers: H, body: JSON.stringify({ enabled: !item.enabled }) });
            this.load();
        },
        async remove(item) {
            if (!confirm(`Remove the testimonial from "${item.name}"?`)) return;
            await fetch(`/api/admin/testimonials/${item.id}`, { method: 'DELETE', headers: H });
            this.load();
        },
    };
}
</script>
@endpush
@endsection
