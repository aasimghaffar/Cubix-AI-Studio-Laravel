/**
 * Cubix AI Studio — Blade frontend behaviour.
 * Direct port of the React app's client-side effects: theme switching with
 * admin colour overrides, spotlight/magnetic/tilt/ripple micro-interactions,
 * the hero typewriter, and the session-scoped splash loader. Alpine.js
 * handles dropdowns/sliders declaratively in the templates.
 */
(function () {
  'use strict';

  /* ── Theme (dark / light) with admin colour overrides ───────────────── */

  var THEME_COLORS = window.__THEME_COLORS || {};

  function applyThemeColors(theme) {
    var root = document.documentElement;
    var bg = theme === 'light' ? THEME_COLORS.light_bg : THEME_COLORS.dark_bg;
    var text = theme === 'light' ? THEME_COLORS.light_text : THEME_COLORS.dark_text;
    if (bg) { root.style.setProperty('--ink-950', hexToRgb(bg)); } else { root.style.removeProperty('--ink-950'); }
    if (text) { document.body.style.color = text; } else { document.body.style.color = ''; }
  }

  function hexToRgb(hex) {
    var m = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return m ? parseInt(m[1], 16) + ' ' + parseInt(m[2], 16) + ' ' + parseInt(m[3], 16) : hex;
  }

  function setTheme(theme) {
    document.documentElement.classList.toggle('theme-light', theme === 'light');
    try { localStorage.setItem('theme', theme); } catch (e) { /* private mode */ }
    applyThemeColors(theme);
    document.querySelectorAll('[data-theme-icon]').forEach(function (el) {
      el.dataset.themeIcon = theme;
    });
  }

  window.cubixTheme = {
    current: function () {
      try { return localStorage.getItem('theme') || 'dark'; } catch (e) { return 'dark'; }
    },
    toggle: function () {
      setTheme(window.cubixTheme.current() === 'light' ? 'dark' : 'light');
    },
  };

  // Apply saved theme immediately (a tiny inline script in <head> already
  // toggled the class to prevent a flash; this syncs colours + icons).
  setTheme(window.cubixTheme.current());

  /* ── Micro-interactions (port of lib/fx.js) ─────────────────────────── */

  if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    document.addEventListener('pointermove', function (e) {
      var t = e.target;
      if (!t || !t.closest) return;

      var card = t.closest('.spotlight');
      if (card) {
        var r = card.getBoundingClientRect();
        card.style.setProperty('--mx', (e.clientX - r.left) + 'px');
        card.style.setProperty('--my', (e.clientY - r.top) + 'px');
      }

      var mag = t.closest('.magnetic');
      if (mag) {
        var mr = mag.getBoundingClientRect();
        var dx = e.clientX - (mr.left + mr.width / 2);
        var dy = e.clientY - (mr.top + mr.height / 2);
        mag.style.transform = 'translate(' + dx * 0.12 + 'px, ' + dy * 0.18 + 'px)';
      }

      var tilt = t.closest('.tilt');
      if (tilt) {
        var tr = tilt.getBoundingClientRect();
        var px = (e.clientX - tr.left) / tr.width - 0.5;
        var py = (e.clientY - tr.top) / tr.height - 0.5;
        tilt.style.transform = 'perspective(900px) rotateY(' + px * 6 + 'deg) rotateX(' + (-py * 6) + 'deg) translateY(-2px)';
      }
    }, { passive: true });

    document.addEventListener('pointerout', function (e) {
      var t = e.target;
      if (!t || !t.closest) return;
      var mag = t.closest('.magnetic');
      if (mag && !mag.contains(e.relatedTarget)) mag.style.transform = '';
      var tilt = t.closest('.tilt');
      if (tilt && !tilt.contains(e.relatedTarget)) tilt.style.transform = '';
    }, { passive: true });

    document.addEventListener('click', function (e) {
      var btn = e.target.closest && e.target.closest('button, .btn-brand, .btn-ghost, a[class*="btn"]');
      if (!btn || btn.disabled) return;
      var r = btn.getBoundingClientRect();
      var ripple = document.createElement('span');
      ripple.className = 'fx-ripple';
      var size = Math.max(r.width, r.height) * 2;
      ripple.style.width = ripple.style.height = size + 'px';
      ripple.style.left = (e.clientX - r.left - size / 2) + 'px';
      ripple.style.top = (e.clientY - r.top - size / 2) + 'px';
      var cs = getComputedStyle(btn);
      if (cs.position === 'static') btn.style.position = 'relative';
      if (cs.overflow !== 'hidden') btn.style.overflow = 'hidden';
      btn.appendChild(ripple);
      setTimeout(function () { ripple.remove(); }, 650);
    }, { passive: true });
  }

  /* ── Header shadow on scroll ────────────────────────────────────────── */

  var header = null;
  function onScroll() {
    if (!header) header = document.querySelector('[data-site-header]');
    if (header) header.classList.toggle('nav-scrolled', window.scrollY > 24);
  }
  window.addEventListener('scroll', onScroll, { passive: true });
  document.addEventListener('DOMContentLoaded', onScroll);

  /* ── Hero typewriter (port of <TypeLine/>) ──────────────────────────── */

  document.addEventListener('DOMContentLoaded', function () {
    var el = document.querySelector('[data-typewriter]');
    if (!el) return;
    var phrases;
    try { phrases = JSON.parse(el.dataset.typewriter); } catch (e) { return; }
    if (!phrases || !phrases.length) return;

    var i = 0, txt = '', del = false;
    function tick() {
      var full = phrases[i % phrases.length];
      if (!del) {
        txt = full.slice(0, txt.length + 1);
        el.textContent = txt;
        if (txt === full) { setTimeout(function () { del = true; tick(); }, 1400); return; }
      } else {
        txt = full.slice(0, Math.max(0, txt.length - 2));
        el.textContent = txt;
        if (!txt) { del = false; i += 1; }
      }
      setTimeout(tick, del ? 24 : 46);
    }
    tick();
  });

  /* ── Splash loader: hide once the page is ready (once per session) ──── */

  document.addEventListener('DOMContentLoaded', function () {
    var loader = document.getElementById('site-loader');
    if (!loader) return;
    var shown = false;
    try { shown = sessionStorage.getItem('loaderShown') === '1'; } catch (e) { /* ignore */ }
    if (shown) { loader.remove(); return; }

    function finish() {
      if (!loader) return;
      loader.classList.add('site-loader-out');
      try { sessionStorage.setItem('loaderShown', '1'); } catch (e) { /* ignore */ }
      setTimeout(function () { loader && loader.remove(); loader = null; }, 600);
    }
    setTimeout(finish, document.readyState === 'complete' ? 700 : 1600);
    setTimeout(finish, 2500); // safety: never trap the visitor
  });
})();

