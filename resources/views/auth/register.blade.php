@extends('layouts.bare')

@section('content')
@include('partials.auth-shell-top')
<h1 class="font-display text-2xl font-bold text-white mb-1">{{ t('auth.create_title', 'Create your account') }}</h1>
<p class="text-sm text-slate-400 mb-7">{{ t('auth.create_sub', 'Free to join — no credit card required.') }}</p>

@include('partials.google-button')

<form method="POST" action="{{ route('register') }}" class="space-y-3">
    @csrf
    <div class="grid grid-cols-2 gap-3">
        <input class="input" name="first_name" placeholder="{{ t('auth.first_name', 'First name') }}" value="{{ old('first_name') }}" required>
        <input class="input" name="last_name" placeholder="{{ t('auth.last_name', 'Last name') }}" value="{{ old('last_name') }}">
    </div>
    <input class="input" type="email" name="email" placeholder="{{ t('auth.email', 'Email') }}" value="{{ old('email') }}" required>
    <input class="input" type="password" name="password" placeholder="{{ t('auth.password', 'Password') }}" required>
    <input class="input" type="password" name="password_confirmation" placeholder="{{ t('auth.password_confirm', 'Confirm password') }}" required>
    @if ($errors->any())
        <div class="rounded-xl border border-red-500/40 bg-red-500/10 text-red-400 text-sm px-4 py-3">
            {{ $errors->first() }}
        </div>
    @endif
    <button type="submit" class="btn-brand w-full">{{ t('auth.register_btn', 'Create account') }}</button>
</form>

<p class="text-sm text-slate-400 mt-6">
    {{ t('auth.have_account', 'Already have an account?') }}
    <a href="{{ route('login') }}" class="text-brand hover:underline">{{ t('auth.signin_btn', 'Sign in') }}</a>
</p>
@include('partials.auth-shell-bottom')
@endsection
