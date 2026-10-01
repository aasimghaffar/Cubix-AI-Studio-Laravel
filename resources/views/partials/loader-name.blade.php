@php $chars = preg_split('//u', $loaderName ?? brand('brand_name', 'Cubix AI Studio'), -1, PREG_SPLIT_NO_EMPTY); @endphp
<p class="sl-name">
    @foreach ($chars as $i => $ch)
        <span style="animation-delay: {{ $i * 55 }}ms">{{ $ch === ' ' ? "\u{00A0}" : $ch }}</span>
    @endforeach
</p>
