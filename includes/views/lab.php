<?php

declare(strict_types=1);

/**
 * Lab. Wrapped by includes/layout.php.
 *
 * CSS alone can't wrap this grid correctly: a project's caption never spans
 * more than one column, but its media can outnumber the columns available at
 * the current breakpoint, and a plain grid only has the column axis left to
 * grow into once a row is full — it drifts sideways instead of wrapping
 * down (see the 2-column test that produced a 137px horizontal overflow).
 * So PHP does the packing itself, once per column count the CSS can be at
 * (2/4/6 — style.css § Lab), and hands each cell its row/column for every
 * one of those as CSS custom properties. The matching @media block then
 * just reads the pair for its own column count.
 *
 * Real data now (getLabProjects(), most recent year first) — the hardcoded
 * multi-project test that exercised the wrapping logic is gone; see the
 * scratchpad backup from earlier in the build if that's ever needed again.
 */
$title = 'Lab — Moïse Lukebanu';

/**
 * Every column count the CSS grid can be at (style.css § Lab) — the packing
 * below runs once per value here.
 */
const LAB_COLUMN_COUNTS = [2, 4, 6];

/**
 * Works out where every caption and media cell lands for one column count.
 * A single cursor, $mediaCol: a caption always takes the same column as its
 * project's own first media, so it's placed at $mediaCol's current value
 * (row above, same column) rather than counted separately — an earlier
 * version gave captions their own counter, which drifted away from the
 * media cursor and left captions sitting over the wrong project's images
 * once a row held more than one project.
 *
 * Returns a flat list of ['row' => int, 'col' => int], one per cell, in the
 * exact order the caller will render them (a project's caption, then every
 * one of its media, then the next project's caption, ...).
 *
 * @param array<int, array{media: array<int, mixed>}> $projects
 * @return list<array{row: int, col: int}>
 */
function labPackTier(array $projects, int $columnCount): array
{
    $placements = [];
    $band = 1;
    $mediaCol = 0;

    foreach ($projects as $project) {
        $mediaCount = count($project['media']);
        if ($mediaCount === 0) {
            continue;
        }

        // Starting a new project: if this band's media row is already full,
        // move on before placing the caption — otherwise the caption would
        // sit a band above its own media instead of directly over it.
        if ($mediaCol >= $columnCount) {
            $band++;
            $mediaCol = 0;
        }

        $placements[] = ['row' => 2 * $band - 1, 'col' => $mediaCol + 1];

        for ($i = 0; $i < $mediaCount; $i++) {
            if ($mediaCol >= $columnCount) {
                $band++;
                $mediaCol = 0;
            }
            $placements[] = ['row' => 2 * $band, 'col' => $mediaCol + 1];
            $mediaCol++;
        }
    }

    return $placements;
}

/**
 * The `--r2:..;--c2:..;--r3:..;...` string for one cell, read off
 * $placementsByTier by its position in the flattened cell order.
 */
function labCellStyle(array $placementsByTier, int $cellIndex): string
{
    $style = '';
    foreach (LAB_COLUMN_COUNTS as $columnCount) {
        $cell = $placementsByTier[$columnCount][$cellIndex];
        $style .= "--r{$columnCount}:{$cell['row']};--c{$columnCount}:{$cell['col']};";
    }

    return $style;
}

// getLabProjects() already orders by year DESC (includes/projects.php) and
// decodes gallery/domains from JSON. Reshape each row into the {caption,
// media} the packing function above expects, skipping any project whose
// gallery is still empty and numbering only the ones actually shown — same
// reasoning as the earlier single-project version of this page.
$number = 0;
$labProjects = [];
foreach (getLabProjects() as $project) {
    if (!$project['gallery']) {
        continue;
    }
    $number++;

    $media = [];
    foreach ($project['gallery'] as $image) {
        // A gallery entry can be a video (.webm) placed by hand, same as
        // cover_media — an <img> can't render one, see admin/project-form.php.
        $isVideo = strtolower(pathinfo((string) $image['src'], PATHINFO_EXTENSION)) === 'webm';
        $media[] = [
            'type' => $isVideo ? 'video' : 'image',
            'src' => url('medias/' . $project['slug'] . '/' . $image['src']),
            'alt' => ($image['caption'] ?? '') !== '' ? $image['caption'] : $project['title'],
        ];
    }

    $labProjects[] = [
        'caption' => sprintf(
            '%02d · %s, %s, %s',
            $number,
            $project['title'],
            implode(', ', $project['domains']),
            $project['year']
        ),
        'media' => $media,
    ];
}

// One packing pass per column count, kept side by side so labCellStyle()
// can pull "this cell's position at 2 columns, at 3, at 4..." all at once
// instead of re-running the algorithm per cell.
$placementsByTier = [];
foreach (LAB_COLUMN_COUNTS as $columnCount) {
    $placementsByTier[$columnCount] = labPackTier($labProjects, $columnCount);
}

$cellIndex = 0;
?>

<div class="lab">
  <?php foreach ($labProjects as $project): ?>
    <h4 class="lab__caption" style="<?= e(labCellStyle($placementsByTier, $cellIndex++)) ?>">
      <?= e($project['caption']) ?>
    </h4>
    <?php foreach ($project['media'] as $media): ?>
      <?php if ($media['type'] === 'video'): ?>
        <video class="lab__image" style="<?= e(labCellStyle($placementsByTier, $cellIndex++)) ?>"
               src="<?= e($media['src']) ?>" autoplay muted loop playsinline></video>
      <?php else: ?>
        <img class="lab__image" style="<?= e(labCellStyle($placementsByTier, $cellIndex++)) ?>"
             src="<?= e($media['src']) ?>" alt="<?= e($media['alt']) ?>" loading="lazy">
      <?php endif; ?>
    <?php endforeach; ?>
  <?php endforeach; ?>
</div>
