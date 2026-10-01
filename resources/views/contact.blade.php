@extends('layouts.app')

@section('content')
@php $business = brand('business') ?? []; @endphp
<div class="relative overflow-hidden"
    x-data="{ form: { name: '', email: '', subject: '', message: '' }, status: null,
        async submit() {
            this.status = 'busy';
            try {
                const res = await fetch('/api/contact', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: JSON.stringify(this.form),
                });
                if (!res.ok) {
                    const data = await res.json().catch(() => ({}));
                    this.status = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Something went wrong.');
                    return;
                }
                this.status = 'sent';
                this.form = { name: '', email: '', subject: '', message: '' };
            } catch (e) { this.status = 'Something went wrong.'; }
        } }">
    <div class="fixed inset-0 pointer-events-none">
        <img src="{{ asset('art/contact-team.jpg') }}" alt="" aria-hidden="true" class="w-full h-full object-cover opacity-35 select-none">
        <div class="absolute inset-0 bg-gradient-to-b from-ink-950/60 via-ink-950/70 to-ink-950/80"></div>
    </div>

    <div class="relative z-10 max-w-6xl mx-auto px-6 py-20">
        <div class="grid lg:grid-cols-[1fr_1.1fr] gap-10 items-start">
            <div class="animate-fade-up lg:sticky lg:top-24">
                <span class="inline-flex p-3 rounded-2xl bg-brand/15 text-brand mb-5 animate-pulse-glow"><x-icon name="MessageCircleMore" :size="24" /></span>
                <h1 class="font-display text-3xl md:text-4xl font-bold text-white leading-tight">
                    {{ t('contact.title', "Let's talk") }}<span class="animate-gradient-text">.</span>
                </h1>
                <p class="text-slate-300 mt-4 mb-8 leading-relaxed">
                    {{ t('contact.subtitle2', 'Our team is here to help — questions, feedback, custom plans, or partnership ideas. We read and answer every message.') }}
                </p>

                <div class="space-y-3">
                    @php
                        $infoCards = array_values(array_filter([
                            ($business['email'] ?? null) ? ['Mail', t('contact.email_us', 'Email us'), $business['email'], 'mailto:'.$business['email']] : null,
                            ($business['phone'] ?? null) ? ['Phone', t('contact.call_us', 'Call us'), $business['phone'], 'tel:'.str_replace(' ', '', $business['phone'])] : null,
                            ($business['address'] ?? null) ? ['MapPin', t('contact.visit_us', 'Visit us'), $business['address'], null] : null,
                        ]));
                    @endphp
                    @foreach ($infoCards as $i => [$icon, $label, $value, $href])
                        <div class="card glass glow-hover p-4 flex items-center gap-4 animate-slide-up" style="animation-delay: {{ $i * 80 }}ms">
                            <span class="p-2.5 rounded-xl bg-brand/15 text-brand shrink-0"><x-icon :name="$icon" :size="17" /></span>
                            <div class="min-w-0">
                                <p class="text-[11px] uppercase tracking-widest text-slate-500">{{ $label }}</p>
                                @if ($href)
                                    <a href="{{ $href }}" class="text-sm text-white hover:text-brand transition break-words">{{ $value }}</a>
                                @else
                                    <p class="text-sm text-white break-words">{{ $value }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="glass rounded-2xl p-4 mt-6 flex items-start gap-3">
                    <x-icon name="History" :size="15" class="text-brand shrink-0 mt-0.5" />
                    <p class="text-xs text-slate-300 leading-relaxed">{{ t('contact.reply_time', 'We typically reply within one business day — usually much faster.') }}</p>
                </div>
            </div>

            <div class="card gradient-ring p-8 space-y-4 animate-slide-up" style="animation-delay: 120ms">
                <h2 class="font-display font-semibold text-white text-lg">{{ t('contact.form_title', 'Send us a message') }}</h2>
                <div class="grid sm:grid-cols-2 gap-4">
                    <input class="input" placeholder="{{ t('contact.name', 'Your name') }}" x-model="form.name">
                    <input class="input" type="email" placeholder="{{ t('contact.email', 'Email address') }}" x-model="form.email">
                </div>
                <input class="input" placeholder="{{ t('contact.subject', 'Subject') }}" x-model="form.subject">
                <textarea class="input min-h-40" placeholder="{{ t('contact.message', 'Your message…') }}" x-model="form.message"></textarea>

                <div x-show="status === 'sent'" x-cloak class="rounded-xl border border-brand/40 bg-brand/10 text-brand text-sm px-4 py-3">
                    {{ t('contact.sent', 'Thanks! Your message has been sent.') }}
                </div>
                <div x-show="status && status !== 'busy' && status !== 'sent'" x-cloak
                    class="rounded-xl border border-red-500/40 bg-red-500/10 text-red-400 text-sm px-4 py-3" x-text="status"></div>

                <button type="button" class="btn-brand w-full" @click="submit()" :disabled="status === 'busy'">
                    <x-icon name="Send" :size="15" /> <span x-text="status === 'busy' ? '…' : '{{ t('contact.send', 'Send message') }}'"></span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
