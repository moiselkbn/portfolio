<?php

declare(strict_types=1);

/**
 * Create / edit a project. Shared by /admin/projects/new and .../{id}/edit.
 * index.php provides: $form (current field values), $errors (field => message),
 * $isEdit (bool), and for edit $editId.
 * Wrapped by includes/layout-admin.php.
 */
$isEdit = $isEdit ?? false;
$title = ($isEdit ? 'Edit project' : 'New project') . ' — Admin';
$action = $isEdit
    ? url('admin/projects/' . $editId . '/edit')
    : url('admin/projects/new');

/** Small helper: the error line for a field, or nothing. */
$err = static function (string $field) use ($errors): string {
    return isset($errors[$field])
        ? '<span class="form__error">' . e($errors[$field]) . '</span>'
        : '';
};
?>

<h1><?= $isEdit ? 'Edit project' : 'New project' ?></h1>

<p><a href="<?= e(url('admin/projects')) ?>">&larr; Back to projects</a></p>

<form class="form form--wide" method="post" action="<?= e($action) ?>"
      enctype="multipart/form-data">
  <?= csrfField() ?>

  <p class="form__row">
    <label class="form__label" for="title">Title</label>
    <input class="form__input" type="text" id="title" name="title"
           value="<?= e($form['title']) ?>" required>
    <?= $err('title') ?>
  </p>

  <p class="form__row">
    <label class="form__label" for="year">Year</label>
    <input class="form__input" type="text" id="year" name="year"
           value="<?= e($form['year']) ?>" placeholder="2025 or 2023–2026" required>
    <?= $err('year') ?>
  </p>

  <p class="form__row">
    <label class="form__label" for="status">Status</label>
    <select class="form__input" id="status" name="status">
      <?php foreach (['draft', 'lab', 'featured'] as $s): ?>
        <option value="<?= $s ?>" <?= $form['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
      <?php endforeach; ?>
    </select>
    <?= $err('status') ?>
  </p>

  <p class="form__row">
    <label class="form__label" for="cover_image">Cover image (jpeg, png or webp)</label>
    <?php if (!empty($form['cover_image']) && $isEdit): ?>
      <img class="form__preview"
           src="<?= e(url('medias/' . slugify($form['title']) . '/' . $form['cover_image'])) ?>"
           alt="Current cover">
    <?php endif; ?>
    <input class="form__input" type="file" id="cover_image" name="cover_image"
           accept="image/jpeg,image/png,image/webp">
    <?= $err('cover_image') ?>
  </p>

  <p class="form__row">
    <label class="form__label" for="context">Context</label>
    <textarea class="form__input" id="context" name="context" rows="3"><?= e($form['context']) ?></textarea>
  </p>

  <p class="form__row">
    <label class="form__label" for="role">Role</label>
    <textarea class="form__input" id="role" name="role" rows="2"><?= e($form['role']) ?></textarea>
    <?= $err('role') ?>
  </p>

  <p class="form__row">
    <label class="form__label" for="result">Result</label>
    <textarea class="form__input" id="result" name="result" rows="2"><?= e($form['result']) ?></textarea>
  </p>

  <fieldset class="form__group">
    <legend>Decisions</legend>
    <?= $err('decisions') ?>
    <?php for ($i = 0; $i < 3; $i++): $d = $form['decisions'][$i] ?? []; ?>
      <div class="form__decision">
        <label class="form__label" for="d<?= $i ?>-probleme">Problem <?= $i + 1 ?></label>
        <textarea class="form__input" id="d<?= $i ?>-probleme" name="decisions[<?= $i ?>][probleme]" rows="2"><?= e($d['probleme'] ?? '') ?></textarea>
        <label class="form__label" for="d<?= $i ?>-options">Options</label>
        <textarea class="form__input" id="d<?= $i ?>-options" name="decisions[<?= $i ?>][options]" rows="2"><?= e($d['options'] ?? '') ?></textarea>
        <label class="form__label" for="d<?= $i ?>-choix">Choice</label>
        <textarea class="form__input" id="d<?= $i ?>-choix" name="decisions[<?= $i ?>][choix]" rows="2"><?= e($d['choix'] ?? '') ?></textarea>
      </div>
    <?php endfor; ?>
  </fieldset>

  <p class="form__row">
    <label class="form__label" for="annex_stack">Stack (comma-separated)</label>
    <input class="form__input" type="text" id="annex_stack" name="annex_stack"
           value="<?= e(implode(', ', $form['annex_stack'])) ?>" placeholder="PHP, CSS, JavaScript">
  </p>

  <p class="form__row">
    <label class="form__label" for="annex_repo_url">Repository URL</label>
    <input class="form__input" type="url" id="annex_repo_url" name="annex_repo_url"
           value="<?= e($form['annex_repo_url']) ?>">
    <?= $err('annex_repo_url') ?>
  </p>

  <p class="form__row">
    <label class="form__label" for="annex_retro">Retrospective (stored, never shown on the site)</label>
    <textarea class="form__input" id="annex_retro" name="annex_retro" rows="3"><?= e($form['annex_retro']) ?></textarea>
  </p>

  <button class="button" type="submit"><?= $isEdit ? 'Save' : 'Create project' ?></button>
</form>
