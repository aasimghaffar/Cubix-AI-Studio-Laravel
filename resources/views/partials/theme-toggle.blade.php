{{-- Dark/light switch. app.js flips the class + saves the choice; the two
     icons swap via the .theme-icon-* rules appended to app.css. --}}
<button type="button" onclick="cubixTheme.toggle()" aria-label="Toggle light/dark mode"
    class="p-2.5 rounded-lg border border-ink-700 text-slate-300 hover:border-brand/60 transition">
    <span class="theme-icon-sun"><x-icon name="Sun" :size="15" /></span>
    <span class="theme-icon-moon"><x-icon name="Moon" :size="15" /></span>
</button>
