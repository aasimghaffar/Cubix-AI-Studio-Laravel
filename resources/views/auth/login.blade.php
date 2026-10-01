@extends('layouts.bare')

@section('content')
@include('partials.auth-shell-top')
<h1 class="font-display text-2xl font-bold text-white mb-1">{{ t('auth.welcome', 'Welcome back') }}</h1>
<p class="text-sm text-slate-400 mb-7">{{ t('auth.signin_sub', 'Sign in to your studio.') }}</p>

@if (request('google') === 'failed')
    <div class="mb-4 rounded-xl border border-red-500/40 bg-red-500/10 text-red-400 text-sm px-4 py-3">
        {{ t('auth.google_failed', "Google sign-in didn't complete — please try again.") }}
    </div>
@elseif (request('google') === 'blocked')
    <div class="mb-4 rounded-xl border border-red-500/40 bg-red-500/10 text-red-400 text-sm px-4 py-3">
        {{ t('auth.google_blocked', 'This account has been suspended.') }}
    </div>
@elseif (request('google') === 'session_limit')
    <div class="mb-4 rounded-xl border border-amber-500/40 bg-amber-500/10 text-amber-400 text-sm px-4 py-3">
        {{ t('auth.google_limit', "Your plan's browser limit is reached. Sign out on another browser first, then try again.") }}
    </div>
@endif

@include('partials.google-button')

<form method="POST" action="{{ route('login') }}" class="space-y-3">
    @csrf
    <input class="input" type="email" name="email" placeholder="{{ t('auth.email', 'Email') }}" value="{{ old('email') }}" required>
    <input class="input" type="password" name="password" placeholder="{{ t('auth.password', 'Password') }}" required>
    @if ($errors->any())
        <div class="rounded-xl border border-red-500/40 bg-red-500/10 text-red-400 text-sm px-4 py-3">
            {{ $errors->first() }}
        </div>
    @endif
    <button type="submit" class="btn-brand w-full">{{ t('auth.signin_btn', 'Sign in') }}</button>
</form>

<p class="text-sm text-slate-400 mt-6">
    {{ t('auth.no_account', "Don't have an account?") }}
    <a href="{{ route('register') }}" class="text-brand hover:underline">{{ t('auth.register_btn', 'Create account') }}</a>
</p>
@include('partials.auth-shell-bottom')
@endsection
