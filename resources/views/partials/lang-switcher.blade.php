{{-- Language dropdown. $compact for footers/mobile. Changing language hits
     /lang/{code} which sets the cookie and redirects back. --}}
@php $langs = webctx()->languages(); $cur = webctx()->current(); @endphp
@if (count($langs) >= 2)
    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
        <button type="button" @click="open = !open"
            class="inline-flex items-center gap-1.5 rounded-lg border border-ink-700 text-slate-300 hover:border-brand/60 transition {{ ($compact ?? false) ? 'px-2 py-1.5 text-xs' : 'px-3 py-2 text-sm' }}">
            <x-icon name="Globe" :size="($compact ?? false) ? 13 : 15" /> {{ $cur?->native_name ?? 'EN' }}
        </button>
        <div x-show="open" x-cloak
            class="absolute right-0 {{ ($compact ?? false) ? 'bottom-full mb-2' : 'bottom-full mb-2 sm:bottom-auto sm:top-full sm:mb-0 sm:mt-2' }} w-44 card p-2 shadow-xl animate-pop-in z-50">
            @foreach ($langs as $l)
                <a href="{{ route('lang', $l->code) }}"
                    class="block w-full text-left px-3 py-2 rounded-lg text-sm {{ $l->code === $cur?->code ? 'text-brand bg-brand/10' : 'text-slate-300 hover:bg-ink-800' }}">
                    {{ $l->native_name }}
                </a>
            @endforeach
        </div>
    </div>
@endif
