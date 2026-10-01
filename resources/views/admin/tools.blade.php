@extends('layouts.admin')

@section('content')
<div x-data="adminTools()" x-init="load()">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-2">
        <h1 class="font-display text-xl sm:text-2xl font-bold text-white">AI tools</h1>
    </div>
    <p class="text-slate-400 text-sm mb-8">
        Every tool customers can use. Open a tool to edit its name, description, category,
        status, and the free credits signed-in users get before choosing a plan.
    </p>

    <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-4">
        <template x-for="(tool, i) in tools" :key="tool.id">
            <div class="card card-laminate p-5 flex flex-col gap-3 animate-slide-up" :style="`animation-delay: ${i * 50}ms`">
                <div class="flex items-start justify-between gap-3">
                    <span class="p-2.5 rounded-xl bg-brand/15 text-brand shrink-0" x-html="iconSvg(tool.icon)"></span>
                    <span class="text-[10px] uppercase tracking-wider rounded-full px-2.5 py-1 border"
                        :class="{
                            active: 'text-brand border-brand/40 bg-brand/10',
                            coming_soon: 'text-amber-300 border-amber-400/40 bg-amber-400/10',
                        }[tool.status] ?? 'text-slate-500 border-ink-700'"
                        x-text="tool.status.replace('_', ' ')"></span>
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="font-display font-semibold text-white truncate" x-text="tool.name"></h2>
                    <p class="text-sm text-slate-400 line-clamp-2 mt-0.5" x-text="tool.description"></p>
                </div>
                <div class="flex items-center justify-between gap-2 text-[11px] text-slate-500">
                    <span x-text="tool.taxonomy?.name ?? 'No category'"></span>
                    <span class="inline-flex items-center gap-1 text-brand" x-show="tool.free_enabled">
                        <x-icon name="Gift" :size="11" />
                        <span x-text="`${tool.free_limit ?? '∞'} free / ${tool.free_unit === 'day' ? 'day' : 'month'}`"></span>
                    </span>
                </div>
                <a :href="`/admin/tools/${tool.id}`" class="btn-brand w-full !py-2 text-sm">
                    <x-icon name="Pencil" :size="13" /> Edit tool
                </a>
            </div>
        </template>
    </div>

    {{-- Hidden icon set: cards pull the matching SVG by tool icon name --}}
    <div class="hidden" x-ref="iconset">
        @foreach (['ImagePlus', 'PenLine', 'Languages', 'FileSearch', 'Eraser', 'AudioLines', 'MessageCircleMore', 'ScanText', 'Wand2'] as $ic)
            <span data-icon="{{ $ic }}"><x-icon :name="$ic" :size="20" /></span>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
function adminTools() {
    return {
        tools: [],
        async load() {
            const res = await fetch('/api/admin/tools', { headers: { 'Accept': 'application/json' } });
            if (res.ok) this.tools = await res.json();
        },
        iconSvg(name) {
            const el = this.$refs.iconset.querySelector(`[data-icon="${name}"]`) || this.$refs.iconset.querySelector('[data-icon="Wand2"]');
            return el ? el.innerHTML : '';
        },
    };
}
</script>
@endpush
@endsection
