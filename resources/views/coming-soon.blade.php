@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto px-6 py-32 text-center animate-fade-up">
    <span class="icon-tile mb-5 mx-auto"><x-icon name="WandSparkles" :size="22" /></span>
    <h1 class="font-display text-3xl font-bold text-white">{{ $what }} — {{ t('phase.title', 'arriving in the next update') }}</h1>
    <p class="text-slate-400 mt-4">
        {{ t('phase.text', 'This part of the studio is being rebuilt and lands in the next update. Everything else is ready to explore.') }}
    </p>
    <a href="{{ route('home') }}" class="btn-brand !px-7 !py-3 mt-8 inline-flex">{{ t('phase.back', 'Back to home') }} <x-icon name="ArrowRight" :size="16" /></a>
</div>
@endsection
