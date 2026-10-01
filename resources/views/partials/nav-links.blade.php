{{-- Desktop navigation — admin menu with hover submenus, core links as fallback. --}}
@php
    $menu = webctx()->menu();
    $isActive = fn (string $path) => request()->path() === ltrim($path, '/') || ($path === '/' && request()->path() === '/');
    $navCls = fn (string $path) => 'text-sm transition '.($isActive($path) ? 'text-brand font-medium' : 'text-slate-300 hover:text-white');
@endphp
@if ($menu->count())
    @foreach ($menu as $item)
        @if ($item->children->count())
            <div class="relative group/menu">
                <button type="button" class="inline-flex items-center gap-1 text-sm text-slate-300 hover:text-white transition py-2">
                    {{ webctx()->menuLabel($item) }}
                    <x-icon name="ChevronDown" :size="13" class="transition group-hover/menu:rotate-180" />
                </button>
                <div class="absolute left-0 top-full pt-2 opacity-0 invisible group-hover/menu:opacity-100 group-hover/menu:visible transition z-50">
                    <div class="card p-2 w-52 shadow-xl">
                        @foreach ($item->children as $child)
                            <a href="{{ webctx()->isExternal($child) ? $child->target : url(webctx()->itemPath($child)) }}"
                                @if (webctx()->isExternal($child)) target="_blank" rel="noreferrer" @endif
                                class="block px-3 py-2 rounded-lg text-sm {{ $isActive(webctx()->itemPath($child)) ? 'text-brand bg-brand/10' : 'text-slate-300 hover:bg-ink-800' }}">
                                {{ webctx()->menuLabel($child) }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @elseif (webctx()->isExternal($item))
            <a href="{{ $item->target }}" target="_blank" rel="noreferrer"
                class="text-sm text-slate-300 hover:text-white transition">{{ webctx()->menuLabel($item) }}</a>
        @else
            <a href="{{ url(webctx()->itemPath($item)) }}" class="{{ $navCls(webctx()->itemPath($item)) }}">{{ webctx()->menuLabel($item) }}</a>
        @endif
    @endforeach
@else
    <a href="{{ url('/') }}" class="{{ $navCls('/') }}">{{ t('nav.home', 'Home') }}</a>
    <a href="{{ url('/tools') }}" class="{{ $navCls('/tools') }}">{{ t('nav.tools', 'Tools') }}</a>
    <a href="{{ url('/pricing') }}" class="{{ $navCls('/pricing') }}">{{ t('nav.pricing', 'Pricing') }}</a>
    <a href="{{ url('/contact') }}" class="{{ $navCls('/contact') }}">{{ t('nav.contact', 'Contact') }}</a>
@endif
