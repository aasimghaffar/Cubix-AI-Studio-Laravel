{{-- Opening half of the AuthShell split-screen (port of AuthShell.jsx).
     Include this, print the form card content, then include auth-shell-bottom. --}}
<div class="min-h-screen grid lg:grid-cols-2 relative">
    @include('partials.fx-background')
    <div class="relative z-10 flex flex-col px-5 py-8 sm:p-10">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 mb-8 lg:mb-10 self-center lg:self-start">
            @include('partials.brand-mark')
            <span class="font-display font-bold text-white text-lg">{{ brand('brand_name') }}</span>
        </a>
        <div class="flex-1 flex flex-col justify-center w-full max-w-sm mx-auto">
            <p class="lg:hidden text-center text-sm text-slate-400 mb-5 px-2">
                {{ t('hero.subtitle', 'One dashboard, one subscription — every AI tool you need.') }}
            </p>
            <div class="relative z-10 w-full animate-fade-up glass-window p-6 sm:p-8 spotlight">
