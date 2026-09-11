<?php

declare(strict_types=1);

/**
 * Create / edit a project. Shared by /admin/projects/new and .../{id}/edit.
 * index.php provides: $form (current field values), $errors (field => message),
 * $isEdit (bool), and for edit $editId.
 * Wrapped by includes/layout-admin.php.
 */
$isEdit = $isEdit ?? false;
$title = ($isEdit ? 'Modifier le projet' : 'Nouveau projet') . ' — Admin';
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

<h1><?= $isEdit ? 'Modifier le projet' : 'Nouveau projet' ?></h1>

<p><a href="<?= e(url('admin/projects')) ?>">&larr; Retour aux projets</a></p>

<form class="form form--wide" method="post" action="<?= e($action) ?>"
      enctype="multipart/form-data">
  <?= csrfField() ?>

  <p class="form__row">
    <label class="form__label" for="title">Titre</label>
    <input class="form__input" type="text" id="title" name="title"
           value="<?= e($form['title']) ?>" required>
    <?= $err('title') ?>
  </p>

  <p class="form__row">
    <label class="form__label" for="year">Année</label>
    <input class="form__input" type="text" id="year" name="year"
           value="<?= e($form['year']) ?>" placeholder="2025 ou 2023–2026" required>
    <?= $err('year') ?>
  </p>

  <p class="form__row">
    <label class="form__label" for="status">Statut</label>
    <select class="form__input" id="status" name="status">
      <?php foreach (['draft', 'lab', 'featured'] as $s): ?>
        <option value="<?= $s ?>" <?= $form['status'] === $s ? 'selected' : '' ?>><?= e(statusLabel($s)) ?></option>
      <?php endforeach; ?>
    </select>
    <?= $err('status') ?>
  </p>

  <p class="form__row">
    <label class="form__label" for="domains">Disciplines (séparées par des virgules)</label>
    <input class="form__input" type="text" id="domains" name="domains"
           value="<?= e(implode(', ', $form['domains'])) ?>" placeholder="3D, Web Dev, UI/UX" required>
    <?= $err('domains') ?>
  </p>

  <p class="form__row">
    <label class="form__label" for="cover_media">Media de couverture (jpeg, png, webp — une vidéo webm se dépose à la main, pas via ce champ)</label>
    <?php if (!empty($form['cover_media']) && $isEdit):
        // A hand-placed video cover is stored the same way as an image (just a
        // file name in cover_media) — the preview has to pick <video> or <img>
        // based on the extension, or a .webm would render as a broken image.
        $coverUrl = url('medias/' . slugify($form['title']) . '/' . $form['cover_media']);
        $coverExt = strtolower(pathinfo((string) $form['cover_media'], PATHINFO_EXTENSION));
    ?>
      <?php if ($coverExt === 'webm'): ?>
        <video class="form__preview" src="<?= e($coverUrl) ?>" controls muted loop></video>
      <?php else: ?>
        <img class="form__preview" src="<?= e($coverUrl) ?>" alt="Couverture actuelle">
      <?php endif; ?>
    <?php endif; ?>
    <input class="form__input" type="file" id="cover_media" name="cover_media"
           accept="image/jpeg,image/png,image/webp">
    <?= $err('cover_media') ?>
  </p>

  <p class="form__row">
    <label class="form__label" for="context">Contexte</label>
    <textarea class="form__input" id="context" name="context" rows="3"
              placeholder="Le contexte : pour qui, dans quel cadre, quel besoin de départ ?"><?= e($form['context']) ?></textarea>
  </p>

  <p class="form__row">
    <label class="form__label" for="role">Rôle</label>
    <textarea class="form__input" id="role" name="role" rows="2"
              placeholder="Ton rôle : ce que tu as fait concrètement sur ce projet."><?= e($form['role']) ?></textarea>
    <?= $err('role') ?>
  </p>

  <p class="form__row">
    <label class="form__label" for="result">Résultat</label>
    <textarea class="form__input" id="result" name="result" rows="2"
              placeholder="Le résultat : ce qui a été livré, l'impact obtenu."><?= e($form['result']) ?></textarea>
  </p>

  <fieldset class="form__group">
    <legend>Décisions</legend>
    <?= $err('decisions') ?>
    <?php for ($i = 0; $i < 3; $i++): $d = $form['decisions'][$i] ?? []; ?>
      <div class="form__decision">
        <label class="form__label" for="d<?= $i ?>-probleme">Problème <?= $i + 1 ?></label>
        <textarea class="form__input" id="d<?= $i ?>-probleme" name="decisions[<?= $i ?>][probleme]" rows="2"><?= e($d['probleme'] ?? '') ?></textarea>
        <label class="form__label" for="d<?= $i ?>-options">Options</label>
        <textarea class="form__input" id="d<?= $i ?>-options" name="decisions[<?= $i ?>][options]" rows="2"><?= e($d['options'] ?? '') ?></textarea>
        <label class="form__label" for="d<?= $i ?>-choix">Choix</label>
        <textarea class="form__input" id="d<?= $i ?>-choix" name="decisions[<?= $i ?>][choix]" rows="2"><?= e($d['choix'] ?? '') ?></textarea>
      </div>
    <?php endfor; ?>
  </fieldset>

  <p class="form__row">
    <label class="form__label" for="annex_stack">Stack (séparée par des virgules)</label>
    <input class="form__input" type="text" id="annex_stack" name="annex_stack"
           value="<?= e(implode(', ', $form['annex_stack'])) ?>" placeholder="PHP, CSS, JavaScript">
  </p>

  <p class="form__row">
    <label class="form__label" for="annex_repo_url">URL du dépôt</label>
    <input class="form__input" type="url" id="annex_repo_url" name="annex_repo_url"
           value="<?= e($form['annex_repo_url']) ?>">
    <?= $err('annex_repo_url') ?>
  </p>

  <p class="form__row">
    <label class="form__label" for="annex_retro">Rétrospective (enregistrée, jamais affichée sur le site)</label>
    <textarea class="form__input" id="annex_retro" name="annex_retro" rows="3"><?= e($form['annex_retro']) ?></textarea>
  </p>

  <button class="button" type="submit"><?= $isEdit ? 'Enregistrer' : 'Créer le projet' ?></button>
</form>
