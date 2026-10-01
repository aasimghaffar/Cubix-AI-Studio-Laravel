@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto px-6 py-28 text-center animate-fade-up">
    <span class="icon-tile mb-5 mx-auto"><x-icon :name="$tool->icon" :size="24" /></span>
    <h1 class="font-display text-3xl font-bold text-white">{{ t("tool.{$tool->slug}.name", $tool->name) }}</h1>
    <p class="text-slate-400 mt-3 leading-relaxed">
        {{ t('workspace.pending', 'This workspace is part of the final update, arriving next. Your access and credits are ready and waiting.') }}
    </p>
    <a href="{{ route('tools') }}" class="btn-brand !px-7 !py-3 mt-8 inline-flex">
        {{ t('workspace.back', 'Back to tools') }} <x-icon name="ArrowRight" :size="16" />
    </a>
</div>
@endsection
