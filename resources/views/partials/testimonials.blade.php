{{-- Rotating testimonials — Alpine port of Testimonials.jsx. Expects $testimonials. --}}
<div class="card gradient-ring p-8 sm:p-10 text-center relative overflow-hidden"
    x-data="{ index: 0, count: {{ $testimonials->count() }} }"
    @if ($testimonials->count() > 1)
        x-init="setInterval(() => { index = (index + 1) % count }, 5000)"
    @endif>
    <x-icon name="Quote" :size="48" class="absolute top-6 left-6 text-brand/20" />
    @foreach ($testimonials as $i => $item)
        <div x-show="index === {{ $i }}" @if ($i > 0) x-cloak @endif>
            <div class="flex justify-center gap-1 mb-4">
                @for ($s = 0; $s < ($item->rating ?? 5); $s++)
                    <x-icon name="Star" :size="16" class="text-brand fill-current" />
                @endfor
            </div>
            <p class="text-slate-200 text-base sm:text-lg leading-relaxed max-w-xl mx-auto min-h-[4.5rem]">
                "{{ t("testimonial.{$item->id}.quote", $item->quote) }}"
            </p>
            <p class="text-sm text-white font-medium mt-5">{{ t("testimonial.{$item->id}.name", $item->name) }}</p>
            @if ($item->role)
                <p class="text-xs text-slate-500">{{ t("testimonial.{$item->id}.role", $item->role) }}</p>
            @endif
        </div>
    @endforeach
    <div class="flex justify-center gap-2 mt-6">
        @foreach ($testimonials as $i => $item)
            <button type="button" @click="index = {{ $i }}" aria-label="Testimonial {{ $i + 1 }}"
                class="dot" :class="index === {{ $i }} ? 'dot-active' : ''"></button>
        @endforeach
    </div>
</div>
