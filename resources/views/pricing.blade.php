@extends('layouts.app')

@section('content')
@php
    $payments = brand('payments') ?? [];
    $gateways = array_keys(array_filter(['stripe' => $payments['stripe'] ?? false, 'paypal' => $payments['paypal'] ?? false]));
@endphp
{{-- Full port of PublicPricing.jsx: grid + comparison table + custom package
     request + payment methods strip + live checkout (Stripe / PayPal). --}}
<div class="relative overflow-hidden"
    x-data='pricingPage(@json($gateways), @json((bool) auth()->user()))'>

    <div class="fixed inset-0 pointer-events-none">
        <img src="{{ asset('art/workflow-art.png') }}" alt="" aria-hidden="true" class="w-full h-full object-cover opacity-30 select-none">
        <div class="absolute inset-0 bg-gradient-to-b from-ink-950/60 via-ink-950/70 to-ink-950/80"></div>
    </div>

    <div class="relative z-10 max-w-6xl mx-auto px-6 py-20">
        <h1 class="font-display text-3xl md:text-4xl font-bold text-white text-center">{{ t('pricing.title', 'Pricing') }}</h1>
        <p class="text-slate-400 text-center mt-3 mb-14">
            {{ t('pricing.subtitle', 'Every plan includes every tool. Credits reset each billing cycle. Cancel anytime.') }}
        </p>

        <div x-show="error" x-cloak class="max-w-xl mx-auto mb-6 rounded-xl border border-red-500/40 bg-red-500/10 text-red-400 text-sm px-4 py-3 flex items-start justify-between gap-3">
            <span x-text="error"></span>
            <button type="button" class="shrink-0 text-red-400/70 hover:text-red-300" @click="error = ''">✕</button>
        </div>

        @include('partials.pricing-grid', ['checkout' => true, 'withCompare' => true])

        {{-- Custom package request --}}
        <div class="card glass mt-10 p-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-center sm:text-left">
            <div>
                <p class="font-display font-semibold text-white">{{ t('pricing.custom_title', 'Need something bigger — or different?') }}</p>
                <p class="text-sm text-slate-400 mt-1">{{ t('pricing.custom_sub', 'Tell us what your team needs and we will build a private custom plan just for you — your own credit amounts, browser logins, and price.') }}</p>
            </div>
            @auth
                <button type="button" class="btn-ghost shrink-0" @click="requesting = true">
                    <x-icon name="MessageSquarePlus" :size="15" /> {{ t('pricing.custom_btn', 'Request a custom package') }}
                </button>
            @else
                <a href="{{ route('register') }}" class="btn-ghost shrink-0">
                    <x-icon name="MessageSquarePlus" :size="15" /> {{ t('pricing.custom_btn', 'Request a custom package') }}
                </a>
            @endauth
        </div>

        {{-- Payment methods strip --}}
        @if (count($gateways))
            <div class="flex items-center justify-center gap-3 mt-12">
                <span class="text-[11px] uppercase tracking-widest text-slate-500">{{ t('pay.supported', 'Payment methods supported') }}</span>
                @if ($payments['stripe'] ?? false)
                    @include('partials.pay-logo', ['brand' => 'stripe'])
                @endif
                @if ($payments['paypal'] ?? false)
                    @include('partials.pay-logo', ['brand' => 'paypal'])
                @endif
                <x-icon name="Lock" :size="12" class="text-brand" />
            </div>
        @endif

    </div>

    <template x-teleport="body">
    {{-- Gateway chooser (only when both Stripe and PayPal are enabled) --}}
    <div x-show="choosing !== null" x-cloak
        class="fixed inset-0 z-50 grid place-items-center p-4 bg-ink-950/60 backdrop-blur-md" @click="choosing = null">
        <div class="relative card p-7 w-full max-w-sm animate-pop-in" @click.stop>
            <button type="button" aria-label="Close" @click="choosing = null"
                class="absolute top-4 right-4 p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-ink-800 transition">
                <x-icon name="X" :size="18" />
            </button>
            <h2 class="font-display font-semibold text-white mb-1">{{ t('pay.choose_title', 'How would you like to pay?') }}</h2>
            <p class="text-xs text-slate-500 mb-5">{{ t('pay.choose_sub', 'Both options are secure — pick whichever you prefer.') }}</p>
            <div class="space-y-3">
                <button type="button" @click="go('stripe')" :disabled="busyId !== null"
                    class="w-full flex items-center gap-3 rounded-xl border border-ink-700 hover:border-brand/60 bg-ink-800/50 px-4 py-3 text-sm text-slate-200 transition disabled:opacity-60">
                    @include('partials.pay-logo', ['brand' => 'stripe', 'size' => 'sm'])
                    {{ t('pay.card', 'Pay by card (Stripe)') }}
                </button>
                <button type="button" @click="go('paypal')" :disabled="busyId !== null"
                    class="w-full flex items-center gap-3 rounded-xl border border-ink-700 hover:border-brand/60 bg-ink-800/50 px-4 py-3 text-sm text-slate-200 transition disabled:opacity-60">
                    @include('partials.pay-logo', ['brand' => 'paypal', 'size' => 'sm'])
                    {{ t('pay.paypal', 'Pay with PayPal') }}
                </button>
            </div>
        </div>
    </div>

    </template>
    {{-- Custom package request modal --}}
    <template x-teleport="body">
    <div x-show="requesting" x-cloak
        class="fixed inset-0 z-50 grid place-items-center p-4 bg-ink-950/60 backdrop-blur-md" @click="requesting = false">
        <div class="relative card p-7 w-full max-w-md animate-pop-in" @click.stop>
            <button type="button" aria-label="Close" @click="requesting = false"
                class="absolute top-4 right-4 p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-ink-800 transition">
                <x-icon name="X" :size="18" />
            </button>
            <h2 class="font-display font-semibold text-white mb-2">{{ t('pricing.custom_modal_title', 'Request a custom package') }}</h2>
            <template x-if="requestDone">
                <p class="text-sm text-brand" x-text="requestDone"></p>
            </template>
            <template x-if="!requestDone">
                <div>
                    <p class="text-xs text-slate-500 mb-4">
                        {{ t('pricing.custom_modal_sub', 'Describe what you need — which tools you use most, roughly how many credits per month, how many people will sign in, and your budget if you have one.') }}
                    </p>
                    <textarea class="input min-h-36" x-model="requestMessage"
                        placeholder="{{ t('pricing.custom_placeholder', "e.g. We're a small agency: ~500 images and 1,000 articles a month, 4 team members sharing one account…") }}"></textarea>
                    <p class="text-sm text-red-400 mt-2" x-show="requestError" x-text="requestError" x-cloak></p>
                    <button type="button" class="btn-brand w-full mt-4" @click="sendRequest()"
                        :disabled="requestBusy || requestMessage.trim().length < 20"
                        x-text="requestBusy ? '{{ t('pricing.sending', 'Sending…') }}' : '{{ t('pricing.send_request', 'Send request') }}'"></button>
                </div>
            </template>
        </div>
    </div>
