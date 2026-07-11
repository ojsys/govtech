# Go-Live Checklist — Nigeria GovTech Conference & Awards

Run through this before pointing the domain at the platform. `php bin/doctor.php`
automates most of these checks.

## 1. Server & files
- [ ] Upload the project; point the domain **document root → `public_html/`**.
- [ ] Keep `app/`, `storage/`, `database/`, `bin/`, `vendor/` outside the webroot
      (or rely on the bundled `Require all denied` `.htaccess` files).
- [ ] `composer install --no-dev` on the host (PHPMailer + chillerlan/php-qrcode).
- [ ] `chmod` writable: `storage/logs`, `storage/qr`, `storage/backups`,
      `public_html/uploads` (755/775).

## 2. Configuration (`app/config/*.php` — copy each `.example`)
- [ ] `config.php`: `env = production`, real `base_url` (https), generated `app_key`
      (`php -r "echo bin2hex(random_bytes(32));"`).
- [ ] `database.php`: real DB host/name/user/pass.
- [ ] `paystack.php`: **live** keys (`sk_live_…`, `pk_live_…`).
- [ ] `mail.php`: Brevo SMTP host/user/pass + from address.

## 3. Database
- [ ] `mysql -u USER -p DBNAME < database/schema.sql`
- [ ] `mysql -u USER -p DBNAME < database/seed.sql`
- [ ] Update **event dates**, **venue**, **countdown_target**, and **real ticket
      prices** (admin → Settings + the `ticket_types` table; prices are in kobo).
- [ ] **Change the default admin password** (`admin@govtechconference.ng` / `admin1234`).
      `php -r "echo password_hash('NEW', PASSWORD_BCRYPT);"` → update `users`.

## 4. Payments (Paystack)
- [ ] Dashboard → Webhooks: set URL to `https://YOURDOMAIN/payment/webhook`.
- [ ] Run a live test transaction; confirm the order shows **paid**, a pass +
      QR is issued, and the confirmation email arrives.
- [ ] Confirm the webhook fires (the webhook is the source of truth; the callback
      is a fast-path — both are idempotent so passes never double-issue).

## 5. Security (mostly enforced already)
- [ ] HTTPS works; HSTS header is sent (it's gated on HTTPS in `.htaccess`).
- [ ] Security headers present (CSP, X-Frame-Options, X-Content-Type-Options,
      Referrer-Policy, Permissions-Policy) — verify with browser dev tools.
- [ ] Secrets are NOT in `public_html` and NOT committed to git.
- [ ] `robots.txt` keeps `/admin`, `/portal`, `/payment`, `/order`, `/ticket` out of search.

## 6. Backups & ops
- [ ] Cron the DB backup: `0 3 * * * php /path/bin/backup.php >> /path/storage/logs/backup.log 2>&1`
- [ ] Confirm `php bin/backup.php` produces a `.sql.gz` in `storage/backups/`.

## 7. Final QA pass
- [ ] Public: home, /about, /sponsor, /contact, /awards, /register all render.
- [ ] Register → pay → receive passes + email.
- [ ] Admin: login, orders, check-in scan, speakers CRUD, awards moderation,
      sponsor confirm (provisions login + comp passes), reports + CSV exports.
- [ ] Awards: submit nomination, shortlist it, vote, confirm by email, see tally.
- [ ] Sponsor: apply, admin confirm, portal login, upload asset, admin approve.
- [ ] `php bin/doctor.php` reports 0 FAIL.

## Roles
`superadmin` (all) · `editor` (content/awards/sponsors) · `finance`
(orders/sponsors/reports) · `checkin` (scanner only). Create staff users in the
`users` table with the appropriate role.
