<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';

/**
 * Read access to the `project` table. One function per page need.
 * Every query is prepared (CLAUDE.md § Sécurité), even when the value is a
 * constant we control — same habit everywhere, no exceptions to remember.
 */

/**
 * All featured projects, newest first — the home page grid.
 *
 * @return array<int, array<string, mixed>>
 */
function getFeaturedProjects(): array
{
    $stmt = db()->prepare(
        'SELECT id, slug, title, year, cover_image
         FROM project
         WHERE status = ?
         ORDER BY created_at DESC'
    );
    $stmt->execute(['featured']);

    return $stmt->fetchAll();
}

/**
 * All lab projects, newest first — the /lab grid.
 *
 * @return array<int, array<string, mixed>>
 */
function getLabProjects(): array
{
    $stmt = db()->prepare(
        'SELECT id, slug, title, year, cover_image
         FROM project
         WHERE status = ?
         ORDER BY created_at DESC'
    );
    $stmt->execute(['lab']);

    return $stmt->fetchAll();
}

/**
 * One project for its dedicated page (/projects/{slug}).
 * Returns the full row, or null when there is no page to show for this slug.
 *
 * @return array<string, mixed>|null
 */
function findFeaturedProjectBySlug(string $slug): ?array
{
    // A dedicated page exists for featured projects only; lab projects show in
    // a lightbox on /lab, so a lab slug here resolves to null (→ 404).
    $stmt = db()->prepare('SELECT * FROM project WHERE slug = ? AND status = ?');
    $stmt->execute([$slug, 'featured']);
    $row = $stmt->fetch();

    return $row ?: null;
}