/* ── Admin settings form (port of SettingsForm.jsx) ─────────────────── */
function settingsForm(cfg) {
  var csrf = document.querySelector('meta[name="csrf-token"]');
  var H = { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf ? csrf.content : '' };
  var groupFor = function (key) {
    return key.indexOf('brand') === 0 ? 'branding'
      : key.indexOf('stripe') === 0 ? 'payment'
      : key.indexOf('notify') === 0 ? 'notifications'
      : key.indexOf('engine_') === 0 ? 'engines'
      : (key.slice(-9) === '_provider' || key === 'currency_code' || key === 'free_limit_message') ? 'general'
      : 'ai_keys';
  };
  return {
    cfg: cfg, values: {}, tests: {}, saved: false, busy: false,
    async load() {
      var res = await fetch('/api/admin/settings', { headers: H });
      if (!res.ok) return;
      var rows = await res.json();
      var v = {};
      rows.forEach(function (r) { v[r.key] = r.value == null ? '' : r.value; });
      this.values = v;
    },
    async save() {
      this.busy = true; this.saved = false;
      await fetch('/api/admin/settings', {
        method: 'PUT', headers: H,
        body: JSON.stringify({ settings: Object.entries(this.values).map(function (e) { return { key: e[0], value: e[1], group: groupFor(e[0]) }; }) }),
      });
      this.busy = false; this.saved = true;
      var self = this;
      setTimeout(function () { self.saved = false; }, 2500);
    },
    async test(provider) {
      this.tests[provider] = 'busy';
      // Send everything currently typed in the form — no need to save first.
      var overrides = {};
      Object.entries(this.values).forEach(function (e) {
        if (typeof e[1] === 'string' && e[1] !== '' && e[1].indexOf('\u2022') === -1) overrides[e[0]] = e[1];
      });
      try {
        var res = await fetch('/api/admin/settings/test/' + provider, { method: 'POST', headers: H, body: JSON.stringify({ overrides: overrides }) });
        this.tests[provider] = await res.json();
      } catch (e) {
        this.tests[provider] = { ok: false, message: 'Could not run the test.' };
      }
    },
  };
}

/* ── Tool access gate (port of useToolGate + checkout) ──────────────── */
function toolGate(authed, hasPlan, gateways, extra) {
  var meta = document.querySelector('meta[name="csrf-token"]');
  var csrf = meta ? meta.content : '';
  var base = {
    authed: authed, hasPlan: hasPlan, gateways: gateways || [],
    gate: null, gateCycle: 'monthly', gateBusy: null, gateChoosing: null, gateError: '',

    openTool: function (slug, isFree) {
      var path = '/tools/' + slug;
      if (!this.authed) { this.gate = 'login'; return; }
      if (!this.hasPlan && !isFree) { this.gate = 'plans'; return; }
      window.location.href = path;
    },

    gateCheckout: function (pkgId) {
      this.gateError = '';
      if (this.gateways.length === 0) { this.gateError = 'Payments are not configured yet — please contact us.'; return; }
      if (this.gateways.length === 1) { this.gateStart(this.gateways[0], pkgId); return; }
      this.gateChoosing = pkgId;
    },
    gateGo: function (gateway) { var id = this.gateChoosing; this.gateChoosing = null; this.gateStart(gateway, id); },
    gateStart: async function (gateway, pkgId) {
      this.gateBusy = pkgId;
      try {
        var res = await fetch(gateway === 'paypal' ? '/checkout/paypal' : '/checkout/stripe', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
          body: JSON.stringify({ package_id: pkgId }),
        });
        var data = await res.json().catch(function () { return {}; });
        if (!res.ok || !data.checkout_url) {
          this.gateError = data.message || 'Could not start checkout — please try again.';
          this.gateBusy = null;
          return;
        }
        window.location.href = data.checkout_url;
      } catch (e) {
        this.gateError = 'Could not start checkout — please try again.';
        this.gateBusy = null;
      }
    },
  };
  return Object.assign(base, extra || {});
}
