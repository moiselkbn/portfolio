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
 * The empty form shape — used for a blank "new project" form and as the base
 * both createProject() and validateProject() expect.
 *
 * @return array<string, mixed>
 */
function emptyProjectForm(): array
{
    return [
        'title'          => '',
        'year'           => '',
        'status'         => 'draft',
        'cover_image'    => null,
        'context'        => '',
        'role'           => '',
        'result'         => '',
        'decisions'      => [],
        'annex_stack'    => [],
        'annex_repo_url' => '',
        'annex_retro'    => '',
    ];
}

/**
 * Turn a DB row (from findProjectById) into the form shape, decoding the JSON
 * columns back to arrays.
 *
 * @param array<string, mixed> $row
 * @return array<string, mixed>
 */
function projectFormFromRow(array $row): array
{
    return [
        'title'          => (string) $row['title'],
        'year'           => (string) $row['year'],
        'status'         => (string) $row['status'],
        'cover_image'    => $row['cover_image'] ?? null,
        'context'        => (string) ($row['context'] ?? ''),
        'role'           => (string) ($row['role'] ?? ''),
        'result'         => (string) ($row['result'] ?? ''),
        'decisions'      => $row['decisions'] ? json_decode((string) $row['decisions'], true) : [],
        'annex_stack'    => $row['annex_stack'] ? json_decode((string) $row['annex_stack'], true) : [],
        'annex_repo_url' => (string) ($row['annex_repo_url'] ?? ''),
        'annex_retro'    => (string) ($row['annex_retro'] ?? ''),
    ];
}

/**
 * Turn the raw $_POST of the project form into the clean shape above:
 * empty decision slots dropped, annex_stack split on commas.
 *
 * @return array<string, mixed>
 */
function projectDataFromPost(): array
{
    $decisions = [];
    foreach ((array) ($_POST['decisions'] ?? []) as $d) {
        $slot = [
            'probleme' => trim((string) ($d['probleme'] ?? '')),
            'options'  => trim((string) ($d['options'] ?? '')),
            'choix'    => trim((string) ($d['choix'] ?? '')),
        ];
        if ($slot['probleme'] !== '' || $slot['options'] !== '' || $slot['choix'] !== '') {
            $decisions[] = $slot;
        }
    }

    $stack = array_values(array_filter(
        array_map('trim', explode(',', (string) ($_POST['annex_stack'] ?? '')))
    ));

    return [
        'title'          => trim((string) ($_POST['title'] ?? '')),
        'year'           => trim((string) ($_POST['year'] ?? '')),
        'status'         => (string) ($_POST['status'] ?? 'draft'),
        'context'        => trim((string) ($_POST['context'] ?? '')),
        'role'           => trim((string) ($_POST['role'] ?? '')),
        'result'         => trim((string) ($_POST['result'] ?? '')),
        'decisions'      => $decisions,
        'annex_stack'    => $stack,
        'annex_repo_url' => trim((string) ($_POST['annex_repo_url'] ?? '')),
        'annex_retro'    => trim((string) ($_POST['annex_retro'] ?? '')),
    ];
}

/**
 * Server-side validation. Returns a map of field name => error message
 * (empty array = the data is good to save). $exceptId is the project being
 * edited, so its own slug does not count as a collision.
 *
 * @param array<string, mixed> $data
 * @return array<string, string>
 */
function validateProject(array $data, ?int $exceptId = null): array
{
    $errors = [];

    if ($data['title'] === '') {
        $errors['title'] = 'Le titre est obligatoire.';
    } elseif (slugExists(slugify($data['title']), $exceptId)) {
        $errors['title'] = 'Un projet avec un titre similaire existe déjà.';
    }

    // /u: treat the string as UTF-8 so the en-dash "–" is one character.
    if (!preg_match('/^\d{4}(\s*[–-]\s*\d{4})?$/u', $data['year'])) {
        $errors['year'] = 'Format d\'année invalide (ex. 2025 ou 2023–2026).';
    }

    if (!in_array($data['status'], ['draft', 'lab', 'featured'], true)) {
        $errors['status'] = 'Statut invalide.';
    }

    if ($data['annex_repo_url'] !== ''
        && !filter_var($data['annex_repo_url'], FILTER_VALIDATE_URL)) {
        $errors['annex_repo_url'] = 'URL invalide.';
    }

    if ($data['status'] === 'featured') {
        if ($data['role'] === '') {
            $errors['role'] = 'Le rôle est obligatoire pour un projet mis en avant.';
        }
        if (count($data['decisions']) < 3) {
            $errors['decisions'] = 'Un projet mis en avant demande au moins 3 décisions.';
        }
    }

    return $errors;
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
        'cover_image'    => $data['cover_image'] ?: null,
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
           (slug, title, year, status, cover_image, context, role, result,
            decisions, annex_stack, annex_repo_url, annex_retro)
         VALUES
           (:slug, :title, :year, :status, :cover_image, :context, :role, :result,
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
           cover_image = :cover_image, context = :context, role = :role,
           result = :result, decisions = :decisions, annex_stack = :annex_stack,
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
