-- ===========================================================================
-- Seed data — Nigeria GovTech Conference & Awards (editorial content)
-- The event + ticket types + sponsorship packages are seeded by schema.sql.
-- This file adds settings, award categories, speakers and the default admin.
-- Prices in KOBO. Dates/prices are PLACEHOLDERS — edit before go-live.
-- Run AFTER schema.sql:  mysql -u USER -p DBNAME < seed.sql
-- ===========================================================================

-- ----- Site settings --------------------------------------------------------
INSERT INTO site_settings (setting_key, setting_value) VALUES
('organizer_name', 'Bureau of Public Service Reforms'),
('organizer_note', 'Organized by the Presidency, Federal Republic of Nigeria'),
('countdown_target', '2026-10-07T09:00:00+01:00'),
('contact_email', 'info@govtechconference.ng');

-- ----- Award categories -----------------------------------------------------
INSERT INTO award_categories (event_id, title, description, sort) VALUES
(1, 'Digital Leadership',         'Outstanding leadership driving digital transformation in the public sector.', 1),
(1, 'Innovation of the Year',     'The most impactful GovTech innovation deployed in the past 12 months.', 2),
(1, 'Best MDA Platform',          'The most effective digital platform delivered by a government MDA.', 3),
(1, 'Public–Private Partnership', 'Excellence in collaboration between government and private sector.', 4),
(1, 'Emerging GovTech Startup',   'A rising startup advancing technology for governance.', 5),
(1, 'Data & AI Excellence',       'Outstanding use of data and AI to improve public service delivery.', 6);

-- ----- Speakers (photos hosted on current site; replace as needed) ----------
INSERT INTO speakers (event_id, name, role, organization, photo, featured, sort) VALUES
(1, 'Sen. George Akume', 'Secretary to the Government of the Federation', '', '2024/06/Akume.jpeg', 1, 1),
(1, 'Didi Walson-Jack, OON', 'Head of the Civil Service of the Federation', '', '2025/07/Mrs-Didi-Esther-Walson-Jack.jpeg', 1, 2),
(1, 'Dr. Adebowale Adedokun', 'Director General', 'Bureau of Public Procurement', '2025/07/Dr.-Adebowale-A-Adedokun.jpeg', 1, 3),
(1, 'Dr. Vincent Olatunji', 'National Commissioner', 'Nigeria Data Protection Commission', '2025/07/Dr.-Vincent-Olatunji.jpeg', 1, 4),
(1, 'Engr. Felix Omatsola Ogbe', 'Executive Secretary', 'NCDMB', '2025/07/Engr.-Felix-Omatsola-Ogbe.jpeg', 0, 5),
(1, 'Dr. Olubunmi Ajala', 'National Director', 'NCAIR', '2025/07/Dr-Olubunmi-Ajala.jpeg', 0, 6),
(1, 'Prof. Eldrid Jordaan', 'Founder', 'Supple (Luxembourg)', '2025/07/Prof-Eldrid-Jordaan-e1752943832851.jpeg', 0, 7),
(1, 'Haruna Jalo-Waziri', 'Managing Director', 'Central Securities Clearing System (CSCS)', '2025/07/Haruna-Jalo-Waziri.jpeg', 0, 8);

-- ----- Default admin --------------------------------------------------------
-- Login: admin@govtechconference.ng  /  admin1234   ← CHANGE THE PASSWORD AFTER FIRST LOGIN.
-- To set your own: php -r "echo password_hash('YOUR_PASSWORD', PASSWORD_BCRYPT);"
-- then UPDATE users SET password_hash='...' WHERE email='admin@govtechconference.ng';
INSERT INTO users (name, email, password_hash, role) VALUES
('Super Admin', 'admin@govtechconference.ng', '$2y$10$xM8L4O1DhDrbylaAM.gE9u4aHHr3N1RDyuMeDVyMImfmsvrfNMwM.', 'superadmin');
