# Nigeria GovTech Conference & Awards — Platform Architecture & Build Plan

**Stack:** PHP 8.2+ · MySQL/MariaDB (InnoDB, utf8mb4) · Paystack · PHPMailer (Brevo SMTP)
**Target host:** hPanel / cPanel shared hosting
**Design:** "Engraved Seal" — green (#0C7A4D) + gold (#C9A227), per approved prototype.

---

## 1. Stack decision (confirm before we build)

| Option | Pros | Cons | Verdict |
|---|---|---|---|
| **Lightweight custom PHP** (PDO + front controller + tiny router) | Trivial cPanel deploy, zero framework lock, total control, matches "PHP + MySQL", smallest footprint | We hand-roll routing/auth/validation | **Recommended default** |
| **Laravel 11** | Migrations, Eloquent, auth, queues, mature | Needs Composer on host, docroot must point to `/public`, queue workers awkward on shared hosting (cron-driven only) | Viable — pick if you want batteries included |

I'll proceed with **custom PHP** unless you say Laravel. Everything below is written for that, but the schema and flows are framework-agnostic.

---

## 2. Folder structure

```
/home/USER/
├── app/                      # OUTSIDE webroot (or protected) — not web-accessible
│   ├── config/
│   │   ├── config.php        # base URL, env, mail, app secrets
│   │   ├── database.php       # PDO DSN + creds
│   │   └── paystack.php       # public/secret keys, webhook secret
│   ├── core/                  # Router, Database(PDO), Request, Auth, Csrf, Mailer, View, Validator, Paystack
│   ├── controllers/
│   │   ├── HomeController.php
│   │   ├── RegistrationController.php
│   │   ├── PaymentController.php      # Paystack init + callback + webhook
│   │   ├── AwardsController.php       # nominations + voting
│   │   ├── SponsorController.php      # packages + applications + portal
│   │   └── admin/                     # AdminAuth, Dashboard, Content, Orders, CheckIn, Awards, Sponsors, Reports
│   ├── models/
│   ├── views/                 # layouts, partials, pages, emails
│   └── middleware/            # auth, admin, csrf, ratelimit
├── storage/                   # logs, qr cache, generated files (writable, not web-accessible)
├── database/
│   ├── schema.sql
│   └── seed.sql
├── vendor/                    # composer: phpmailer/phpmailer, chillerlan/php-qrcode
└── public_html/               # WEBROOT (docroot)
    ├── index.php              # front controller — routes everything
    ├── .htaccess              # rewrite to index.php + security headers + HTTPS
    ├── assets/ (css, js, img)
    └── uploads/               # public images (speakers, sponsor logos) — random filenames, no exec
```

> On hosts where everything must live inside `public_html`, place `app/`, `storage/`, `config/` one level up if allowed, otherwise drop a `.htaccess` `Require all denied` into each. Keep secrets out of webroot.

---

## 3. Module map

```
PUBLIC SITE ───────────────► REGISTRATION ──► PAYSTACK ──► TICKETS + QR + EMAIL
 (DB-driven content)              │              │              │
 speakers/sponsors/gallery        │              │              ▼
 awards categories                │              │          CHECK-IN (QR scan)
 sponsorship packages             │              ▼
                                  │         payments (audit)        ┌─ ADMIN DASHBOARD ─┐
 AWARDS  ──► nominate ──► moderate ──► vote (email-verified)        │ content CRUD       │
                                                                    │ orders/attendees   │
 SPONSOR PORTAL ──► apply ──► invoice/pay ──► account ──► upload    │ check-in           │
                            (logo, brochure advert, screen ad)      │ awards moderation  │
                                              + comp delegate passes │ sponsor review     │
                                                                    │ reports/exports    │
                                                                    └────────────────────┘
```

---

## 4. Database schema (core tables)

```sql
-- ===== EVENTS & CONTENT =====
CREATE TABLE events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(180), edition VARCHAR(20), theme TEXT,
  start_date DATE, end_date DATE, venue VARCHAR(255),
  status ENUM('draft','live','archived') DEFAULT 'draft',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE speakers (
  id INT AUTO_INCREMENT PRIMARY KEY, event_id INT,
  name VARCHAR(160), role VARCHAR(220), organization VARCHAR(180),
  photo VARCHAR(255), bio TEXT, featured TINYINT DEFAULT 0, sort INT DEFAULT 0,
  FOREIGN KEY (event_id) REFERENCES events(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- sponsors (logos), gallery, testimonials, site_settings(key,value),
-- newsletter_subscribers, contact_messages: same shape, omitted for brevity.

-- ===== TICKETING & REGISTRATION =====
CREATE TABLE ticket_types (
  id INT AUTO_INCREMENT PRIMARY KEY, event_id INT,
  name VARCHAR(120), slug VARCHAR(120) UNIQUE,
  price_kobo BIGINT NOT NULL,           -- store money in kobo, never floats
  description VARCHAR(255), perks_json JSON,
  group_size INT DEFAULT 1,             -- e.g. MDA group = 5
  quota INT NULL, sold INT DEFAULT 0,
  featured TINYINT DEFAULT 0, is_active TINYINT DEFAULT 1, sort INT DEFAULT 0,
  FOREIGN KEY (event_id) REFERENCES events(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE attendees (                 -- the buyer/registrant
  id INT AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(80), last_name VARCHAR(80),
  email VARCHAR(160), phone VARCHAR(40),
  organization VARCHAR(180), job_title VARCHAR(160),
  sector ENUM('public','private','academia','other'), state VARCHAR(60),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX(email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  reference VARCHAR(40) UNIQUE,          -- our ref, sent to Paystack
  attendee_id INT,
  subtotal_kobo BIGINT, total_kobo BIGINT, currency CHAR(3) DEFAULT 'NGN',
  status ENUM('pending','paid','failed','cancelled') DEFAULT 'pending',
  paystack_ref VARCHAR(80), paystack_access_code VARCHAR(120),
  paid_at DATETIME NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (attendee_id) REFERENCES attendees(id), INDEX(status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY, order_id INT, ticket_type_id INT,
  unit_price_kobo BIGINT, quantity INT, subtotal_kobo BIGINT,
  FOREIGN KEY (order_id) REFERENCES orders(id),
  FOREIGN KEY (ticket_type_id) REFERENCES ticket_types(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE tickets (                    -- one issued pass per seat (incl. group expansion)
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_item_id INT, ticket_type_id INT, attendee_id INT,
  ticket_code VARCHAR(40) UNIQUE,         -- goes into the QR
  qr_path VARCHAR(255),
  holder_name VARCHAR(160), holder_email VARCHAR(160),
  source ENUM('purchase','sponsor','comp') DEFAULT 'purchase',
  status ENUM('valid','checked_in','void') DEFAULT 'valid',
  checked_in_at DATETIME NULL, checked_in_by INT NULL,
  FOREIGN KEY (order_item_id) REFERENCES order_items(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE payments (                   -- gateway audit log (source of truth = webhook)
  id INT AUTO_INCREMENT PRIMARY KEY, order_id INT,
  gateway VARCHAR(20) DEFAULT 'paystack',
  reference VARCHAR(80), amount_kobo BIGINT,
  status VARCHAR(30), raw_response JSON,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX(reference)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===== AWARDS =====
CREATE TABLE award_categories (
  id INT AUTO_INCREMENT PRIMARY KEY, event_id INT,
  title VARCHAR(160), description TEXT, is_active TINYINT DEFAULT 1, sort INT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE nominations (
  id INT AUTO_INCREMENT PRIMARY KEY, category_id INT,
  nominee_name VARCHAR(160), nominee_org VARCHAR(180), nominee_email VARCHAR(160),
  nominator_name VARCHAR(160), nominator_email VARCHAR(160), justification TEXT,
  status ENUM('pending','approved','shortlisted','rejected') DEFAULT 'pending',
  votes_count INT DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES award_categories(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE votes (
  id INT AUTO_INCREMENT PRIMARY KEY, nomination_id INT, category_id INT,
  voter_email VARCHAR(160), verify_token VARCHAR(64), verified TINYINT DEFAULT 0,
  ip VARCHAR(45), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY one_vote_per_category (category_id, voter_email)  -- anti-ballot-stuffing
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===== SPONSOR / EXHIBITOR =====
CREATE TABLE sponsorship_packages (
  id INT AUTO_INCREMENT PRIMARY KEY, event_id INT,
  type ENUM('sponsor','exhibition') DEFAULT 'sponsor',
  name VARCHAR(120), price_kobo BIGINT, booth_size VARCHAR(30) NULL,
  perks_json JSON, is_active TINYINT DEFAULT 1, sort INT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE sponsor_applications (
  id INT AUTO_INCREMENT PRIMARY KEY, package_id INT,
  company_name VARCHAR(200), contact_name VARCHAR(160), email VARCHAR(160), phone VARCHAR(40),
  logo_path VARCHAR(255), message TEXT, order_id INT NULL,
  status ENUM('new','contacted','invoiced','confirmed','paid') DEFAULT 'new',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (package_id) REFERENCES sponsorship_packages(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE sponsor_accounts (           -- portal login for confirmed sponsors
  id INT AUTO_INCREMENT PRIMARY KEY, application_id INT,
  email VARCHAR(160) UNIQUE, password_hash VARCHAR(255), is_active TINYINT DEFAULT 1,
  FOREIGN KEY (application_id) REFERENCES sponsor_applications(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE sponsor_assets (             -- brochure adverts, screen ads, hi-res logo
  id INT AUTO_INCREMENT PRIMARY KEY, account_id INT,
  type ENUM('logo','brochure_ad','screen_ad'), file_path VARCHAR(255),
  status ENUM('pending','approved','rejected') DEFAULT 'pending', notes VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (account_id) REFERENCES sponsor_accounts(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===== ADMIN / AUTH =====
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(120), email VARCHAR(160) UNIQUE,
  password_hash VARCHAR(255),
  role ENUM('superadmin','editor','finance','checkin') DEFAULT 'editor',
  is_active TINYINT DEFAULT 1, last_login DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE audit_log (
  id INT AUTO_INCREMENT PRIMARY KEY, user_id INT, action VARCHAR(60),
  entity VARCHAR(60), entity_id INT, meta JSON, ip VARCHAR(45),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 5. Paystack payment flow (server-trusted)

```
1. Buyer fills registration form + selects passes.
2. Server RE-COMPUTES totals from ticket_types.price_kobo (never trusts posted prices).
3. Create attendee + order(status=pending) + order_items. Generate unique `reference`.
4. POST https://api.paystack.co/transaction/initialize
      headers: Authorization: Bearer SECRET_KEY
      body: { email, amount(kobo), reference, callback_url, metadata:{order_id} }
   → returns authorization_url + access_code.
5. Redirect buyer to authorization_url (or Paystack Inline popup with PUBLIC key).
6. CALLBACK (callback_url): verify by reference →
      GET https://api.paystack.co/transaction/verify/{reference}
   Only if status=success AND amount matches → mark order paid, issue tickets.
7. WEBHOOK (/payment/webhook) = source of truth:
      - Verify signature: HMAC-SHA512 of raw body with SECRET_KEY == X-Paystack-Signature header.
      - On charge.success → idempotently mark paid + issue tickets (skip if already paid).
8. Issue: per order_item quantity (× group_size) create tickets, generate ticket_code,
   render QR (chillerlan/php-qrcode), email PDF/links via PHPMailer (Brevo SMTP).
```

Idempotency: webhook and callback can both fire — guard ticket issuance behind `orders.status` + a DB transaction so passes are never double-issued.

---

## 6. Security checklist (non-negotiable)

- PDO prepared statements everywhere; no string-built SQL.
- CSRF token on every POST form; SameSite=Lax, HttpOnly, Secure cookies; regenerate session id on login.
- `password_hash()` / `password_verify()` (argon2id or bcrypt).
- Money in **kobo (integers)**; totals recomputed server-side from DB.
- Paystack: verify webhook HMAC-SHA512 **and** verify-by-reference before issuing anything.
- File uploads: validate MIME + extension + size, random filenames, store with no execute, ideally outside webroot.
- Voting: one row per (category, email), email-verify link before the vote counts, IP + rate limit.
- Login + nomination + contact forms: rate-limited; honeypot or hCaptcha on public forms.
- Force HTTPS + security headers (HSTS, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, CSP) via `.htaccess`.
- Secrets in `app/config` outside webroot; never in `public_html`, never in git.
- `audit_log` every admin mutation.

---

## 7. Build sequence

| Phase | Deliverable |
|---|---|
| **0 — Foundation** | Folder skeleton, front controller + router, PDO wrapper, config, Auth/Csrf/Mailer/Paystack core, `schema.sql` + `seed.sql` (event, speakers, ticket types, sponsorship packages, award categories), base layout/partials matching the prototype. |
| **1 — Public site** | Port the approved prototype into PHP views, all content pulled from DB (speakers, gallery, packages, countdown, etc.). |
| **2 — Registration + Tickets** | Multi-pass cart → order → **Paystack** → verify/webhook → QR tickets → confirmation email. |
| **3 — Admin dashboard** | Login + roles, content CRUD, orders/attendees views, CSV/Excel export, **QR check-in scanner** page. |
| **4 — Awards** | Categories, nomination form, admin moderation/shortlist, email-verified public voting + live tally. |
| **5 — Sponsor / Exhibitor portal** | Package listing, application + logo upload, admin review/confirm, sponsor account login, asset uploads (brochure ad / screen ad), auto comp delegate passes. |
| **6 — Hardening** | Headers, rate limits, backups, reports, final QA. |

---

## 8. Open decisions to confirm

1. **Framework:** custom PHP (my default) or Laravel?
2. **Event basis:** single current edition, or multi-edition from day one (schema already supports it)?
3. **Ticket prices/dates:** the prototype figures are placeholders — give me the real numbers when ready (can also be edited in admin later).
4. **Voting model:** public email-verified voting, or judges-only / hybrid?
5. **QR library:** `chillerlan/php-qrcode` (pure PHP) — confirm Composer/SSH is available on the host, else I'll use a GD-based fallback.
6. **Comp passes:** confirm the pass allocation per sponsor tier (prototype mirrors the current site: Platinum 4, Gold 2, Silver 2, Bronze 1).
