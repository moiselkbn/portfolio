<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/database.php';

/**
 * Write access to the `project` table — used by the admin only.
 * Public read access lives in includes/projects.php.
 * Callers pass data that has already been validated (see validateProject()).
 */

/**
 * "Maison Gaïa" -> "maison-gaia". Accents flattened, everything that is not a
 * letter or digit becomes a single hyphen, no hyphen at the ends.
 * Uses the intl extension for a reliable transliteration (é->e, É->e, …).
 */
function slugify(string $text): string
{
    $latin = Transliterator::create('Any-Latin; Latin-ASCII; Lower()')
        ?->transliterate($text);
    $text = $latin ?? mb_strtolower($text);

    $text = preg_replace('/[^a-z0-9]+/', '-', $text);

    return trim($text, '-');
}

/**
 * Is this slug already taken? $exceptId skips one row (the project being edited).
 */
function slugExists(string $slug, ?int $exceptId = null): bool
{
    if ($exceptId === null) {
        $stmt = db()->prepare('SELECT 1 FROM project WHERE slug = ?');
        $stmt->execute([$slug]);
    } else {
        $stmt = db()->prepare('SELECT 1 FROM project WHERE slug = ? AND id <> ?');
        $stmt->execute([$slug, $exceptId]);
    }

    return $stmt->fetchColumn() !== false;
}

/**
 * Every project, most recently changed first — the admin list.
 *
 * @return array<int, array<string, mixed>>
 */
function allProjects(): array
{
    return db()->query(
        'SELECT id, title, year, status, updated_at
         FROM project
         ORDER BY updated_at DESC'
    )->fetchAll();
}

/**
 * One project by id, or null.
 *
 * @return array<string, mixed>|null
 */
function findProjectById(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM project WHERE id = ?');
    $stmt->execute([$id]);

    return $stmt->fetch() ?: null;
}

/**
 * Map validated form data to the named parameters shared by INSERT and UPDATE.
 * JSON columns are encoded here; empty optional text becomes NULL.
 *
 * @param array<string, mixed> $data
 * @return array<string, mixed>
 */
function projectParams(array $data): array
{
    return [
        'slug'           => slugify((string) $data['title']),
        'title'          => trim((string) $data['title']),
        'year'           => trim((string) $data['year']),
        'status'         => $data['status'],
        'context'        => $data['context'] ?: null,
        'role'           => $data['role'] ?: null,
        'result'         => $data['result'] ?: null,
        'decisions'      => $data['decisions'] ? json_encode($data['decisions']) : null,
        'annex_stack'    => $data['annex_stack'] ? json_encode($data['annex_stack']) : null,
        'annex_repo_url' => $data['annex_repo_url'] ?: null,
        'annex_retro'    => $data['annex_retro'] ?: null,
    ];
}

/**
 * Insert a new project. Returns its id.
 *
 * @param array<string, mixed> $data
 */
function createProject(array $data): int
{
    $stmt = db()->prepare(
        'INSERT INTO project
           (slug, title, year, status, context, role, result,
            decisions, annex_stack, annex_repo_url, annex_retro)
         VALUES
           (:slug, :title, :year, :status, :context, :role, :result,
            :decisions, :annex_stack, :annex_repo_url, :annex_retro)'
    );
    $stmt->execute(projectParams($data));

    return (int) db()->lastInsertId();
}

/**
 * Update an existing project.
 *
 * @param array<string, mixed> $data
 */
function updateProject(int $id, array $data): void
{
    $params = projectParams($data);
    $params['id'] = $id;

    db()->prepare(
        'UPDATE project SET
           slug = :slug, title = :title, year = :year, status = :status,
           context = :context, role = :role, result = :result,
           decisions = :decisions, annex_stack = :annex_stack,
           annex_repo_url = :annex_repo_url, annex_retro = :annex_retro
         WHERE id = :id'
    )->execute($params);
}

/**
 * Delete a project for good.
 */
function deleteProject(int $id): void
{
    db()->prepare('DELETE FROM project WHERE id = ?')->execute([$id]);
}
