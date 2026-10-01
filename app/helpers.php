<?php

use App\Services\WebContext;

/*
 * Blade-side helpers for the Laravel frontend. Thin wrappers around the
 * WebContext singleton so templates read naturally:
 *
 *   {{ t('hero.title', 'Create images…') }}
 *   {{ brand('brand_name') }}
 */

if (! function_exists('webctx')) {
    function webctx(): WebContext
    {
        return app(WebContext::class);
    }
}

if (! function_exists('t')) {
    /** Translate a UI string: current language → English → fallback. */
    function t(string $key, string $fallback = ''): string
    {
        return webctx()->t($key, $fallback);
    }
}

if (! function_exists('brand')) {
    /** Branding value by key, or the whole branding array with no args. */
    function brand(?string $key = null, $default = null)
    {
        $b = webctx()->branding();

        return $key === null ? $b : ($b[$key] ?? $default);
    }
}
