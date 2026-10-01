{{-- Sign in / Get started for guests, avatar dropdown for signed-in users. --}}
@auth
    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
        <button type="button" @click="open = !open"
            class="flex items-center gap-2 p-1.5 pr-2.5 rounded-full border border-ink-700 hover:border-brand/60 transition">
            <span class="p-1.5 rounded-full bg-brand/15 text-brand"><x-icon name="UserRound" :size="16" /></span>
            <x-icon name="ChevronDown" :size="14" class="text-slate-400" />
        </button>
        <div x-show="open" x-cloak class="absolute right-0 mt-2 w-52 card p-2 shadow-xl animate-pop-in z-50">
            <p class="px-3 py-2 text-sm text-white truncate border-b border-ink-700/60 mb-1">{{ auth()->user()->name }}</p>
            @if (auth()->user()->role === 'admin')
                <a href="{{ url('/admin') }}"
                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm text-slate-300 hover:bg-ink-800 hover:text-white">
                    <x-icon name="ShieldCheck" :size="16" /> {{ t('nav.admin', 'Admin panel') }}
                </a>
            @endif
            <a href="{{ url('/account') }}"
                class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm text-slate-300 hover:bg-ink-800 hover:text-white">
                <x-icon name="UserCircle2" :size="16" /> {{ t('nav.account', 'My account') }}
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm text-slate-300 hover:bg-ink-800 hover:text-red-400 w-full">
                    <x-icon name="LogOut" :size="16" /> {{ t('nav.signout', 'Sign out') }}
                </button>
            </form>
        </div>
    </div>
@else
    <a href="{{ route('login') }}" class="hidden sm:block text-sm text-slate-300 hover:text-white px-3 py-2">{{ t('nav.signin', 'Sign in') }}</a>
    <a href="{{ route('register') }}" class="btn-brand !py-2 !px-4 text-xs sm:text-sm">{{ t('nav.getstarted', 'Get started') }}</a>
@endauth
