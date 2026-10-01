{{-- Payment brand tile: crisp vector logo, official colors, never overflows.
     Included with ['brand' => 'stripe'|'paypal'] and optional ['size' => 'sm']. --}}
@php $paySm = ($size ?? 'md') === 'sm'; @endphp
@if (($brand ?? '') === 'paypal')
    <span class="{{ $paySm ? 'h-8 w-14' : 'h-9 w-16' }} rounded-lg bg-white grid place-items-center shrink-0" title="PayPal">
        <span class="inline-flex items-center gap-1">
            <svg viewBox="0 0 24 24" class="{{ $paySm ? 'h-4 w-4' : 'h-[18px] w-[18px]' }}" aria-hidden="true"><path fill="#003087" d="M7.016 19.198h-4.2a.562.562 0 0 1-.555-.65L5.093.584A.692.692 0 0 1 5.776 0h7.222c3.417 0 5.904 2.488 5.846 5.5-.006.25-.027.5-.066.747A6.794 6.794 0 0 1 12.071 12H8.743a.69.69 0 0 0-.682.583l-.325 2.056-.013.083-.692 4.39-.015.087zM19.79 6.142c-.01.087-.01.175-.023.261a7.76 7.76 0 0 1-7.695 6.598H9.007l-.283 1.795-.013.083-.692 4.39-.134.843-.014.088H6.86l-.497 3.15a.562.562 0 0 0 .555.65h3.612c.34 0 .63-.249.683-.585l.952-6.031a.692.692 0 0 1 .683-.584h2.126a6.793 6.793 0 0 0 6.707-5.752c.306-1.95-.466-3.744-1.89-4.906z"/></svg>
            <span class="font-bold italic tracking-tight {{ $paySm ? 'text-[10px]' : 'text-[11px]' }}" style="color:#003087">Pay<span style="color:#0079C1">Pal</span></span>
        </span>
    </span>
@else
    <span class="{{ $paySm ? 'h-8 w-14' : 'h-9 w-16' }} rounded-lg bg-white grid place-items-center shrink-0" title="Stripe">
        <svg viewBox="0 0 24 24" class="{{ $paySm ? 'h-5 w-9' : 'h-6 w-10' }}" aria-hidden="true"><path fill="#635BFF" d="M13.976 9.15c-2.172-.806-3.356-1.426-3.356-2.409 0-.831.683-1.305 1.901-1.305 2.227 0 4.515.858 6.09 1.631l.89-5.494C18.252.975 15.697 0 12.165 0 9.667 0 7.589.654 6.104 1.872 4.56 3.147 3.757 4.992 3.757 7.218c0 4.039 2.467 5.76 6.476 7.219 2.585.92 3.445 1.574 3.445 2.583 0 .98-.84 1.545-2.354 1.545-1.875 0-4.965-.921-6.99-2.109l-.9 5.555C5.175 22.99 8.385 24 11.714 24c2.641 0 4.843-.624 6.328-1.813 1.664-1.305 2.525-3.236 2.525-5.732 0-4.128-2.524-5.851-6.594-7.305h.003z"/></svg>
    </span>
@endif
