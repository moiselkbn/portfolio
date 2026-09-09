-- Portfolio 2026 — database schema
-- Two tables only (CLAUDE.md § Base de données). Run once in phpMyAdmin.
-- The database itself is created separately:
--   CREATE DATABASE portfolio_2026_v3 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- admin_user — two accounts, created by hand, no signup page
-- ---------------------------------------------------------------------------
CREATE TABLE admin_user (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username      VARCHAR(50)  NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_admin_user_username (username)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- project — showcase + lab projects, one row each
-- ---------------------------------------------------------------------------
CREATE TABLE project (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug           VARCHAR(255) NOT NULL,                 -- derived from title, unique
  title          VARCHAR(255) NOT NULL,
  year           VARCHAR(20)  NOT NULL,                 -- "2025" or a range "2023–2026"
  cover_image    VARCHAR(255) DEFAULT NULL,             -- file name inside medias/<project>/
  gallery        JSON         DEFAULT NULL,             -- [{ "src": "...", "caption": "..." }]
  context        TEXT         DEFAULT NULL,
  role           TEXT         DEFAULT NULL,
  result         TEXT         DEFAULT NULL,
  decisions      JSON         DEFAULT NULL,             -- [{ "probleme": "", "options": "", "choix": "" }]
  annex_stack    JSON         DEFAULT NULL,             -- ["PHP", "CSS", ...]
  annex_repo_url VARCHAR(255) DEFAULT NULL,
  annex_retro    TEXT         DEFAULT NULL,             -- stored, never rendered on the site
  status         ENUM('draft', 'lab', 'featured') NOT NULL DEFAULT 'lab',
  created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_project_slug (slug),
  KEY idx_project_status (status)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
