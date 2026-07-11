-- Add per-image "edition" categorization to the gallery so photos can be
-- grouped by conference edition (e.g. 2024, 2025) and filtered on the site.
-- Safe to run once on an existing database.
ALTER TABLE gallery ADD COLUMN edition VARCHAR(40) DEFAULT NULL AFTER caption;

-- Optional: label any existing gallery rows with the current edition.
-- Adjust '2024' to match your first edition, or delete this line.
UPDATE gallery SET edition = '2024' WHERE edition IS NULL OR edition = '';
