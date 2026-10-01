{{-- Branded splash loader — port of SiteLoader.jsx. Shown once per browser
     session; app.js fades it out and enforces the 2.5s safety timeout. --}}
@php
    $loaderStyle = brand('loader_style', 'neural');
    $loaderName  = brand('brand_name', 'Cubix AI Studio');
@endphp
<div id="site-loader" class="site-loader" role="status" aria-live="polite">
    @include('partials.loader-art')
    <span class="sr-only">Loading</span>
</div>
