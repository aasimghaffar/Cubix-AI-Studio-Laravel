@extends('layouts.bare')

@section('content')
<div class="min-h-screen grid place-items-center relative">
    @include('partials.fx-background')
    <div class="relative z-10 card p-8 text-center animate-pop-in" x-data="googleBridge()" x-init="run()">
        <span class="icon-tile mb-4 mx-auto"><x-icon name="Loader2" :size="20" class="animate-spin" /></span>
        <p class="text-sm text-slate-300" x-text="msg">{{ t('auth.google_finishing', 'Finishing Google sign-in…') }}</p>
    </div>
</div>
<script>
function googleBridge() {
    return {
        msg: @json(t('auth.google_finishing', 'Finishing Google sign-in…')),
        async run() {
            const token = new URLSearchParams(location.hash.slice(1)).get('token');
            if (!token) { location.href = '/login?google=failed'; return; }
            try {
                const res = await fetch('/auth/google/exchange', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json', 'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ token }),
                });
                const data = await res.json().catch(() => ({}));
                location.href = res.ok && data.redirect ? data.redirect : '/login?google=failed';
            } catch (e) { location.href = '/login?google=failed'; }
        },
    };
}
</script>
@endsection
