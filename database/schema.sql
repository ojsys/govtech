-- ===========================================================================
-- Nigeria GovTech Conference & Awards — Database Schema
-- MySQL 8 / MariaDB 10.4+  ·  InnoDB  ·  utf8mb4
-- Money is stored in KOBO (integers). Never use floats for money.
-- Run:  mysql -u USER -p DBNAME < schema.sql
-- ===========================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ===== EVENTS & CONTENT ====================================================
CREATE TABLE IF NOT EXISTS events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(180) NOT NULL,
  edition VARCHAR(20),
  theme TEXT,
  start_date DATE,
  end_date DATE,
  venue VARCHAR(255),
  status ENUM('draft','live','archived') DEFAULT 'draft',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS speakers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_id INT,
  name VARCHAR(160) NOT NULL,
  role VARCHAR(220),
  organization VARCHAR(180),
  photo VARCHAR(255),
  bio TEXT,
  featured TINYINT DEFAULT 0,
  sort INT DEFAULT 0,
  FOREIGN KEY (event_id) REFERENCES events(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sponsors (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_id INT,
  name VARCHAR(180),
  logo VARCHAR(255),
  tier VARCHAR(40),
  url VARCHAR(255),
  sort INT DEFAULT 0,
  FOREIGN KEY (event_id) REFERENCES events(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS gallery (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_id INT,
  image VARCHAR(255),
  caption VARCHAR(255),
  edition VARCHAR(40) DEFAULT NULL,
  sort INT DEFAULT 0,
  FOREIGN KEY (event_id) REFERENCES events(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS testimonials (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_id INT,
  name VARCHAR(160),
  role VARCHAR(220),
  quote TEXT,
  sort INT DEFAULT 0,
  FOREIGN KEY (event_id) REFERENCES events(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS site_settings (
  setting_key VARCHAR(80) PRIMARY KEY,
  setting_value TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS newsletter_subscribers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(160),
  email VARCHAR(160) UNIQUE,
  confirmed TINYINT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS contact_messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(160), email VARCHAR(160),
  subject VARCHAR(200), message TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===== TICKETING & REGISTRATION ============================================
CREATE TABLE IF NOT EXISTS ticket_types (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_id INT,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(120) UNIQUE,
  price_kobo BIGINT NOT NULL,
  description VARCHAR(255),
  perks_json JSON,
  group_size INT DEFAULT 1,
  quota INT NULL,
  sold INT DEFAULT 0,
  featured TINYINT DEFAULT 0,
  is_active TINYINT DEFAULT 1,
  sort INT DEFAULT 0,
  FOREIGN KEY (event_id) REFERENCES events(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS attendees (
  id INT AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(80), last_name VARCHAR(80),
  email VARCHAR(160), phone VARCHAR(40),
  organization VARCHAR(180), job_title VARCHAR(160),
  sector ENUM('public','private','academia','other'),
  state VARCHAR(60),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX(email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  reference VARCHAR(40) UNIQUE,
  attendee_id INT,
  subtotal_kobo BIGINT,
  total_kobo BIGINT,
  currency CHAR(3) DEFAULT 'NGN',
  status ENUM('pending','paid','failed','cancelled') DEFAULT 'pending',
  paystack_ref VARCHAR(80),
  paystack_access_code VARCHAR(120),
  paid_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (attendee_id) REFERENCES attendees(id),
  INDEX(status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT, ticket_type_id INT,
  unit_price_kobo BIGINT, quantity INT, subtotal_kobo BIGINT,
  FOREIGN KEY (order_id) REFERENCES orders(id),
  FOREIGN KEY (ticket_type_id) REFERENCES ticket_types(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tickets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_item_id INT, ticket_type_id INT, attendee_id INT,
  ticket_code VARCHAR(40) UNIQUE,
  qr_path VARCHAR(255),
  holder_name VARCHAR(160), holder_email VARCHAR(160),
  source ENUM('purchase','sponsor','comp') DEFAULT 'purchase',
  status ENUM('valid','checked_in','void') DEFAULT 'valid',
  checked_in_at DATETIME NULL, checked_in_by INT NULL,
  FOREIGN KEY (order_item_id) REFERENCES order_items(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT,
  gateway VARCHAR(20) DEFAULT 'paystack',
  reference VARCHAR(80),
  amount_kobo BIGINT,
  status VARCHAR(30),
  raw_response JSON,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX(reference)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===== AWARDS ==============================================================
CREATE TABLE IF NOT EXISTS award_categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_id INT,
  title VARCHAR(160), description TEXT,
  is_active TINYINT DEFAULT 1, sort INT DEFAULT 0,
  FOREIGN KEY (event_id) REFERENCES events(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS nominations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT,
  nominee_name VARCHAR(160), nominee_org VARCHAR(180), nominee_email VARCHAR(160),
  nominator_name VARCHAR(160), nominator_email VARCHAR(160),
  justification TEXT,
  status ENUM('pending','approved','shortlisted','rejected') DEFAULT 'pending',
  votes_count INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES award_categories(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS votes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nomination_id INT, category_id INT,
  voter_email VARCHAR(160),
  verify_token VARCHAR(64), verified TINYINT DEFAULT 0,
  ip VARCHAR(45),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY one_vote_per_category (category_id, voter_email),
  FOREIGN KEY (nomination_id) REFERENCES nominations(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===== SPONSOR / EXHIBITOR =================================================
CREATE TABLE IF NOT EXISTS sponsorship_packages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_id INT,
  type ENUM('sponsor','exhibition') DEFAULT 'sponsor',
  name VARCHAR(120), price_kobo BIGINT,
  booth_size VARCHAR(30) NULL,
  perks_json JSON,
  comp_passes INT DEFAULT 0,
  is_active TINYINT DEFAULT 1, sort INT DEFAULT 0,
  FOREIGN KEY (event_id) REFERENCES events(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sponsor_applications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  package_id INT,
  company_name VARCHAR(200), contact_name VARCHAR(160),
  email VARCHAR(160), phone VARCHAR(40),
  logo_path VARCHAR(255), message TEXT,
  order_id INT NULL,
  status ENUM('new','contacted','invoiced','confirmed','paid') DEFAULT 'new',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (package_id) REFERENCES sponsorship_packages(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sponsor_accounts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  application_id INT,
  email VARCHAR(160) UNIQUE, password_hash VARCHAR(255),
  is_active TINYINT DEFAULT 1,
  FOREIGN KEY (application_id) REFERENCES sponsor_applications(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sponsor_assets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  account_id INT,
  type ENUM('logo','brochure_ad','screen_ad'),
  file_path VARCHAR(255),
  status ENUM('pending','approved','rejected') DEFAULT 'pending',
  notes VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (account_id) REFERENCES sponsor_accounts(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===== ADMIN / AUTH ========================================================
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120), email VARCHAR(160) UNIQUE,
  password_hash VARCHAR(255),
  role ENUM('superadmin','editor','finance','checkin') DEFAULT 'editor',
  is_active TINYINT DEFAULT 1,
  last_login DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS audit_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT, action VARCHAR(60),
  entity VARCHAR(60), entity_id INT,
  meta JSON, ip VARCHAR(45),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- ===========================================================================
-- STARTER CATALOG SEED (event + ticket types + sponsorship packages)
-- Money is in KOBO (naira × 100). Prices/dates are PLACEHOLDERS — edit in admin.
-- INSERT IGNORE + explicit ids = safe to re-run; won't duplicate.
-- Editorial content (settings, speakers, award categories, admin) is in seed.sql.
-- ===========================================================================

INSERT IGNORE INTO events (id, name, edition, theme, start_date, end_date, venue, status) VALUES
(1, 'Nigeria GovTech Conference & Awards', '3rd',
 'Redefining Possibilities: Harnessing Emerging Technologies for Public Service Delivery and Socio-Economic Development.',
 '2026-10-07', '2026-10-08', 'Banquet Hall, Presidential Villa, Abuja', 'live');

-- ----- Ticket types --------------------------------------------------------
INSERT IGNORE INTO ticket_types (id, event_id, name, slug, price_kobo, description, perks_json, group_size, featured, is_active, sort) VALUES
(1, 1, 'Virtual Access',     'virtual',   2500000,  'per attendee',
 JSON_ARRAY('Full livestream, all sessions','Digital programme & materials','Session recordings (30 days)'), 1, 0, 1, 1),
(2, 1, 'Standard Delegate',  'standard',  7500000,  'per delegate',
 JSON_ARRAY('In-person, both days','Lunch & networking breaks','Conference kit & certificate','Awards gala access'), 1, 0, 1, 2),
(3, 1, 'Executive Delegate', 'executive', 15000000, 'per delegate',
 JSON_ARRAY('Priority front-row seating','Speakers'' lounge access','VIP networking dinner','Conference kit & certificate'), 1, 1, 1, 3),
(4, 1, 'Government MDA Group','mda-group', 30000000, '5 delegates',
 JSON_ARRAY('5 standard delegate passes','Reserved group seating','Dedicated liaison','Brand listing as participant'), 5, 0, 1, 4);

-- ----- Sponsorship & exhibition packages -----------------------------------
INSERT IGNORE INTO sponsorship_packages (id, event_id, type, name, price_kobo, booth_size, comp_passes, perks_json, is_active, sort) VALUES
(1, 1, 'sponsor', 'Platinum', 3000000000, NULL, 4,
 JSON_ARRAY('2-page brochure advert','Ad & logo on digital screens','4 delegate passes','Premier exhibition placement'), 1, 1),
(2, 1, 'sponsor', 'Gold',     2000000000, NULL, 2,
 JSON_ARRAY('2-page brochure advert','Ad & logo on digital screens','2 delegate passes'), 1, 2),
(3, 1, 'sponsor', 'Silver',   1500000000, NULL, 2,
 JSON_ARRAY('Half-page brochure advert','Logo on digital screens','2 delegate passes'), 1, 3),
(4, 1, 'sponsor', 'Bronze',   1000000000, NULL, 1,
 JSON_ARRAY('Half-page brochure advert','Logo on digital screens','1 delegate pass'), 1, 4),
(5, 1, 'exhibition', '6 sqm Exhibition Booth',  300000000, '6sqm',  0, JSON_ARRAY('Standard exhibition booth'), 1, 5),
(6, 1, 'exhibition', '9 sqm Exhibition Booth',  350000000, '9sqm',  0, JSON_ARRAY('Standard exhibition booth'), 1, 6),
(7, 1, 'exhibition', '12 sqm Exhibition Booth', 400000000, '12sqm', 0, JSON_ARRAY('Standard exhibition booth'), 1, 7);
