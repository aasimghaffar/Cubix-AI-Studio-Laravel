{{-- One loader animation (art + brand name), selected by $loaderStyle.
     Used by the site splash loader and the Appearance live previews. --}}
@if ($loaderStyle === 'node')
        <div class="sl-card">
            <div class="sl-node">
                <span class="sl-aura"></span>
                <span class="sl-orbit"><i></i></span>
                <span class="sl-nucleus"></span>
            </div>
            <div class="sl-wave"><span></span><span></span><span></span><span></span><span></span></div>
            @include('partials.loader-name')
        </div>
    @elseif ($loaderStyle === 'orbit')
        <div class="sl-stack">
            <div class="sl-rings">
                <span class="sl-ring sl-ring-1"></span>
                <span class="sl-ring sl-ring-2"></span>
                <span class="sl-ring sl-ring-3"></span>
                <span class="sl-core"></span>
            </div>
            @include('partials.loader-name')
            <span class="sl-bar"><span></span></span>
        </div>
    @elseif ($loaderStyle === 'pulse')
        <div class="sl-stack">
            <div class="sl-pulse"><span></span><span></span><span></span><em></em></div>
            @include('partials.loader-name')
        </div>
    @elseif ($loaderStyle === 'prism')
        <div class="sl-stack">
            <div class="sl-prism">
                <span class="sl-face sl-face-a"></span>
                <span class="sl-face sl-face-b"></span>
                <span class="sl-face sl-face-c"></span>
            </div>
            @include('partials.loader-name')
            <span class="sl-bar"><span></span></span>
        </div>
    @else {{-- neural (default) --}}
        <div class="sl-stack">
            <div class="sl-neural">
                <svg viewBox="0 0 120 72" aria-hidden="true">
                    <g class="sl-links">
                        <line x1="14" y1="36" x2="60" y2="14"></line><line x1="14" y1="36" x2="60" y2="36"></line>
                        <line x1="14" y1="36" x2="60" y2="58"></line><line x1="60" y1="14" x2="106" y2="36"></line>
                        <line x1="60" y1="36" x2="106" y2="36"></line><line x1="60" y1="58" x2="106" y2="36"></line>
                    </g>
                    <g class="sl-nodes">
                        <circle cx="14" cy="36" r="5" style="animation-delay:0ms"></circle>
                        <circle cx="60" cy="14" r="4.5" style="animation-delay:180ms"></circle>
                        <circle cx="60" cy="36" r="4.5" style="animation-delay:300ms"></circle>
                        <circle cx="60" cy="58" r="4.5" style="animation-delay:420ms"></circle>
                        <circle cx="106" cy="36" r="5" style="animation-delay:600ms"></circle>
                    </g>
                </svg>
            </div>
            @include('partials.loader-name')
            <span class="sl-bar"><span></span></span>
        </div>
    @endif
