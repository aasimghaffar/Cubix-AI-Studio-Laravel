{{-- Sidebar body, shared by desktop and the mobile drawer. --}}
<div class="flex items-center gap-2 px-3 py-4 mb-2">
    <x-icon name="ShieldCheck" :size="22" class="text-brand" />
    <div>
        <p class="font-display font-bold text-white leading-tight">{{ brand('brand_name') }}</p>
        <p class="text-[11px] uppercase tracking-widest text-slate-500">Admin panel</p>
    </div>
</div>

@foreach ($NAV as $item)
    @if (isset($item['children']))
        @php $groupActive = collect($item['children'])->contains(fn ($c) => $isActive($c['to'])); @endphp
        <div x-data="{ open: {{ $groupActive ? 'true' : 'false' }} }">
            <button type="button" @click="open = !open"
                class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm transition {{ $groupActive ? 'text-brand' : 'text-slate-300 hover:bg-ink-800' }}">
                <x-icon :name="$item['icon']" :size="18" /> {{ $item['label'] }}
                <x-icon name="ChevronDown" :size="14" class="ml-auto transition" ::class="open ? 'rotate-180' : ''" />
            </button>
            <div x-show="open" @if (! $groupActive) x-cloak @endif class="ms-5 ps-3 border-s border-ink-700/60 space-y-0.5 mb-1">
                @foreach ($item['children'] as $child)
                    <a href="{{ url($child['to']) }}"
                        class="block px-3 py-2 rounded-lg text-[13px] transition {{ $isActive($child['to']) ? 'text-brand bg-brand/10' : 'text-slate-400 hover:text-white hover:bg-ink-800' }}">
                        {{ $child['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    @else
        <a href="{{ url($item['to']) }}"
            class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm transition {{ $isActive($item['to'], $item['exact'] ?? false) ? 'bg-brand/15 text-brand font-medium' : 'text-slate-400 hover:text-slate-200 hover:bg-ink-800' }}">
            <x-icon :name="$item['icon']" :size="18" /> {{ $item['label'] }}
        </a>
    @endif
@endforeach

<div class="mt-auto border-t border-ink-700/60 pt-3">
    <a href="{{ url('/') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm text-slate-400 hover:text-brand">
        <x-icon name="ArrowLeftRight" :size="18" /> View website
    </a>
    <p class="px-4 pt-2 text-sm text-slate-300 truncate">{{ auth()->user()->name }}</p>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm text-slate-400 hover:text-red-400 w-full">
            <x-icon name="LogOut" :size="18" /> Sign out
        </button>
    </form>
</div>
