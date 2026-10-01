{{-- Closing half of the AuthShell. The trust points are defined in this file
     (not the opening partial) because included partials do not share the
     variables they create with each other. --}}
@php
    $points = [
        ['ImagePlus', t('tool.ai-image-generator.desc', 'Turn words into stunning visuals.')],
        ['PenLine', t('tool.ai-content-writer.desc', 'Blogs, ads, emails — written in seconds.')],
        ['FileText', t('tool.ai-document-assistant.desc', 'Upload a document and ask it anything.')],
        ['AudioLines', t('tool.ai-text-to-audio.desc', 'Lifelike voiceovers from text.')],
    ];
@endphp
            </div>
            <ul class="lg:hidden mt-7 space-y-2.5 mx-auto w-full max-w-[19rem]">
                @foreach ($points as [$icon, $text])
                    <li class="grid grid-cols-[18px_1fr] items-start gap-2.5">
                        <x-icon :name="$icon" :size="13" class="text-brand mt-[3px]" />
                        <span class="text-[12px] leading-relaxed text-slate-400">{{ $text }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
    <div class="hidden lg:block relative z-10 overflow-hidden border-s border-ink-700/60">
        <img src="{{ asset('art/ai-workspace.png') }}" alt="" aria-hidden="true"
            class="absolute inset-0 w-full h-full object-cover opacity-70">
        <div class="absolute inset-0 bg-gradient-to-br from-ink-950/95 via-ink-950/70 to-ink-950/40"></div>
        <div class="aurora aurora-a w-[420px] h-[420px] -bottom-32 -right-24"></div>
        <div class="relative h-full flex flex-col justify-center p-14 max-w-xl">
            <h2 class="font-display text-3xl xl:text-4xl font-bold text-white leading-tight mb-4">
                {{ t('hero.title', 'Create images, words & voice with') }}
                <span class="animate-gradient-text">{{ brand('brand_name') }}</span>
            </h2>
            <p class="text-slate-300 mb-10">{{ t('hero.subtitle', 'One dashboard, one subscription — every AI tool you need.') }}</p>
            <ul class="space-y-4">
                @foreach ($points as [$icon, $text])
                    <li class="flex items-center gap-3 text-slate-200">
                        <span class="icon-tile !p-2 shrink-0"><x-icon :name="$icon" :size="16" /></span>
                        <span class="text-sm">{{ $text }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
