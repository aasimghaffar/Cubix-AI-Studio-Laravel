@extends('layouts.admin')

@section('content')
<div x-data="adminDashboard()" x-init="load()">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-8">
        <h1 class="font-display text-xl sm:text-2xl font-bold text-white">Admin dashboard</h1>
        <button type="button" @click="demoOpen = true"
            class="inline-flex items-center gap-2 text-xs px-4 py-2.5 rounded-xl border border-ink-700 text-slate-300 hover:border-brand/60 hover:text-white transition">
            <x-icon name="DatabaseZap" :size="14" /> Apply demo data
        </button>
    </div>

    <p class="text-slate-400" x-show="!stats">Loading…</p>

    <template x-if="stats">
        <div>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5 mb-6">
                <div class="min-w-0 card-glow p-5">
                    <span class="icon-tile !p-2 mb-3"><x-icon name="DollarSign" :size="18" /></span>
                    <p class="font-display text-xl sm:text-2xl font-bold text-white truncate" x-text="'$' + Number(stats.total_revenue).toLocaleString()"></p>
                    <p class="text-xs text-slate-400 mt-1">Monthly recurring revenue</p>
                </div>
                <div class="min-w-0 card-glow p-5">
                    <span class="icon-tile !p-2 mb-3"><x-icon name="UserCheck" :size="18" /></span>
                    <p class="font-display text-xl sm:text-2xl font-bold text-white truncate" x-text="stats.active_subscribers"></p>
                    <p class="text-xs text-slate-400 mt-1">Active subscribers</p>
                </div>
                <div class="min-w-0 card-glow p-5">
                    <span class="icon-tile !p-2 mb-3"><x-icon name="Users" :size="18" /></span>
                    <p class="font-display text-xl sm:text-2xl font-bold text-white truncate" x-text="stats.total_customers"></p>
                    <p class="text-xs text-slate-400 mt-1">Total customers</p>
                </div>
                <div class="min-w-0 card-glow p-5">
                    <span class="icon-tile !p-2 mb-3"><x-icon name="Activity" :size="18" /></span>
                    <p class="font-display text-xl sm:text-2xl font-bold text-white truncate"
                        x-text="stats.tool_usage.reduce((s, t) => s + Number(t.runs), 0).toLocaleString()"></p>
                    <p class="text-xs text-slate-400 mt-1">Tool runs (30 days)</p>
                </div>
            </div>

            <div class="grid lg:grid-cols-2 gap-5 mb-5">
                <div class="min-w-0 card p-6">
                    <h2 class="font-display font-semibold text-white mb-1">Activity — last 14 days</h2>
                    <p class="text-xs text-slate-500 mb-4">Tool runs per day across all customers</p>
                    <div class="relative h-56"><canvas x-ref="chartActivity"></canvas></div>
                </div>
                <div class="min-w-0 card p-6">
                    <h2 class="font-display font-semibold text-white mb-1">Most used tools</h2>
                    <p class="text-xs text-slate-500 mb-4">Runs in the last 30 days</p>
                    <p class="text-sm text-slate-500 py-16 text-center" x-show="stats.tool_usage.length === 0">No usage yet.</p>
                    <div class="relative h-56" x-show="stats.tool_usage.length > 0"><canvas x-ref="chartTools"></canvas></div>
                </div>
            </div>

            <div class="grid lg:grid-cols-2 gap-5">
                <div class="min-w-0 card p-6">
                    <h2 class="font-display font-semibold text-white mb-1">New customers</h2>
                    <p class="text-xs text-slate-500 mb-4">Sign-ups per month (6 months)</p>
                    <div class="relative h-52"><canvas x-ref="chartSignups"></canvas></div>
                </div>
                <div class="min-w-0 card p-6">
                    <h2 class="font-display font-semibold text-white mb-1">New subscription revenue</h2>
                    <p class="text-xs text-slate-500 mb-4">By month subscriptions started</p>
                    <div class="relative h-52"><canvas x-ref="chartRevenue"></canvas></div>
                </div>
            </div>
        </div>
    </template>

    <template x-teleport="body">
    {{-- Apply-demo-data confirmation (password required) --}}
    <div x-show="demoOpen" x-cloak class="fixed inset-0 z-50 grid place-items-center p-4 bg-ink-950/70 backdrop-blur-md" @click="!demoBusy && (demoOpen = false)">
        <div class="relative card p-7 w-full max-w-md animate-pop-in border-amber-400/40" @click.stop>
            <button type="button" @click="demoOpen = false" :disabled="demoBusy" aria-label="Close"
                class="absolute top-4 right-4 p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-ink-800 transition">
                <x-icon name="X" :size="18" />
            </button>
            <div class="flex items-start gap-3 mb-4">
                <span class="p-2.5 rounded-xl bg-amber-400/15 text-amber-400 shrink-0"><x-icon name="TriangleAlert" :size="20" /></span>
                <div>
                    <h2 class="font-display font-semibold text-white">Apply demo data?</h2>
                    <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                        This <strong class="text-amber-300">permanently deletes all current data</strong> — customers,
                        subscriptions, results, pages, settings — and reinstalls the professional demo content.
                        You will be signed out and can sign back in with <strong class="text-white">admin@example.com / password</strong>.
                    </p>
                </div>
            </div>
            <label class="text-xs text-slate-500 block mb-1.5">Type your account password to confirm</label>
            <input class="input" type="password" placeholder="Your admin password" x-model="demoPassword"
                @keydown.enter="demoPassword && runDemo()">
            <p class="text-sm text-red-400 mt-2" x-show="demoError" x-text="demoError" x-cloak></p>
            <div class="flex gap-3 mt-5">
                <button type="button" @click="runDemo()" :disabled="demoBusy || !demoPassword"
                    class="flex-1 rounded-xl px-5 py-2.5 text-sm font-semibold bg-amber-400/90 text-ink-950 hover:bg-amber-400 disabled:opacity-50 transition"
                    x-text="demoBusy ? 'Installing… (can take a minute)' : 'Yes, apply demo data'"></button>
                <button type="button" class="btn-ghost flex-1" @click="demoOpen = false" :disabled="demoBusy">Cancel</button>
            </div>
        </div>
    </div>
