<?php

namespace App\Services;

use App\Models\Language;
use App\Models\MenuItem;

/**
 * Per-request context for the Blade frontend: current language + t(),
 * branding, and the navigation menu. Mirrors the React contexts
 * (LanguageContext / AuthContext.branding) so views translate and brand
 * exactly like the SPA did.
 *
 * Registered as a singleton — everything is loaded once per request.
 */
class WebContext
{
    protected ?array $languages = null;

    protected ?array $branding = null;

    protected $menu = null;

    public function __construct(protected SettingsService $settings)
    {
    }

    /* ── Languages & translation ───────────────────────────────── */

    /** All enabled languages, ordered — shape matches GET /api/languages. */
    public function languages(): array
    {
        if ($this->languages === null) {
            $this->languages = Language::where('enabled', true)
                ->orderBy('sort_order')
                ->get(['code', 'name', 'native_name', 'dir', 'translations'])
                ->all();
        }

        return $this->languages;
    }

    /** The visitor's chosen language code (cookie), defaulting to English. */
    public function code(): string
    {
        $code = request()->cookie('lang', 'en');
        foreach ($this->languages() as $l) {
            if ($l->code === $code) {
                return $code;
            }
        }

        return 'en';
    }

    public function current(): ?Language
    {
        foreach ($this->languages() as $l) {
            if ($l->code === $this->code()) {
                return $l;
            }
        }

        return $this->english();
    }

    public function english(): ?Language
    {
        foreach ($this->languages() as $l) {
            if ($l->code === 'en') {
                return $l;
            }
        }

        return null;
    }

    /** True when the current language reads right-to-left (Arabic etc.). */
    public function rtl(): bool
    {
        return ($this->current()?->dir ?? 'ltr') === 'rtl';
    }

    /**
     * Translate a key. Fallback chain identical to the React app:
     * current language → English → provided fallback text.
     */
    public function t(string $key, string $fallback = ''): string
    {
        return $this->current()?->translations[$key]
            ?? $this->english()?->translations[$key]
            ?? ($fallback !== '' ? $fallback : $key);
    }

    /* ── Branding ──────────────────────────────────────────────── */

    /** Same payload the SPA read from /api/auth (branding key). */
    public function branding(): array
    {
        return $this->branding ??= $this->settings->branding();
    }

    /* ── Navigation menu ───────────────────────────────────────── */

    /** Enabled top-level items with enabled children — matches GET /api/menu. */
    public function menu()
    {
        if ($this->menu === null) {
            $this->menu = MenuItem::whereNull('parent_id')
                ->where('enabled', true)
                ->with(['children' => fn ($q) => $q->where('enabled', true)->orderBy('sort_order')])
                ->orderBy('sort_order')
                ->get();
        }

        return $this->menu;
    }

    /** Flattened menu for footers — children promoted, separators dropped. */
    public function footerMenu(): array
    {
        $flat = [];
        foreach ($this->menu() as $item) {
            if ($item->children->count()) {
                foreach ($item->children as $child) {
                    $flat[] = $child;
                }
            } else {
                $flat[] = $item;
            }
        }

        return array_values(array_filter($flat, fn ($m) => $m->target !== '#'));
    }

    /**
     * Translated label for a menu item — core nav key first, then the
     * admin-editable menu.<id> key, then the raw label. Mirrors menuLabel()
     * from the React PublicLayout.
     */
    public function menuLabel($item): string
    {
        $core = ['/' => 'nav.home', '/tools' => 'nav.tools', '/pricing' => 'nav.pricing', '/contact' => 'nav.contact'];

        if ($item->type === 'core' && isset($core[$item->target])) {
            return $this->t($core[$item->target], $item->label);
        }

        return $this->t("menu.{$item->id}", $item->label);
    }

    /** URL for a menu item (pages live under /p/{slug}). */
    public function itemPath($item): string
    {
        return $item->type === 'page' ? "/p/{$item->target}" : $item->target;
    }

    public function isExternal($item): bool
    {
        return $item->type === 'link' && preg_match('/^https?:/i', $item->target);
    }
}
