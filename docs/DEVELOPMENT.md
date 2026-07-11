# Development & Deployment Guide

## What's built so far

- **Phase 0 — Foundation** ✅ front controller + router, PDO wrapper, config
  loader, core classes (View, Csrf, Request, Controller, Database), autoloader,
  error/exception handling, sessions, security headers + HTTPS redirect via
  `.htaccess`, protected `app/` `storage/` `uploads/`.
- **Phase 1 — Public site** ✅ the approved prototype ported to DB-driven PHP
  views. Hero, countdown, stats, about, speakers, awards, tickets, sponsorship,
  exhibition booths, gallery, testimonials, newsletter — all content pulled from
  the database and scoped by `event_id`.
- **Phase 2 — Registration + tickets + Paystack** ✅ pass selector + attendee
  form (`/register`), server-side total recomputation, pending order + items,
  Paystack initialize → hosted checkout, callback verify-by-reference, webhook
  (HMAC-SHA512 signature check), **idempotent** ticket issuance with group-size
  expansion, QR codes (chillerlan/php-qrcode, SVG; placeholder until composer
  install), confirmation page + emailed passes (PHPMailer/Brevo). Core services:
  `Paystack`, `Mailer`, `Qr`, `Validator`, `RateLimit`, `TicketIssuer`.

- **Phase 3 — Admin dashboard** ✅ role-based login (`/admin/login`, argon2id/
  bcrypt, session regen, rate-limited), dashboard with revenue/orders/check-in
  stats, orders list + filter + detail + **CSV export**, **QR check-in scanner**
  (native BarcodeDetector camera + manual entry, idempotent admit/already/unpaid
  verdicts), speakers CRUD with secure image upload, editable site settings, and
  `audit_log` on every mutation. Roles: superadmin / editor / finance / checkin.

- **Phase 4 — Awards** ✅ public nomination form (`/awards/nominate`, rate-limited
  + honeypot, held as `pending` for review), admin moderation queue
  (approve / shortlist / reject), **email-verified public voting** (`/awards`):
  one vote per (category, email) enforced by a DB unique key, the tally only
  increments after the voter confirms via an emailed link, and confirming is
  idempotent (no double counts). Live tally at `/awards/results`. Admin awards
  console at `/admin/awards` (category show/hide + add, nomination moderation).

**Standalone pages** (added after Phase 4): `/about` (story, objectives, image
gallery section), `/sponsor` (full sponsorship tiers + exhibition + comp-pass
note + partnerships CTA), `/contact` (working message form → `contact_messages`
+ organiser email notification). The footer newsletter form now works site-wide
(`POST /newsletter/subscribe` → `newsletter_subscribers`, idempotent). Nav/footer
link to these pages; "Awards" now points to the voting page.

- **Phase 5 — Sponsor/Exhibitor portal** ✅ public application (`/sponsor/apply`,
  rate-limited + honeypot, optional logo upload), admin review queue
  (`/admin/sponsors`) with status workflow, and a **Confirm & provision** action
  that (idempotently) creates the sponsor's portal login, **auto-issues the
  tier's complimentary delegate passes** (source=`comp`, with QR), and emails
  credentials. Sponsor portal (`/portal`, separate `SponsorAuth`) shows comp
  passes + lets sponsors upload branding assets (logo / brochure ad / screen ad,
  MIME-validated) for admin approve/reject.

Still to come: Phase 6 (hardening).

### Sponsor portal access

Sponsors log in at `/portal/login` with credentials emailed on confirmation.
Comp passes are matched to a sponsor by holder email + `source='comp'`. Asset
uploads accept images + PDF (≤8 MB), stored with random filenames in the
no-exec `uploads/` dir.

### Admin access

Default login (from `seed.sql`): **admin@govtechconference.ng / admin1234** —
change the password immediately after first login. The QR check-in scanner lives
at `/admin/checkin`; scanning a ticket QR (which encodes `/checkin/verify?code=…`)
routes staff there. Camera scanning uses the browser's BarcodeDetector API
(Chrome/Edge/Android); other browsers fall back to manual code entry.

### Paystack setup (Phase 2)