</template>
    </div>

@push('scripts')
<script>
function pricingPage(gateways, authed) {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    return {
        gateways, authed,
        error: '', busyId: null, choosing: null,
        requesting: false, requestMessage: '', requestBusy: false, requestDone: '', requestError: '',

        init() {
            // Back from an abandoned / failed checkout
            const q = new URLSearchParams(location.search).get('checkout');
            if (q === 'canceled') this.error = @json(t('pay.canceled', 'Checkout was canceled — no charge was made. Pick a plan whenever you are ready.'));
            if (q === 'failed') this.error = @json(t('pay.failed', 'We could not confirm that payment. If you were charged, contact us and we will sort it out immediately.'));
            if (q) history.replaceState({}, '', '/pricing');
        },

        choose(pkgId) {
            if (!this.authed) { location.href = @json(route('register')); return; }
            this.error = '';
            if (this.gateways.length === 0) { this.error = @json(t('pay.disabled', 'Payments are not configured yet — please contact us.')); return; }
            if (this.gateways.length === 1) { this.startCheckout(this.gateways[0], pkgId); return; }
            this.choosing = pkgId; // both enabled → let the customer pick
        },

        go(gateway) { const id = this.choosing; this.choosing = null; this.startCheckout(gateway, id); },

        async startCheckout(gateway, pkgId) {
            this.busyId = pkgId;
            try {
                const res = await fetch(gateway === 'paypal' ? '/checkout/paypal' : '/checkout/stripe', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify({ package_id: pkgId }),
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok || !data.checkout_url) {
                    this.error = data.message || @json(t('pay.error', 'Could not start checkout — please try again.'));
                    this.busyId = null;
                    return;
                }
                location.href = data.checkout_url; // off to Stripe / PayPal
            } catch (e) {
                this.error = @json(t('pay.error', 'Could not start checkout — please try again.'));
                this.busyId = null;
            }
        },

        async sendRequest() {
            this.requestBusy = true; this.requestError = '';
            try {
                const res = await fetch('/pricing/request-custom', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify({ message: this.requestMessage }),
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    this.requestError = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Something went wrong.');
                    return;
                }
                this.requestDone = data.message;
            } catch (e) {
                this.requestError = 'Something went wrong.';
            } finally {
                this.requestBusy = false;
            }
        },
    };
}
</script>
@endpush
@endsection
