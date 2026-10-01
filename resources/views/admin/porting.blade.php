@extends('layouts.admin')

@section('content')
<div class="max-w-lg mx-auto text-center py-24 animate-fade-up">
    <span class="icon-tile mb-5 mx-auto"><x-icon name="WandSparkles" :size="22" /></span>
    <h1 class="font-display text-2xl font-bold text-white">{{ $title }}</h1>
    <p class="text-slate-400 mt-3 leading-relaxed">
        This section is being converted from the previous interface and arrives in the
        next update — nothing has been removed, and all its data and settings are intact.
    </p>
    <a href="{{ url('/admin') }}" class="btn-brand !px-6 !py-2.5 mt-7 inline-flex">Back to dashboard</a>
</div>
@endsection