1. Put **test** keys in `app/config/paystack.php` (`sk_test_…`, `pk_test_…`).
2. In the Paystack dashboard, set the webhook URL to
   `https://YOURDOMAIN/payment/webhook`. The webhook is the source of truth and
   is HMAC-verified; the browser callback (`/checkout/callback`) is a fast-path
   that also verifies-by-reference. Both call the same idempotent issuer, so a
   pass is never double-issued.
3. Test locally with Paystack test cards; flip to **live** keys at go-live.

### Ticket QR codes

QR codes are served (and disk-cached) from `/ticket/{code}/qr.png`, encoding the
public `/verify?code=…` URL. Generation order:
1. **chillerlan/php-qrcode** if installed (`composer install`) — fully local, no
   external calls. **Recommended for production.**
2. **Fallback** (no Composer): a real QR is fetched once from a public QR service
   and cached under `storage/qr/` (the service is hit at most once per ticket).
3. Last resort (no lib + no network): a labelled GD placeholder.

So QR works out of the box; running `composer install` just makes it fully
self-hosted. The disk cache means changing the QR library later requires clearing
`storage/qr/*.png`.

## Folder layout

```
app/            # NOT web-accessible (config, core, controllers, models, views)
storage/        # logs, qr cache (writable, not web-accessible)
database/       # schema.sql + seed.sql
public_html/    # WEBROOT: index.php front controller, .htaccess, assets/, uploads/
composer.json   # PHPMailer + chillerlan/php-qrcode (run composer install on host)
```

## Configuration

Real config files live in `app/config/*.php` and are **gitignored**. Copy each
`*.example` and fill it in:

```bash
cd app/config
cp config.php.example   config.php
cp database.php.example database.php
cp paystack.php.example paystack.php
cp mail.php.example     mail.php
# generate an app key:
php -r "echo bin2hex(random_bytes(32)).PHP_EOL;"   # paste into config.php app_key
```

## Local preview (no MySQL needed)

The PDO wrapper supports a `sqlite` driver for local dev only (production uses
MySQL). Point `app/config/database.php` at a SQLite file:

```php
<?php return ['driver' => 'sqlite', 'path' => '/absolute/path/to/dev.sqlite'];
```

Then serve the webroot with PHP's built-in server (a tiny router emulates the
`.htaccess` rewrite — serve real files, otherwise hand off to the front
controller):

```bash
# public_html/_devrouter.php
<?php
$f = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (is_file($f) && basename($f) !== '_devrouter.php') return false;
require __DIR__ . '/index.php';
```
```bash
php -S 127.0.0.1:8000 -t public_html public_html/_devrouter.php
# visit http://127.0.0.1:8000/
```

Set `'env' => 'development'` in `config.php` to see full error traces.

## Deploy to cPanel / hPanel

1. Upload the project. Point the domain **document root at `public_html/`**.
   Keep `app/`, `storage/`, `database/`, `vendor/` **outside** the webroot (one
   level up) if the host allows; otherwise the bundled `Require all denied`
   `.htaccess` files protect them.
2. `composer install --no-dev` on the host (or upload a local `vendor/`) to get
   PHPMailer + the QR library. The app runs without `vendor/` but email/QR
   (Phase 2) need it.
3. Create the MySQL database + user in cPanel, then import:
   ```bash
   mysql -u USER -p DBNAME < database/schema.sql
   mysql -u USER -p DBNAME < database/seed.sql
   ```
4. Fill `app/config/*.php` with real DB creds, Paystack **live** keys, Brevo SMTP.
5. Replace the placeholder admin password hash in `seed.sql`/the `users` table:
   ```bash
   php -r "echo password_hash('YOUR_PASSWORD', PASSWORD_ARGON2ID).PHP_EOL;"
   ```
6. Make `storage/logs`, `storage/qr`, `public_html/uploads` writable (755/775).
7. Confirm HTTPS works, then keep HSTS enabled in `public_html/.htaccess`.
8. (Phase 2) Set the Paystack webhook URL to `https://YOURDOMAIN/payment/webhook`.

## Engineering rules in force

Money in kobo (ints); PDO prepared statements only; CSRF on every POST; output
escaped via `e()`; secrets never in the webroot or git. See `CLAUDE.md`.
