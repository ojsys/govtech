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
('contact_email', 'info@govtechconference.ng'),
('partnerships_email', 'partnerships@govtechconference.ng');

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

-- ----- Agenda / programme (sample running order — edit in Admin → Agenda) ----
INSERT INTO agenda_sessions (event_id, day_label, start_time, end_time, title, description, speaker, location, is_break, sort) VALUES
(1, 'Day 1 · Tue, 7 Oct', '08:30', '09:30', 'Registration & Networking Breakfast', 'Collect your pass and connect with fellow delegates over breakfast.', '', 'Main Foyer', 1, 1),
(1, 'Day 1 · Tue, 7 Oct', '09:30', '10:00', 'Opening Ceremony & Welcome Address', 'Formal opening of the Nigeria GovTech Conference & Awards.', 'Bureau of Public Service Reforms', 'Main Auditorium', 0, 2),
(1, 'Day 1 · Tue, 7 Oct', '10:00', '10:45', 'Keynote: The Future of Digital Government', 'A vision for public-sector digital transformation in Nigeria.', 'Keynote Speaker', 'Main Auditorium', 0, 3),
(1, 'Day 1 · Tue, 7 Oct', '11:00', '12:30', 'Panel: Building Citizen-Centric Services', 'Leaders discuss delivering services that put citizens first.', 'Expert Panel', 'Main Auditorium', 0, 4),
(1, 'Day 1 · Tue, 7 Oct', '13:30', '15:00', 'Breakout Sessions & Workshops', 'Parallel deep-dive sessions across GovTech themes.', '', 'Halls A–C', 0, 5),
(1, 'Day 2 · Wed, 8 Oct', '09:30', '10:30', 'Keynote: Data, AI & Public Trust', 'Harnessing data and AI responsibly in government.', 'Keynote Speaker', 'Main Auditorium', 0, 6),
(1, 'Day 2 · Wed, 8 Oct', '11:00', '12:30', 'Exhibition Showcase', 'Explore innovations on the exhibition floor.', '', 'Exhibition Hall', 0, 7),
(1, 'Day 2 · Wed, 8 Oct', '19:00', '22:00', 'Nigeria GovTech Awards Gala', 'Celebrating excellence in public-sector technology.', '', 'Banquet Hall', 0, 8);

-- ----- Default admin --------------------------------------------------------
-- Login: admin@govtechconference.ng  /  admin1234   ← CHANGE THE PASSWORD AFTER FIRST LOGIN.
-- To set your own: php -r "echo password_hash('YOUR_PASSWORD', PASSWORD_BCRYPT);"
-- then UPDATE users SET password_hash='...' WHERE email='admin@govtechconference.ng';
INSERT INTO users (name, email, password_hash, role) VALUES
('Super Admin', 'admin@govtechconference.ng', '$2y$10$xM8L4O1DhDrbylaAM.gE9u4aHHr3N1RDyuMeDVyMImfmsvrfNMwM.', 'superadmin');
