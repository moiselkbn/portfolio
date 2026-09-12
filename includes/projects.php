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
        'SELECT id, slug, title, year, cover_media
         FROM project
         WHERE status = ?
         ORDER BY created_at DESC'
    );
    $stmt->execute(['featured']);

    return $stmt->fetchAll();
}

/**
 * All lab projects, most recent year first — the /lab grid. Unlike the other
 * read functions here, this one decodes its JSON columns: the view needs the
 * actual arrays (how many images, which domains) to lay the grid out, not
 * the raw JSON strings PDO hands back.
 *
 * @return array<int, array<string, mixed>>
 */
function getLabProjects(): array
{
    // `year` is a VARCHAR ("2025" or a range "2023–2026" — CLAUDE.md § Base de
    // données), but every value starts with 4 digits, so a plain text sort
    // already orders by the starting year correctly; created_at only breaks
    // a tie between two projects from the same year.
    $stmt = db()->prepare(
        'SELECT id, slug, title, year, cover_media, gallery, domains
         FROM project
         WHERE status = ?
         ORDER BY year DESC, created_at DESC'
    );
    $stmt->execute(['lab']);
    $rows = $stmt->fetchAll();

    // By reference so the decoded value replaces the raw JSON string in place.
    foreach ($rows as &$row) {
        $row['gallery'] = $row['gallery'] ? json_decode((string) $row['gallery'], true) : [];
        $row['domains'] = $row['domains'] ? json_decode((string) $row['domains'], true) : [];
    }
    unset($row); // break the reference — leaving it dangling risks a subtle bug on the next foreach that reuses $row

    return $rows;
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
