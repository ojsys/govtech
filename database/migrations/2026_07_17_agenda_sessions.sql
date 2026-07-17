-- Programme / agenda module: the running order of the event, grouped by day,
-- shown on the public /agenda page and managed from Admin → Agenda.
-- Safe to run once on an existing database.
CREATE TABLE IF NOT EXISTS agenda_sessions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_id INT,
  day_label VARCHAR(80),
  start_time VARCHAR(20),
  end_time VARCHAR(20),
  title VARCHAR(200) NOT NULL,
  description TEXT,
  speaker VARCHAR(200),
  location VARCHAR(160),
  track VARCHAR(80),
  is_break TINYINT DEFAULT 0,
  is_published TINYINT DEFAULT 1,
  sort INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (event_id) REFERENCES events(id),
  INDEX(event_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
