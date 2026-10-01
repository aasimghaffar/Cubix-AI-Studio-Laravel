@extends('layouts.app')

@section('content')
@php
    $user = auth()->user();
    $sub = $user->activeSubscription;
    $boot = [
        'name' => $user->name,
        'email' => $user->email,
        'created_at' => $user->created_at?->toISOString(),
        'date_of_birth' => $user->date_of_birth ? substr((string) $user->date_of_birth, 0, 10) : '',
        'google' => (bool) $user->google_id,
        'notify_prefs' => $user->notify_prefs ?: new stdClass,
        'sub' => $sub ? [
            'package_name' => $sub->package?->name,
            'expires_at' => $sub->expires_at?->toISOString(),
            'cancel_at_period_end' => (bool) $sub->cancel_at_period_end,
        ] : null,
    ];
@endphp
<div class="max-w-3xl mx-auto px-4 sm:px-6 py-14"
    x-data='accountPage(@json($boot))' x-init="init()">

    <h1 class="font-display text-2xl font-bold text-white mb-6">{{ t('account.title', 'My account') }}</h1>

    {{-- Tabs --}}
    <div class="card p-1.5 flex gap-1 mb-8 overflow-x-auto w-full">
        @foreach ([
            ['profile', 'account.tab.profile', 'Profile', 'UserCircle2'],
            ['plan', 'account.tab.plan', 'Plan & credits', 'CreditCard'],
            ['password', 'account.tab.password', 'Password', 'KeyRound'],
            ['notifications', 'account.tab.notifications', 'Notifications', 'BellRing'],
        ] as [$id, $key, $label, $icon])
            <button type="button" @click="tab = '{{ $id }}'"
                class="relative flex-1 min-w-fit inline-flex items-center justify-center gap-2 px-4 sm:px-5 py-2.5 rounded-xl text-sm whitespace-nowrap transition"
                :class="tab === '{{ $id }}' ? 'text-ink-950 font-semibold' : 'text-slate-300 hover:text-white hover:bg-ink-800'"
                :style="tab === '{{ $id }}' ? 'background: linear-gradient(90deg, rgb(var(--brand)), rgb(var(--accent)))' : ''">
                <x-icon :name="$icon" :size="15" /> {{ t($key, $label) }}
            </button>
        @endforeach
    </div>

    {{-- ── Profile tab ── --}}
    <div x-show="tab === 'profile'" class="card p-7 animate-fade-up">
        <div class="flex items-center gap-4 mb-7">
            <span class="w-16 h-16 rounded-2xl grid place-items-center font-display font-bold text-xl text-ink-950"
                style="background: linear-gradient(135deg, rgb(var(--brand)), rgb(var(--accent)))"
                x-text="initials()"></span>
            <div>
                <h2 class="font-display font-semibold text-white text-lg">{{ $user->name }}</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    {{ t('acct.member_since', 'Member since') }}
                    {{ $user->created_at?->translatedFormat('F Y') ?? '—' }}
                </p>
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="text-xs text-slate-500 block mb-1.5">{{ t('acct.first_name', 'First name') }}</label>
                <input class="input" x-model="profile.first_name">
            </div>
            <div>
                <label class="text-xs text-slate-500 block mb-1.5">{{ t('acct.last_name', 'Last name') }}</label>
                <input class="input" x-model="profile.last_name">
            </div>
            <div>
                <label class="text-xs text-slate-500 block mb-1.5">{{ t('acct.dob', 'Date of birth') }}</label>
                <input class="input" type="date" :max="new Date().toISOString().slice(0, 10)" x-model="profile.date_of_birth">
            </div>
            <div>
                <label class="text-xs text-slate-500 block mb-1.5">
                    {{ t('acct.email', 'Email') }} <span class="text-slate-600">{{ t('acct.email_locked', "(sign-in email can't be changed)") }}</span>
                </label>
                <input class="input w-full opacity-60 cursor-not-allowed" value="{{ $user->email }}" readonly>
            </div>
        </div>

        <div x-show="profileError" x-cloak class="mt-4 rounded-xl border border-red-500/40 bg-red-500/10 text-red-400 text-sm px-4 py-3" x-text="profileError"></div>
        <div class="flex items-center gap-3 mt-6">
            <button type="button" class="btn-brand" @click="saveProfile()" :disabled="busy"
                x-text="busy ? @js(t('acct.saving', 'Saving…')) : @js(t('acct.save_profile', 'Save profile'))"></button>
            <span class="text-sm text-brand" x-show="profileDone" x-cloak>{{ t('acct.saved', 'Saved ✓') }}</span>
        </div>
    </div>

    {{-- ── Plan & credits tab ── --}}
    <div x-show="tab === 'plan'" x-cloak class="animate-fade-up">
        <div x-show="notice" x-cloak class="mb-5 rounded-xl border border-brand/40 bg-brand/10 text-brand text-sm px-4 py-3 flex items-start justify-between gap-3">
            <span x-text="notice"></span>
            <button type="button" class="shrink-0 text-brand/70 hover:text-brand" @click="notice = ''">✕</button>
        </div>

        <div class="card p-6 mb-5">
            <h2 class="font-display font-semibold text-white mb-4">{{ t('acct.current_plan', 'Current plan') }}</h2>
            <template x-if="sub">
                <div>
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <x-icon name="BadgeCheck" :size="20" class="text-brand" />
                            <div>
                                <p class="text-white font-medium" x-text="sub.package_name"></p>
                                <p class="text-xs text-slate-500 flex items-center gap-1.5 mt-0.5">
                                    <x-icon name="History" :size="12" />
                                    <span x-text="sub.cancel_at_period_end
                                        ? `Renewal canceled — active until ${new Date(sub.expires_at).toLocaleDateString()}`
                                        : sub.expires_at
                                            ? `Renews ${new Date(sub.expires_at).toLocaleDateString()}`
                                            : 'Active'"></span>
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('pricing') }}" class="text-sm text-brand hover:underline shrink-0">{{ t('acct.change_plan', 'Change plan') }}</a>
                    </div>
                    <div class="border-t border-ink-700/60 mt-5 pt-4" x-show="!sub.cancel_at_period_end">
                        <button type="button" @click="confirming = true"
                            class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-red-400 transition">
                            <x-icon name="CircleX" :size="15" /> {{ t('account.cancel', 'Cancel subscription') }}
                        </button>
                    </div>
                    <div x-show="cancelError" x-cloak class="mt-3 rounded-xl border border-red-500/40 bg-red-500/10 text-red-400 text-sm px-4 py-3" x-text="cancelError"></div>
                </div>
            </template>
            <template x-if="!sub">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <p class="text-slate-400 text-sm">{{ t('account.no_plan', 'No active plan yet.') }}</p>
                    <a href="{{ route('pricing') }}" class="btn-brand !py-2">{{ t('account.choose_plan', 'Choose a plan') }}</a>
                </div>
            </template>
        </div>

        <div class="card p-6" x-show="Object.keys(meters).length > 0">
            <h2 class="font-display font-semibold text-white mb-5">{{ t('account.credits', 'Credits this cycle') }}</h2>
            <div class="grid sm:grid-cols-2 gap-6">
                <template x-for="[key, m] in Object.entries(meters)" :key="key">
                    <div>
                        <p class="text-xs text-slate-400 mb-1" x-text="METER_LABELS[key] ?? key"></p>
                        {{-- unlimited plan credits --}}
                        <p class="text-xs font-semibold animate-gradient-text inline-block" x-show="Number(m.limit) === -1">∞ Unlimited</p>
                        {{-- unlimited free usage --}}
                        <p class="text-xs text-brand" x-show="m.limit === null || m.limit === undefined"
                            x-text="`${m.free ? (m.renews === 'day' ? @js(t('acct.free_today', 'free uses today')) : @js(t('acct.free_month', 'free uses this month'))) : 'Free'} — unlimited${m.renews ? ` · renews ${m.renews === 'day' ? 'daily' : 'monthly'}` : ''}`"></p>
                        {{-- metered --}}
                        <template x-if="m.limit !== null && m.limit !== undefined && Number(m.limit) !== -1">
                            <div>
                                <div class="h-2 rounded-full bg-ink-700 overflow-hidden">
                                    <div class="h-full rounded-full transition-all"
                                        :class="pct(m) >= 90 ? 'bg-red-500' : 'bg-brand'"
                                        :style="`width: ${pct(m)}%`"></div>
                                </div>
                                <p class="text-xs text-slate-500 mt-1"
                                    x-text="`${Number(m.used).toLocaleString()} / ${Number(m.limit).toLocaleString()} ${m.free ? (m.renews === 'day' ? @js(t('acct.free_today', 'free uses today')) : @js(t('acct.free_month', 'free uses this month'))) : 'used'}`"></p>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        <template x-teleport="body">
    {{-- Cancel confirmation --}}
        <div x-show="confirming" x-cloak class="fixed inset-0 z-50 grid place-items-center p-4 bg-ink-950/60 backdrop-blur-md" @click="confirming = false">
            <div class="relative card p-7 w-full max-w-sm animate-pop-in border-red-400/30" @click.stop>
                <button type="button" @click="confirming = false" aria-label="Close"
                    class="absolute top-4 right-4 p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-ink-800 transition">
                    <x-icon name="X" :size="18" />
                </button>
                <h2 class="font-display font-semibold text-white mb-2">{{ t('acct.cancel_confirm', 'Cancel your subscription?') }}</h2>
                <p class="text-sm text-slate-400 mb-6">
                    Your plan will <strong class="text-white">not renew</strong>, and no further payments will be taken.
                    You keep full access and your remaining credits until
                    <span x-text="sub?.expires_at ? new Date(sub.expires_at).toLocaleDateString() : 'the end of the period'"></span>.
                </p>
                <div class="flex gap-3">
                    <button type="button" @click="cancelPlan()" :disabled="busy"
                        class="flex-1 rounded-xl px-5 py-2.5 text-sm font-semibold bg-red-500/90 text-white hover:bg-red-500 disabled:opacity-50 transition"
                        x-text="busy ? 'Canceling…' : 'Yes, cancel'"></button>
                    <button type="button" class="btn-ghost flex-1" @click="confirming = false">{{ t('acct.keep_plan', 'Keep plan') }}</button>
                </div>
            </div>
        </div>
    </template>
    </div>

    {{-- ── Password tab ── --}}
    <div x-show="tab === 'password'" x-cloak class="card p-7 animate-fade-up grid md:grid-cols-[1fr_260px] gap-8">
        <div>
            <h2 class="font-display font-semibold text-white mb-1">{{ t('acct.change_password', 'Change password') }}</h2>
            <p class="text-xs text-slate-500 mb-5">
                @if ($user->google_id)
                    {{ t('acct.pw_google', 'You signed up with Google. You can still set a password here to also sign in with email.') }}
                @else
                    {{ t('acct.pw_min', 'Use at least 8 characters.') }}
                @endif
            </p>
            <div class="space-y-3">
                <input class="input" type="password" placeholder="{{ t('acct.ph_current_pw', 'Current password') }}" x-model="pw.current_password">
                <input class="input" type="password" placeholder="{{ t('acct.ph_new_pw', 'New password (min 8)') }}" x-model="pw.password">
                <input class="input" type="password" placeholder="{{ t('acct.ph_confirm_pw', 'Confirm new password') }}" x-model="pw.password_confirmation">
                <div x-show="pwError" x-cloak class="rounded-xl border border-red-500/40 bg-red-500/10 text-red-400 text-sm px-4 py-3" x-text="pwError"></div>
                <div x-show="pwDone" x-cloak class="rounded-xl border border-brand/40 bg-brand/10 text-brand text-sm px-4 py-3">{{ t('acct.pw_updated', 'Password updated successfully.') }}</div>
                <button type="button" class="btn-brand w-full" @click="savePassword()" :disabled="busy"
                    x-text="busy ? @js(t('acct.saving', 'Saving…')) : @js(t('acct.update_password', 'Update password'))"></button>
            </div>
        </div>
        <div class="hidden md:block border-s border-ink-700/60 ps-8">
            <p class="text-xs uppercase tracking-widest text-slate-500 mb-3">{{ t('acct.pw_tips', 'Strong password tips') }}</p>
            <ul class="space-y-2.5 text-xs text-slate-400 leading-relaxed">
                <li>• {{ t('acct.tip1', 'Use 12+ characters when possible') }}</li>
                <li>• {{ t('acct.tip2', 'Mix letters, numbers, and symbols') }}</li>
                <li>• {{ t('acct.tip3', 'Avoid names, birthdays, and reused passwords') }}</li>
                <li>• {{ t('acct.tip4', 'A short sentence works great: "coffee-at-9-tastes-best!"') }}</li>
            </ul>
        </div>
    </div>

    {{-- ── Notifications tab ── --}}
    <div x-show="tab === 'notifications'" x-cloak class="card p-6 animate-fade-up">
        <div class="flex items-center justify-between gap-3 mb-1">
            <h2 class="font-display font-semibold text-white">{{ t('acct.email_notifs', 'Email notifications') }}</h2>
            <span class="text-xs text-brand" x-show="notifSaved" x-cloak>{{ t('acct.saved', 'Saved ✓') }}</span>
        </div>
        <p class="text-xs text-slate-500 mb-6">{{ t('acct.notifs_sub', 'Choose which emails you receive. Security emails (like verification) are always sent.') }}</p>

        <div class="space-y-4">
            @foreach ([
                ['plan_purchased', 'notif.purchase', 'Plan purchase receipts', 'notif.purchase_sub', 'When a plan is purchased or assigned to your account'],
                ['plan_expiry', 'notif.expiry', 'Plan expiry reminders', 'notif.expiry_sub', 'A heads-up before your plan renews or expires'],
                ['account_updates', 'notif.updates', 'Account updates', 'notif.updates_sub', 'Important changes to your account status'],
            ] as [$nk, $tkey, $label, $hintKey, $hint])
                <label class="flex items-start justify-between gap-4" :class="prefs.all === false ? 'opacity-40 pointer-events-none' : ''">
                    <span>
                        <span class="block text-sm text-white">{{ t($tkey, $label) }}</span>
                        <span class="block text-xs text-slate-500 mt-0.5">{{ t($hintKey, $hint) }}</span>
                    </span>
                    <button type="button" :disabled="busy" @click="savePrefs({ ...prefs, {{ $nk }}: prefs.{{ $nk }} === false })"
                        class="relative w-11 h-6 rounded-full transition shrink-0 disabled:opacity-50"
                        :class="prefs.{{ $nk }} !== false ? 'bg-brand' : 'bg-ink-700'">
                        <span class="absolute top-0.5 w-5 h-5 rounded-full bg-white transition-all"
                            :class="prefs.{{ $nk }} !== false ? 'left-[22px]' : 'left-0.5'"></span>
                    </button>
                </label>
            @endforeach

            <div class="border-t border-ink-700/60 pt-4 flex items-start justify-between gap-4">
                <span>
                    <span class="block text-sm text-white">{{ t('acct.disable_all', 'Disable all notifications') }}</span>
                    <span class="block text-xs text-slate-500 mt-0.5">{{ t('acct.disable_all_sub', 'Turn off every optional email in one switch') }}</span>
                </span>
                <button type="button" :disabled="busy" @click="savePrefs({ ...prefs, all: prefs.all === false })"
                    class="relative w-11 h-6 rounded-full transition shrink-0 disabled:opacity-50"
                    :class="prefs.all === false ? 'bg-red-500' : 'bg-ink-700'">
                    <span class="absolute top-0.5 w-5 h-5 rounded-full bg-white transition-all"
                        :class="prefs.all === false ? 'left-[22px]' : 'left-0.5'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function accountPage(boot) {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const H = { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf };
    const err = (data) => data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Something went wrong.');
    const parts = (boot.name ?? '').split(' ');

    return {
        tab: 'profile', busy: false,
        sub: boot.sub, meters: {},
        METER_LABELS: {
            image_generation_credits: 'AI images', content_writer_credits: 'Written articles',
            translation_credits: 'Translations', document_query_credits: 'Document queries',
            background_removal_credits: 'Background removals', audio_character_limit: 'Audio characters',
            chat_credits: 'Chat questions', rewriter_credits: 'Rewrites', summarizer_credits: 'Summaries',
        },
        profile: { first_name: parts[0] ?? '', last_name: parts.slice(1).join(' '), date_of_birth: boot.date_of_birth ?? '' },
        profileError: '', profileDone: false,
        pw: { current_password: '', password: '', password_confirmation: '' }, pwError: '', pwDone: false,
        prefs: Object.assign({ all: true, plan_purchased: true, plan_expiry: true, account_updates: true }, boot.notify_prefs ?? {}),
        notifSaved: false,
        confirming: false, notice: '', cancelError: '',

        init() {
            fetch('/api/tools', { headers: H }).then((r) => r.ok ? r.json() : null)
                .then((d) => { if (d && d.meters) this.meters = d.meters; }).catch(() => {});
        },
        initials() {
            return (((this.profile.first_name[0] ?? '') + (this.profile.last_name[0] ?? '')).toUpperCase()) || 'U';
        },
        pct(m) {
            return m.limit > 0 ? Math.min(100, Math.round((m.used / m.limit) * 100)) : 0;
        },

        async saveProfile() {
            this.busy = true; this.profileError = ''; this.profileDone = false;
            try {
                const res = await fetch('/api/account/profile', {
                    method: 'PUT', headers: H,
                    body: JSON.stringify({ ...this.profile, date_of_birth: this.profile.date_of_birth || null }),
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) { this.profileError = err(data); return; }
                this.profileDone = true;
                setTimeout(() => { this.profileDone = false; location.reload(); }, 1200);
            } finally { this.busy = false; }
        },

        async savePassword() {
            this.busy = true; this.pwError = ''; this.pwDone = false;
            try {
                const res = await fetch('/api/account/password', { method: 'PUT', headers: H, body: JSON.stringify(this.pw) });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) { this.pwError = err(data); return; }
                this.pwDone = true;
                this.pw = { current_password: '', password: '', password_confirmation: '' };
            } finally { this.busy = false; }
        },

        async savePrefs(next) {
            this.prefs = next; this.busy = true; this.notifSaved = false;
            try {
                await fetch('/api/account/notifications', { method: 'PUT', headers: H, body: JSON.stringify({ prefs: next }) });
                this.notifSaved = true;
                setTimeout(() => { this.notifSaved = false; }, 2000);
            } finally { this.busy = false; }
        },

        async cancelPlan() {
            this.busy = true; this.cancelError = '';
            try {
                const res = await fetch('/api/billing/cancel', { method: 'POST', headers: H });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) { this.cancelError = err(data); return; }
                this.notice = data.message;
                this.confirming = false;
                this.sub = { ...this.sub, cancel_at_period_end: true };
            } finally { this.busy = false; }
        },
    };
}
</script>
@endpush
@endsection
