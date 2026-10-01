# Cubix AI Studio

A single Laravel 12 application — Blade frontend + REST API in one
codebase, served from one address. There is no separate frontend build:
Blade pages call the app's own `/api/*` JSON endpoints using the browser
session, and the compiled CSS/JS are already committed under
`public/assets/`, so nothing needs to be built before it runs.

---

## 1. Requirements

- **PHP 8.2 or 8.3**, with the extensions Laravel needs: mbstring,
  pdo_mysql, openssl, tokenizer, xml, ctype, json, bcmath, fileinfo, gd.
  XAMPP includes all of these by default.
- **MySQL** — XAMPP's bundled MySQL/MariaDB works fine.
- **[Composer](https://getcomposer.org)** — PHP's package manager.
- **Node.js** — *optional*, only needed if you plan to change a Tailwind
  CSS class and recompile `public/assets/app.css` yourself.

---

## 2. Project structure

```
app/
  Http/Controllers/
    Web/                     Blade page controllers — SiteController (home,
                              tools, pricing, contact, /p/{slug} pages,
                              workspace), AuthController (login, register,
                              Google sign-in bridge)
    Api/                     Public JSON endpoints — AccountController,
                              AuthController, BillingController (checkout,
                              cancel), ContactController, GoogleAuthController,
                              LanguagesController, MenuController,
                              PagesController, TestimonialsController,
                              ToolController (tool catalog, run, history)
    Api/Admin/                Admin-only JSON endpoints — Activity, Customer,
                              Dashboard, Package, Settings, Taxonomy,
                              ToolManager controllers
  Models/                    AiTool, ContactMessage, Generation, Language,
                              MenuItem, Package, Setting, SitePage,
                              Subscription, Taxonomy, Testimonial, UsageLog,
                              User
  Services/
    WebContext.php           Per-request context every Blade view uses:
                              current language + t() translation lookups,
                              branding, the nav menu
    AiService.php            Calls out to whichever AI provider is
                              configured per tool (OpenAI, Gemini, Claude,
                              DeepSeek, Mistral, Groq, Stability, Clipdrop,
                              remove.bg, ElevenLabs, or the free Pollinations
                              fallback for images)
    AutoTranslateService.php Registers every translatable string (tool
                              names, form fields, page titles) so Admin →
                              Languages → Auto-translate can machine-translate
                              anything new
    ContentTranslations.php  Hand-bundled offline translations (Spanish,
                              French, Arabic, Chinese) for demo content —
                              tool names, form fields, package names,
                              Legal/Privacy/FAQ page bodies — with no
                              internet dependency
    UiTranslations.php       Bundled translations for all the fixed site
                              chrome text (buttons, labels, nav)

resources/views/
  layouts/                   app.blade.php (public site shell, header/
                              footer/nav), admin.blade.php (admin shell +
                              sidebar), bare.blade.php (auth pages)
  partials/                  Reusable pieces — header variants, footer
                              variants, loaders, the language switcher,
                              the tool access gate popup, the pricing grid,
                              payment logos, the showcase carousel
  admin/                     All 18 admin pages: dashboard, customers,
                              messages, shortcodes, subscriptions, usage,
                              testimonials, taxonomies, tools, tool-editor,
                              packages, keys, engines, settings, pages,
                              menu, appearance, languages
  account.blade.php          Customer account page (profile, plan &
                              credits, password, notifications)
  home / tools / pricing /
  contact / page.blade.php   Public pages — page.blade.php also renders
                              [pricing] [tools] [stats] [cta] shortcodes
                              for any admin-created page
  workspace.blade.php        The actual per-tool workspace customers use
                              to generate content (dynamic form + results
                              + history, one template shared by all 9 tools)

public/assets/               Compiled Tailwind CSS (app.css) + all
                              interactivity (app.js, Alpine.js) — vendored,
                              nothing to build for a normal run
routes/web.php               Every Blade page route
routes/api.php                Every JSON API route (used by Blade's own
                              fetch() calls; not a separate public API)
database/migrations/         Schema
database/seeders/            DatabaseSeeder runs these in order:
                                1. AiToolSeeder    — the 9 AI tools, the
                                   default admin account, the 3 packages
                                2. LanguageSeeder  — the 5 supported
                                   languages and their base UI strings
                                3. PagesMenuSeeder — Legal/Terms/Privacy/
                                   FAQ pages + the site menu
                                4. DemoDataSeeder  — sample customers,
                                   testimonials, and demo settings
```

### How the frontend actually works

Every interactive Blade page defines an `x-data="somePageFunction()"`
[Alpine.js](https://alpinejs.dev/) component, whose logic lives either
inline in that Blade file's `@push('scripts')` block, or in
`public/assets/app.js` for logic shared across pages (the theme system,
the settings-form engine used by API Keys/Engines/Settings, the tool
access gate). These components call the app's own `/api/*` endpoints
using the browser's session cookie for authentication — there's no
separate token-based API to configure.

---

## 3. Setting up on a new computer/server, from nothing

This assumes nothing is installed yet. Every path below uses
**`cubix-ai-studio`** as the project folder name — keep that name so
every command below matches exactly.

### Step 1 — Install XAMPP and Composer

- XAMPP: https://www.apachefriends.org (default install location: `C:\xampp`)
- Composer: https://getcomposer.org/Composer-Setup.exe

Open the **XAMPP Control Panel** and click **Start** next to **Apache**
and **MySQL**.

### Step 2 — Extract the project

Extract the project zip into:

```
C:\xampp\htdocs\cubix-ai-studio
```

That folder should directly contain `artisan`, `app\`, `public\`,
`routes\`, etc.

**Note on `.env`:** you'll see `.env.example` in the project but no
`.env` — that's intentional. `.env` holds secrets (database password, API
keys) and is never shared; every computer creates its own from the
example, in Step 4 below.

### Step 3 — Install dependencies

```
cd C:\xampp\htdocs\cubix-ai-studio
composer install
```

Downloads everything the backend needs into a new `vendor\` folder (a few
hundred MB — normal, can take a few minutes).

### Step 4 — Set up the environment file

```
copy .env.example .env
php artisan key:generate
notepad .env
```

Set these values (leave everything else as-is):

```
APP_URL=http://localhost/cubix-ai-studio/public
FRONTEND_URL=http://localhost/cubix-ai-studio/public

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cubix_ai
DB_USERNAME=root
DB_PASSWORD=
```

`root` with an empty password is XAMPP's default MySQL login. `APP_URL`
and `FRONTEND_URL` must always match each other exactly.

### Step 5 — Enable Apache's URL rewriting (easy to miss)

XAMPP's Apache ignores Laravel's routing file by default until you turn
this on — without it, the homepage loads but every other page (tools,
admin, pricing) returns "Not Found." Open **Notepad as Administrator**
and edit:

```
C:\xampp\apache\conf\httpd.conf
```

Search (`Ctrl+F`) for `AllowOverride`. XAMPP's config often has **more
than one** `<Directory>` block — make sure **every** occurrence you find
says:

```apache
AllowOverride All
```

Also confirm this line is *not* commented out (no `#` at the start):

```apache
LoadModule rewrite_module modules/mod_rewrite.so
```

Save, then in the **XAMPP Control Panel**, click **Stop** then **Start**
next to Apache.

### Step 6 — Create the database

Open `http://localhost/phpmyadmin`, click **New**, name it `cubix_ai`,
click **Create**.

### Step 7 — Build the database tables and seed data

```
php artisan migrate
php artisan db:seed
```

`db:seed` (no `--class`) runs the full chain described in the structure
section above — it creates the admin account, the AI tools, languages,
the Legal/Terms/Privacy/FAQ pages, the menu, and sample demo customers,
all in one command. Skipping it, or running only one `--class`, is the
most common reason a fresh install looks empty or has no working admin
login.

### Step 8 — Link storage

```
php artisan storage:link
```

Makes uploaded/generated files (logo, AI-generated images and audio)
actually viewable in the browser — without this they'll look broken.

### Step 9 — Open the site

```
http://localhost/cubix-ai-studio/public
```

| Email | Password | What it is |
|---|---|---|
| `admin@example.com` | `password` | Admin panel access |
| `liam@example.com` | `password` | Customer with an active plan |
| `maya@example.com` | `password` | Customer with no plan (tests the upgrade gate) |

### Step 10 — (Optional, for later) Raise PHP's upload limits

Some tools accept file uploads (documents, images). If a tool complains a
file is too large, edit `C:\xampp\php\php.ini`:

```ini
upload_max_filesize = 25M
post_max_size = 25M
max_execution_time = 120
memory_limit = 256M
```

Save, then restart Apache again.

---

## 4. Applying an update to a site that's already running

Different from first-time setup above — **don't** repeat steps 1–6, and
don't run `composer install` or touch `.env` unless specifically told to.

1. Extract the update, **replacing existing files**.
2. ```
   php artisan optimize:clear
   ```
   The single most important step, and the one most often missed.
   Laravel caches routes and views for speed — without clearing that
   cache, pages can 404 or show up blank even though the new files are
   correctly in place.
3. Only if told the update includes new database content:
   ```
   php artisan db:seed
   ```
   Safe to re-run — it only adds what's missing or updates specific known
   keys, it doesn't erase your data.
4. **Hard refresh** your browser: `Ctrl + Shift + R` (a normal refresh
   can serve a cached copy of the page).

---

## 5. Useful commands while developing

```
php artisan optimize:clear     # clear all caches — run after any .env/route/view change
php artisan route:list         # see every registered route
php artisan tinker             # interactive PHP shell with the app booted
npm install && npm run css     # recompile Tailwind after changing a class (optional)
```

---

## 6. Troubleshooting

- **Homepage loads, every other page says "Not Found"** — Step 5 above
  (Apache `AllowOverride`/`mod_rewrite`) wasn't applied, or Apache wasn't
  restarted afterward.
- **A database connection error** — check `DB_DATABASE`/`DB_USERNAME`/
  `DB_PASSWORD` in `.env` match phpMyAdmin, then `php artisan
  optimize:clear`.
- **Admin login doesn't work / pages look empty** — `db:seed` wasn't run,
  or was run with a specific `--class` instead of the full chain.
- **Uploaded/generated images look broken** — `php artisan storage:link`
  was skipped.
- **A file upload says it's too large** — see Step 10.
- **A change you made doesn't seem to appear** — `php artisan
  optimize:clear`, then hard refresh (`Ctrl + Shift + R`).

---

## 7. Going live later (a real domain, not just your PC)

1. Point the domain's DNS at your server; the document root is the
   project's `public\` folder, same idea as Step 5.
2. In `.env`: `APP_URL`/`FRONTEND_URL` → your real domain with `https://`,
   `APP_ENV=production`, `APP_DEBUG=false`.
3. Update the **Google OAuth redirect URI** (Google Cloud Console) and
   the **Stripe/PayPal webhook URLs** to your live domain — the admin
   Settings page has notes on this too.
4. Set `MAIL_MAILER` to real SMTP credentials so account/plan emails
   actually send (currently just written to `storage/logs/laravel.log`).
5. Get an SSL certificate (most hosts offer free ones via Let's Encrypt).
6. Once everything above is confirmed working:
   ```
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
   Any future change still needs `php artisan optimize:clear` again.
