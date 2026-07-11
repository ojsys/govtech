# CLAUDE.md — Nigeria GovTech Conference & Awards Platform

> Read this first. It is the operating context for building this project.
> Full detail lives in `docs/ARCHITECTURE.md`. The approved visual design is
> `docs/design-prototype.html` (open in a browser) — match it exactly.

## What we're building

A full event platform for the **Nigeria GovTech Conference & Awards** (organized by
the Bureau of Public Service Reforms, The Presidency). Replaces a static WordPress
site. Scope: public marketing site, attendee **registration + ticketing + Paystack
payments**, an **admin dashboard** (content, orders, QR check-in, exports), an
**awards** module (nominations + email-verified voting), and a **sponsor/exhibitor
portal** (applications, accounts, asset uploads, complimentary passes).

## Confirmed decisions

- **Design:** "Engraved Seal" direction — green `#0C7A4D` + gold `#C9A227` on forest-ink. Approved. Match `docs/design-prototype.html`.
- **Payment gateway:** **Paystack** (NGN).
- **Scope:** full platform (all modules above).

## Assumed defaults (change here if you disagree)

- **Framework:** vanilla structured PHP (PDO + a small front controller/router). No heavy framework — deploys to cPanel/hPanel by uploading a folder. *(Laravel is the alternative; only switch if the owner confirms.)*
- **Multi-edition:** yes — schema has an `events` table; always scope content by `event_id`.
- **Comp passes per sponsor tier:** Platinum 4 / Gold 2 / Silver 2 / Bronze 1 (in `sponsorship_packages.comp_passes`).
- **Voting:** public, **email-verified**, one vote per (category, email).

## Stack

- PHP 8.2+, MySQL 8 / MariaDB 10.4+ (InnoDB, utf8mb4)
- PDO (prepared statements only)
- PHPMailer over **Brevo SMTP** for transactional email
- `chillerlan/php-qrcode` for ticket QR codes (pure PHP; falls back to GD if needed)
- No build step for the front end — plain CSS/JS assets ported from the prototype

## Target folder layout

```
app/            # OUTSIDE webroot (or protected). config, core, controllers, models, views, middleware
storage/        # logs, qr cache (writable, not web-accessible)
database/       # schema.sql, seed.sql  (already provided)
vendor/         # composer deps
public_html/    # WEBROOT: index.php (front controller), .htaccess, assets/, uploads/
```
Keep `app/`, `storage/`, `config` out of the webroot, or drop `Require all denied`
`.htaccess` files into them. Secrets live in `app/config/*` — never in `public_html`, never committed.

## Non-negotiable engineering rules

1. **Money is always in kobo (integers).** Never floats. Totals are **recomputed server-side** from `ticket_types.price_kobo` — never trust posted prices/amounts.
2. **SQL:** PDO prepared statements everywhere. No string-concatenated queries.
3. **CSRF token** on every POST. Sessions: HttpOnly + Secure + SameSite=Lax; regenerate id on login.
4. **Passwords:** `password_hash()` (argon2id/bcrypt) + `password_verify()`.
5. **Paystack:** issue tickets ONLY after BOTH (a) verify-by-reference (`GET /transaction/verify/{ref}`) and (b) webhook signature check (HMAC-SHA512 of raw body with secret key). Make ticket issuance **idempotent** (guard on `orders.status` inside a DB transaction) so passes are never double-issued.
6. **Uploads:** validate MIME + extension + size; random filenames; no execute; ideally outside webroot.
7. **Voting / login / public forms:** rate-limited; honeypot or captcha on public forms.
8. **Force HTTPS** + security headers (HSTS, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, CSP) via `.htaccess`.
9. **Audit** every admin mutation into `audit_log`.
10. **Escape on output** (`htmlspecialchars`) — treat all DB/user content as untrusted in views.

## Paystack flow (summary)

`form → recompute totals server-side → create attendee+order(pending)+items → POST /transaction/initialize → redirect to authorization_url (or Inline popup) → callback verifies by reference → webhook (source of truth) confirms charge.success → idempotently issue tickets → QR + email`. See `docs/ARCHITECTURE.md §5`.

## Build order (do in sequence)

- **Phase 0 — Foundation:** folder skeleton, front controller + router, PDO wrapper, config loader, core classes (Auth, Csrf, Mailer, View, Validator, Paystack). Run `database/schema.sql` + `seed.sql`. Base layout/partials with the design tokens below.
- **Phase 1 — Public site:** port `docs/design-prototype.html` into PHP views; pull speakers, gallery, ticket types, sponsorship packages, award categories, countdown, testimonials from the DB.
- **Phase 2 — Registration + tickets:** multi-pass cart → order → Paystack → verify/webhook → QR tickets → confirmation email.
- **Phase 3 — Admin dashboard:** role-based login, content CRUD, orders/attendees, CSV/Excel export, QR check-in scanner page.
- **Phase 4 — Awards:** categories, nomination form, admin moderation/shortlist, email-verified public voting + live tally.
- **Phase 5 — Sponsor/exhibitor portal:** packages, application + logo upload, admin review/confirm, sponsor account login, asset uploads, auto comp passes.
- **Phase 6 — Hardening:** headers, rate limits, backups, reports, QA.

## Design tokens (match the prototype)

```
--ink:#07140E  --ink-2:#0B1E16  --ink-3:#102B20
--green:#0C7A4D  --verdant:#16B47A
--gold:#C9A227  --gold-soft:#E4C865
--parchment:#F5F2E9  --sage:#9FB3A8
Display: Fraunces (serif)   Body: Plus Jakarta Sans   Data/labels: IBM Plex Mono
Signature: animated guilloché seal (hero + awards medallion + ticket watermark)
Motion: scroll reveals, animated counters, live countdown; respect prefers-reduced-motion.
```

## Still open (ask the owner)

- Real ticket prices and event dates (current values are placeholders).
- Host specifics (Composer/SSH available? GD/Imagick for QR?).
- Whether to keep speaker photos hotlinked from the old site or migrate them to `uploads/`.