</template>
    </div>

@push('scripts')
<script>
function adminDashboard() {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const PALETTE = ['#0ea5a4', '#8b5cf6', '#f59e0b', '#ec4899', '#38bdf8', '#84cc16', '#f43f5e', '#a78bfa', '#2dd4bf'];
    // --brand holds space-separated channels ("14 165 164"); build proper
    // comma syntax so canvas gradients accept both rgb() and rgba() forms.
    const brandChannels = () => getComputedStyle(document.documentElement).getPropertyValue('--brand').trim().split(/\s+/).join(', ');
    const brandColor = () => `rgb(${brandChannels()})`;
    const prettyTool = (slug) => slug.replace('ai-', '').split('-').map((w) => w[0].toUpperCase() + w.slice(1)).join(' ');
    const tooltip = { backgroundColor: '#0d1524', borderColor: '#22304a', borderWidth: 1, cornerRadius: 12,
        titleColor: '#e2e8f0', bodyColor: '#e2e8f0', padding: 10 };
    const axis = { ticks: { color: '#64748b', font: { size: 11 } }, grid: { display: false }, border: { display: false } };

    return {
        stats: null, demoOpen: false, demoPassword: '', demoBusy: false, demoError: '',

        async load() {
            const res = await fetch('/api/admin/dashboard', { headers: { 'Accept': 'application/json' } });
            if (!res.ok) return;
            this.stats = await res.json();
            this.$nextTick(() => this.draw());
        },

        gradient(ctx, channels) {
            const g = ctx.createLinearGradient(0, 0, 0, 220);
            g.addColorStop(0, `rgba(${channels}, 0.5)`);
            g.addColorStop(1, `rgba(${channels}, 0)`);
            return g;
        },

        draw() {
            const s = this.stats, brand = brandColor();

            new Chart(this.$refs.chartActivity, {
                type: 'line',
                data: { labels: s.daily_activity.map((d) => new Date(d.day).toLocaleDateString(undefined, { day: 'numeric', month: 'short' })),
                    datasets: [{ data: s.daily_activity.map((d) => Number(d.runs)), borderColor: brand, borderWidth: 2.5,
                        fill: true, backgroundColor: (c) => this.gradient(c.chart.ctx, brandChannels()), tension: 0.4,
                        pointRadius: 0, pointHoverRadius: 4, pointBackgroundColor: brand }] },
                options: { maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip },
                    scales: { x: axis, y: { ...axis, beginAtZero: true, ticks: { ...axis.ticks, precision: 0 } } } },
            });

            if (s.tool_usage.length) {
                new Chart(this.$refs.chartTools, {
                    type: 'doughnut',
                    data: { labels: s.tool_usage.map((t) => prettyTool(t.tool_slug)),
                        datasets: [{ data: s.tool_usage.map((t) => Number(t.runs)),
                            backgroundColor: s.tool_usage.map((_, i) => PALETTE[i % PALETTE.length]), borderWidth: 0 }] },
                    options: { maintainAspectRatio: false, cutout: '55%',
                        plugins: { tooltip, legend: { position: 'bottom',
                            labels: { color: '#94a3b8', font: { size: 12 }, usePointStyle: true, pointStyle: 'circle', boxWidth: 8 } } } },
                });
            }

            new Chart(this.$refs.chartSignups, {
                type: 'line',
                data: { labels: s.monthly_growth.map((m) => m.month),
                    datasets: [{ data: s.monthly_growth.map((m) => Number(m.total)), borderColor: '#8b5cf6', borderWidth: 2.5,
                        fill: true, backgroundColor: (c) => this.gradient(c.chart.ctx, '139, 92, 246'), tension: 0.4,
                        pointRadius: 0, pointHoverRadius: 4, pointBackgroundColor: '#8b5cf6' }] },
                options: { maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip },
                    scales: { x: axis, y: { ...axis, beginAtZero: true, ticks: { ...axis.ticks, precision: 0 } } } },
            });

            new Chart(this.$refs.chartRevenue, {
                type: 'bar',
                data: { labels: s.revenue_by_month.map((m) => m.month),
                    datasets: [{ data: s.revenue_by_month.map((m) => Number(m.total)), backgroundColor: brand,
                        borderRadius: { topLeft: 8, topRight: 8 }, maxBarThickness: 42 }] },
                options: { maintainAspectRatio: false,
                    plugins: { legend: { display: false },
                        tooltip: { ...tooltip, callbacks: { label: (c) => ' $' + Number(c.raw).toLocaleString() } } },
                    scales: { x: axis, y: { ...axis, beginAtZero: true } } },
            });
        },

        async runDemo() {
            this.demoBusy = true; this.demoError = '';
            try {
                const res = await fetch('/api/admin/demo/install', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify({ password: this.demoPassword }),
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    this.demoError = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Could not install.');
                    this.demoBusy = false;
                    return;
                }
                alert(data.message);
                location.href = '/login';
            } catch (e) {
                this.demoError = 'Could not install.';
                this.demoBusy = false;
            }
        },
    };
}
</script>
@endpush
@endsection
