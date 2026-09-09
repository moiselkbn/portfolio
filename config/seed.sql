-- Throwaway development data — a few rows so the pages have something to show
-- before the admin exists. The real content will be entered through the admin
-- and these rows deleted. Run in phpMyAdmin on `portfolio_2026_v3`.

INSERT INTO project (slug, title, year, status, context) VALUES
  ('maison-gaia', 'Maison Gaïa', '2023–2026', 'featured',
   'Placeholder context for the dev build. Replace via the admin.'),
  ('lo-stallone', 'Lo Stallone', '2025', 'featured',
   'Placeholder context for the dev build. Replace via the admin.'),
  ('bruxelles', 'Bruxelles', '2024', 'lab', NULL);
