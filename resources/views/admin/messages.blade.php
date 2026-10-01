@extends('layouts.admin')

@section('content')
<div x-data="adminMessages()" x-init="load()">
    <h1 class="font-display text-xl sm:text-2xl font-bold text-white mb-6">Contact messages</h1>
    <div class="space-y-3">
        <template x-for="msg in rows" :key="msg.id">
            <div class="card p-5" :class="msg.is_read ? 'opacity-60' : ''">
                <div class="flex items-start justify-between gap-4">
                    <button type="button" class="text-left flex-1 min-w-0" @click="open = (open === msg.id ? null : msg.id)">
                        <p class="text-white font-medium truncate" x-text="msg.subject || '(no subject)'"></p>
                        <p class="text-sm text-slate-400 mt-0.5"
                            x-text="`${msg.name} · ${msg.email} · ${new Date(msg.created_at).toLocaleString()}`"></p>
                    </button>
                    <div class="flex gap-2 shrink-0">
                        <button type="button" :title="msg.is_read ? 'Mark unread' : 'Mark read'" @click="toggleRead(msg)"
                            class="p-2 rounded-lg border border-ink-700 text-slate-400 hover:text-brand">
                            <span x-show="msg.is_read"><x-icon name="Mail" :size="15" /></span>
                            <span x-show="!msg.is_read"><x-icon name="MailOpen" :size="15" /></span>
                        </button>
                        <button type="button" title="Delete" @click="remove(msg)"
                            class="p-2 rounded-lg border border-ink-700 text-slate-400 hover:text-red-400">
                            <x-icon name="Trash2" :size="15" />
                        </button>
                    </div>
                </div>
                <p x-show="open === msg.id" x-cloak
                    class="text-sm text-slate-300 mt-4 pt-4 border-t border-ink-700/60 whitespace-pre-wrap" x-text="msg.message"></p>
            </div>
        </template>
        <div x-show="loaded && rows.length === 0" x-cloak class="card p-10 text-center text-slate-500 text-sm">No messages yet.</div>
    </div>
</div>

@push('scripts')
<script>
function adminMessages() {
    const H = { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content };
    return {
        rows: [], open: null, loaded: false,
        async load() {
            const res = await fetch('/api/admin/messages', { headers: H });
            if (res.ok) { const p = await res.json(); this.rows = p.data ?? []; }
            this.loaded = true;
        },
        async toggleRead(msg) { await fetch(`/api/admin/messages/${msg.id}/read`, { method: 'POST', headers: H }); this.load(); },
        async remove(msg) {
            if (!confirm('Delete this message?')) return;
            await fetch(`/api/admin/messages/${msg.id}`, { method: 'DELETE', headers: H });
            this.open = null; this.load();
        },
    };
}
</script>
@endpush
@endsection
