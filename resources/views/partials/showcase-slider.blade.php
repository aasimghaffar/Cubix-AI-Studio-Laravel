{{-- Auto-rotating showcase — Alpine port of ShowcaseSlider.jsx. All five
     slides render server-side; Alpine switches which is visible and drives
     the 4.5s progress bar (restarted per slide via the :key-style x-effect). --}}
@php
    $slides = [
        ['icon' => 'ImagePlus', 'title' => 'AI Image Generator', 'caption' => t('slide.1.caption', '"A neon-lit street market at night, rain reflections, cinematic"')],
        ['icon' => 'PenLine', 'title' => 'AI Content Writer', 'caption' => t('slide.2.caption', 'A 400-word product story, written in your tone, in seconds')],
        ['icon' => 'Languages', 'title' => 'AI Translator', 'caption' => t('slide.3.caption', 'English → Urdu, Arabic, Spanish & 9 more — naturally phrased')],
        ['icon' => 'AudioLines', 'title' => 'AI Text-to-Audio', 'caption' => t('slide.4.caption', 'Natural voiceovers with adjustable speed and voice')],
        ['icon' => 'Sparkles', 'title' => 'One studio, every tool', 'caption' => t('slide.5.caption', 'Images, words, voice & documents under one subscription')],
    ];
@endphp
<div class="card p-4 sm:p-6 relative group"
    x-data="{ index: 0, paused: false, count: {{ count($slides) }}, timer: null,
        start() { this.stop(); this.timer = setInterval(() => { if (!this.paused) this.next() }, 4500) },
        stop() { if (this.timer) clearInterval(this.timer) },
        next() { this.index = (this.index + 1) % this.count; this.restartBar() },
        prev() { this.index = (this.index - 1 + this.count) % this.count; this.restartBar() },
        go(i) { this.index = i; this.restartBar() },
        restartBar() { const b = this.$refs.bar; if (!b) return; b.style.animation = 'none'; void b.offsetWidth; b.style.animation = '' } }"
    x-init="start()" @mouseenter="paused = true" @mouseleave="paused = false">

    <div class="absolute top-0 left-4 right-4 h-0.5 rounded-full bg-ink-700/60 overflow-hidden">
        <div x-ref="bar" class="h-full bg-gradient-to-r from-[rgb(var(--brand))] to-[rgb(var(--accent))] slide-progress"
            :style="paused ? 'animation-play-state: paused' : ''"></div>
    </div>

    <button type="button" aria-label="Previous slide" @click="prev()"
        class="absolute left-2 top-1/2 -translate-y-1/2 z-10 w-9 h-9 rounded-full grid place-items-center bg-ink-950/70 border border-ink-700 text-slate-300 opacity-0 group-hover:opacity-100 transition hover:text-white">‹</button>
    <button type="button" aria-label="Next slide" @click="next()"
        class="absolute right-2 top-1/2 -translate-y-1/2 z-10 w-9 h-9 rounded-full grid place-items-center bg-ink-950/70 border border-ink-700 text-slate-300 opacity-0 group-hover:opacity-100 transition hover:text-white">›</button>

    <div class="h-56 sm:h-72">
        {{-- Slide 1: image workspace art --}}
        <div x-show="index === 0" class="w-full h-full animate-fade-up">
            <div class="w-full h-full rounded-2xl overflow-hidden border border-ink-700/60 relative">
                <img src="{{ asset('art/ai-workspace.png') }}" alt="Creating images with AI" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-ink-950/60 to-transparent"></div>
            </div>
        </div>
        {{-- Slide 2: content writer art --}}
        <div x-show="index === 1" x-cloak class="w-full h-full animate-fade-up">
            <div class="w-full h-full rounded-2xl overflow-hidden border border-ink-700/60 relative">
                <img src="{{ asset('art/content-writer-art.png') }}" alt="Writing an article with AI" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-ink-950/60 to-transparent"></div>
            </div>
        </div>
        {{-- Slide 3: translator art --}}
        <div x-show="index === 2" x-cloak class="w-full h-full animate-fade-up">
            <div class="w-full h-full rounded-2xl overflow-hidden border border-ink-700/60 relative">
                <img src="{{ asset('art/translator-art.png') }}" alt="Translating text with AI" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-ink-950/60 to-transparent"></div>
            </div>
        </div>
        {{-- Slide 4: audio art --}}
        <div x-show="index === 3" x-cloak class="w-full h-full animate-fade-up">
            <div class="w-full h-full rounded-2xl overflow-hidden border border-ink-700/60 relative">
                <img src="{{ asset('art/text-to-audio-art.png') }}" alt="Text becomes natural speech" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-ink-950/60 to-transparent"></div>
            </div>
        </div>
        {{-- Slide 5: banner art --}}
        <div x-show="index === 4" x-cloak class="w-full h-full animate-fade-up">
            <div class="w-full h-full rounded-2xl overflow-hidden border border-ink-700/60 relative">
                <img src="{{ asset('art/ai-banner.png') }}" alt="AI creative tools" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-ink-950/70 to-transparent"></div>
            </div>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mt-5">
        <div class="flex items-center gap-3 min-w-0">
            @foreach ($slides as $i => $s)
                <template x-if="index === {{ $i }}">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="icon-tile !p-2.5 shrink-0"><x-icon :name="$s['icon']" :size="18" /></span>
                        <div class="min-w-0">
                            <p class="font-display font-semibold text-white">{{ $s['title'] }}</p>
                            <p class="text-xs text-slate-400 truncate">{{ $s['caption'] }}</p>
                        </div>
                    </div>
                </template>
            @endforeach
        </div>
        <div class="flex gap-2 shrink-0">
            @foreach ($slides as $i => $s)
                <button type="button" @click="go({{ $i }})" aria-label="Slide {{ $i + 1 }}"
                    class="dot" :class="index === {{ $i }} ? 'dot-active' : ''"></button>
            @endforeach
        </div>
    </div>
</div>
