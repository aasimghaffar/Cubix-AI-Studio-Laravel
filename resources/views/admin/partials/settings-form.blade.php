{{-- Config-driven settings form — port of SettingsForm.jsx. Pages pass a
     $config array (title, intro, sections, saveLabel); the whole form is
     rendered by Alpine from that config, exactly like the React component:
     masked saved secrets, per-provider Test buttons, dropdown / longtext /
     choice / toggle / number / color / text field types, and showWhen
     conditional visibility. --}}
<div class="w-full" x-data='settingsForm(@json($config))' x-init="load()">
    <h1 class="font-display text-2xl font-bold text-white mb-1" x-text="cfg.title"></h1>
    <p class="text-slate-400 mb-8 text-sm" x-text="cfg.intro"></p>

    <template x-for="section in cfg.sections" :key="section.title">
        <div class="card p-6 mb-5">
            <h2 class="font-display font-semibold text-white mb-1" x-show="cfg.title !== section.title" x-text="section.title"></h2>
            <p class="text-xs text-slate-500 mb-4" x-show="section.note && cfg.intro !== section.note" x-text="section.note"></p>
            <div class="space-y-4 mt-4">
                <template x-for="f in section.keys.filter((f) => !f.showWhen || (values[f.showWhen.key] ?? f.showWhen.fallback ?? 'test') === f.showWhen.value)" :key="f.key">
                    <div>
                        <div class="flex items-center justify-between gap-3 mb-1.5">
                            <label class="block text-sm text-slate-300" x-text="f.label"></label>
                            <template x-if="f.help_url">
                                <a :href="f.help_url" target="_blank" rel="noreferrer"
                                    class="shrink-0 inline-flex items-center gap-1 text-xs text-brand hover:underline">
                                    <span x-text="f.help_label || 'Get your key'"></span> <x-icon name="ExternalLink" :size="11" />
                                </a>
                            </template>
                        </div>
                        <div class="flex gap-2">
                            {{-- dropdown --}}
                            <template x-if="f.type === 'dropdown'">
                                <select class="input" :value="values[f.key] ?? f.default" @change="values[f.key] = $event.target.value">
                                    <template x-for="opt in f.options" :key="opt.value">
                                        <option :value="opt.value" :selected="(values[f.key] ?? f.default) === opt.value" x-text="opt.label"></option>
                                    </template>
                                </select>
                            </template>
                            {{-- longtext --}}
                            <template x-if="f.type === 'longtext'">
                                <textarea class="input min-h-20" :placeholder="f.placeholder ?? ''"
                                    :value="values[f.key] ?? ''" @input="values[f.key] = $event.target.value"></textarea>
                            </template>
                            {{-- choice (segmented buttons) --}}
                            <template x-if="f.type === 'choice'">
                                <div class="flex rounded-xl border border-ink-700 overflow-hidden">
                                    <template x-for="opt in f.options" :key="opt.value">
                                        <button type="button" @click="values[f.key] = opt.value"
                                            class="px-5 py-2.5 text-sm transition"
                                            :class="(values[f.key] ?? f.default) === opt.value
                                                ? 'bg-brand text-ink-950 font-semibold'
                                                : 'text-slate-300 hover:bg-ink-800'"
                                            x-text="opt.label"></button>
                                    </template>
                                </div>
                            </template>
                            {{-- toggle --}}
                            <template x-if="f.type === 'toggle'">
                                <select class="input" :value="values[f.key] ?? '1'" @change="values[f.key] = $event.target.value">
                                    <option value="1">Enabled</option>
                                    <option value="0">Disabled</option>
                                </select>
                            </template>
                            {{-- number --}}
                            <template x-if="f.type === 'number'">
                                <input class="input" type="number" min="1" max="30"
                                    :value="values[f.key] ?? '3'" @input="values[f.key] = $event.target.value">
                            </template>
                            {{-- color --}}
                            <template x-if="f.type === 'color'">
                                <span class="flex items-center gap-3">
                                    <input type="color" :value="values[f.key] || '#000000'"
                                        class="h-11 w-16 rounded-xl border border-ink-700 bg-transparent cursor-pointer p-1"
                                        @input="values[f.key] = $event.target.value">
                                    <input class="input !w-32" type="text" placeholder="#hex or empty"
                                        :value="values[f.key] ?? ''" @input="values[f.key] = $event.target.value">
                                    <button type="button" x-show="values[f.key]" class="text-xs text-slate-400 hover:text-red-400"
                                        @click="values[f.key] = ''">Reset</button>
                                </span>
                            </template>
                            {{-- plain text (default) --}}
                            <template x-if="!f.type">
                                <input class="input" type="text" :placeholder="f.placeholder ?? ''"
                                    :value="values[f.key] ?? ''" @input="values[f.key] = $event.target.value">
                            </template>

                            {{-- Test button (keyed providers, or testable dropdowns) --}}
                            <template x-if="f.provider || (f.testable && values[f.key])">
                                <button type="button" @click="test(f.provider || values[f.key])"
                                    class="shrink-0 inline-flex items-center gap-2 px-4 rounded-xl border border-ink-700 text-sm text-slate-300 hover:border-brand/60">
                                    <span x-show="tests[f.provider || values[f.key]] === 'busy'" class="inline-block animate-spin"><x-icon name="Loader2" :size="15" /></span>
                                    <span x-show="tests[f.provider || values[f.key]]?.ok === true"><x-icon name="CircleCheck" :size="15" class="text-brand" /></span>
                                    <span x-show="tests[f.provider || values[f.key]]?.ok === false"><x-icon name="CircleX" :size="15" class="text-red-400" /></span>
                                    <span x-show="tests[f.provider || values[f.key]] === undefined"><x-icon name="Plug" :size="15" /></span>
                                    Test
                                </button>
                            </template>
                        </div>
                        <p class="text-xs mt-1" x-show="tests[f.provider || values[f.key]]?.message"
                            :class="tests[f.provider || values[f.key]]?.ok ? 'text-brand' : 'text-red-400'"
                            x-text="tests[f.provider || values[f.key]]?.message"></p>
                    </div>
                </template>
            </div>
        </div>
    </template>

    <button type="button" class="btn-brand" @click="save()" :disabled="busy"
        x-text="busy ? 'Saving…' : saved ? 'Saved ✓' : cfg.saveLabel"></button>
</div>
