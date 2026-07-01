# بهنام (Behnam) — Persian RTL Cosmetics & Hygiene E‑commerce

A production‑grade, fully RTL Persian storefront built with **PHP 8.3 + MySQL 8**, a custom MVC core
(Repository + Service layers, REST API), **Tailwind CSS** compiled to a single static file, and
vanilla ES6 + **jQuery/AJAX**. No Node.js runtime, no heavy framework.

> This repository currently implements **Phase 1 — Foundation + Storefront slice**:
> Home, Category (filter/sort/load‑more), Product (gallery, variants, Aparat video, specs, reviews),
> and an AJAX Cart. Later phases (OTP checkout, payments, admin panel, blog, etc.) are described in the
> project plan.

---

## Requirements
- **PHP 8.3** (with `pdo_mysql`, `mbstring`) — bundled with **Laragon**.
- **MySQL 8** (Laragon bundles MySQL 8.4).
- Composer is **not** required (the app ships a custom PSR‑4 autoloader).

## Quick start (Laragon)

```powershell
# 1) Configure environment
copy .env.example .env
#    then edit .env if your DB user/password differ (Laragon default: root / empty)

# 2) Create schema + demo data + admin account
& "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" database\migrate.php --fresh
& "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" database\seed.php
& "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" database\seed_admin.php

# 3) Build the CSS (standalone Tailwind CLI — no Node needed)
powershell -ExecutionPolicy Bypass -File tools\build.ps1

# 4) Run it
powershell -ExecutionPolicy Bypass -File tools\serve.ps1     # http://127.0.0.1:8000
```

Open **http://127.0.0.1:8000** (storefront) and **http://127.0.0.1:8000/admin** (admin panel).

### Admin panel
WooCommerce-style RTL admin at **`/admin`**. Default login: **`admin` / `admin1234`** (change it after first login). Full CRUD for products (images, specs, variants, tags, flags, SEO), categories, brands, tags, a menu builder that drives the storefront header, order management (status/tracking/payment confirmation with SMS), customers, and site settings. Roles: `super`, `manager`, `editor` (capability-based access).

### Alternative: Laragon/Apache virtual host
Point a vhost `behnam.test` at the project's **`public/`** directory (Laragon → Menu → Apache →
sites‑enabled, or drop the project under `C:\laragon\www`). URLs are host‑relative, so the site works
under any hostname. Set `APP_URL` in `.env` to match (used for canonical/OG tags).

---

## Development

| Task | Command |
|------|---------|
| Rebuild CSS once | `tools\build.ps1` |
| Rebuild CSS on change | `tools\build.ps1 -Watch` |
| Reset DB + reseed | `php database\migrate.php --fresh` then `php database\seed.php` |
| Serve locally | `tools\serve.ps1 -Port 8000` |

The Tailwind standalone CLI (`tools/tailwindcss.exe`, git‑ignored) is downloaded per machine from the
[Tailwind releases](https://github.com/tailwindlabs/tailwindcss/releases) (Windows x64 binary).

---

## Architecture

```
public/           Web root (front controller, .htaccess, compiled assets, uploads)
app/
  Core/           Router, Request/Response, View, DB, BaseRepository, Csrf, Session,
                  RateLimiter, Validator, Config, Env, Bootstrap, middleware contract
  Controllers/    Storefront\*, Api\*  (thin)
  Repositories/   PDO prepared‑statement data access
  Services/       CatalogService, CartService, SettingsService, RecentlyViewed
  Middleware/     SecurityHeaders, VerifyCsrf, ThrottleRequests
  Support/        helpers.php (fa/money/e/url/asset/jdate…), Jalali, Html sanitizer
views/            layouts, partials (header, product-card…), storefront pages, errors
routes/           web.php, api.php
config/           app, database, sms, payment, shipping
database/         migrate.php, seed.php, migrations/*.sql
resources/tailwind/  input.css, tailwind.config.js
tools/            tailwindcss.exe, build.ps1, serve.ps1, dev-router.php
```

### Security baseline (built in from day one)
- All SQL via **PDO prepared statements** (no string‑interpolated queries).
- **CSRF** token on every mutating request (form field + `X-CSRF-Token` header).
- **XSS**: output escaping everywhere; product HTML passed through an allowlist sanitizer that
  permits only safe formatting + a validated Aparat embed.
- **CSP** + security headers (middleware and `.htaccess`), hardened **sessions**, per‑IP
  **rate limiting** on the API.

### Admin‑configurable settings (`settings` table)
`show_stock_qty`, `low_stock_threshold`, `free_shipping_threshold`, `announcement_text`,
`show_announcement`, `flash_sale_ends_at`, `brand_name`. E.g. set `show_stock_qty=0` to hide exact
stock counts from customers.

---

## License
Proprietary — © بهنام. All rights reserved.
