# Nigeria GovTech Conference & Awards

Event platform: public site · registration & ticketing (Paystack) · admin dashboard ·
awards (nominations + voting) · sponsor/exhibitor portal.

**Stack:** PHP 8.2+ · MySQL/MariaDB · PHPMailer (Brevo SMTP) · Paystack

## Start here

1. Read **`CLAUDE.md`** — project context, decisions, and engineering rules.
2. Read **`docs/ARCHITECTURE.md`** — full architecture, schema reference, Paystack flow, security checklist, build phases.
3. Open **`docs/design-prototype.html`** in a browser — the approved visual design to match.

## Database

```bash
mysql -u USER -p DBNAME < database/schema.sql
mysql -u USER -p DBNAME < database/seed.sql
```
Money is stored in **kobo** (integers). Prices and dates in `seed.sql` are placeholders.

## Build status — all phases complete

- **0 Foundation** — front controller, router, PDO wrapper, core classes, security headers.
- **1 Public site** — prototype ported to DB-driven views.
- **2 Registration + tickets** — cart → Paystack → verify/webhook → idempotent QR tickets → email.
- **3 Admin** — role-based login, orders, QR check-in scanner, content CRUD, CSV export, audit log.
- **4 Awards** — nominations, moderation, email-verified voting, live tally.
- **5 Sponsor portal** — application → admin confirm → auto comp passes + login → asset uploads.
- **6 Hardening** — security headers/CSP, robots/sitemap, reports + exports, backups, deploy doctor.

Standalone pages: `/about`, `/sponsor`, `/contact` (working form), footer newsletter.

## Deploy

Built for cPanel/hPanel: upload the folder, point the docroot at `public_html/`,
keep `app/`, `storage/`, `bin/`, and config outside the webroot (or rely on the
bundled `Require all denied` files), `composer install` on the host, run the SQL
above, set secrets in `app/config/*`, configure the Paystack webhook.

**Before go-live:** work through **`docs/GO-LIVE.md`** and run **`php bin/doctor.php`**
(it checks env, config, secrets, DB, permissions). Back up with `php bin/backup.php`
(cron-friendly). Full build details in `docs/DEVELOPMENT.md`.
