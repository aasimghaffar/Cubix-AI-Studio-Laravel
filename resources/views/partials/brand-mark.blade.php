@if (brand('brand_logo'))
    <img src="{{ brand('brand_logo') }}" alt="{{ brand('brand_name') }}" class="h-8 w-auto max-w-[140px] object-contain">
@else
    <x-icon name="Sparkles" :size="$size ?? 22" class="text-brand" />
@endif
