@extends('layouts.admin')

@section('content')
@php
    $SHORTCODES = [
        ['[pricing]', 'Pricing plans', 'The full pricing grid with monthly/yearly tabs and discount badges — the same one as the Pricing page.'],
        ['[tools]', 'AI tools grid', 'All active AI tools as clickable cards, grouped exactly like the Tools page.'],
        ['[stats]', 'Statistics strip', 'The "tools included / languages supported" numbers strip from the homepage.'],
        ['[cta]', 'Call-to-action', 'A "create your free account" banner with a sign-up button.'],
    ];
@endphp
<div class="w-full" x-data="{ copied: '' , copy(code) { navigator.clipboard?.writeText(code); this.copied = code; setTimeout(() => this.copied = '', 1500); } }">
    <h1 class="font-display text-xl sm:text-2xl font-bold text-white mb-1">Shortcodes</h1>
    <p class="text-slate-400 text-sm mb-8">
        Paste any of these codes into a page (Pages → edit → type it on its own line) and the
        matching section is rendered there automatically — just like WordPress shortcodes.
        Example: create an "Our plans" page containing only <code class="text-brand">[pricing]</code>.
    </p>
    <div class="space-y-3">
        @foreach ($SHORTCODES as [$code, $shortTitle, $desc])
            <div class="card p-5 flex items-start gap-4">
                <span class="icon-tile !p-2.5 shrink-0"><x-icon name="Code2" :size="17" /></span>
                <div class="flex-1">
                    <div class="flex items-center gap-3 flex-wrap">
                        <code class="text-brand font-semibold">{{ $code }}</code>
                        <span class="text-white text-sm font-medium">{{ $shortTitle }}</span>
                    </div>
                    <p class="text-xs text-slate-400 mt-1">{{ $desc }}</p>
                </div>
                <button type="button" @click="copy('{{ $code }}')"
                    class="shrink-0 inline-flex items-center gap-1.5 text-xs text-slate-300 border border-ink-700 rounded-lg px-3 py-2 hover:border-brand/60">
                    <span x-show="copied === '{{ $code }}'" x-cloak class="inline-flex items-center gap-1.5"><x-icon name="Check" :size="13" class="text-brand" /> Copied</span>
                    <span x-show="copied !== '{{ $code }}'" class="inline-flex items-center gap-1.5"><x-icon name="Copy" :size="13" /> Copy</span>
                </button>
            </div>
        @endforeach
    </div>
</div>
@endsection
