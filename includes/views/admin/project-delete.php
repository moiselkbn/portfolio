<?php

declare(strict_types=1);

/**
 * Delete confirmation. index.php provides $project (the row) and $deleteId.
 * The actual delete is the POST of the form below. Wrapped by layout-admin.php.
 */
$title = 'Supprimer le projet — Admin';
?>

<h1>Supprimer ce projet ?</h1>

<p>
  <strong><?= e($project['title']) ?></strong> (<?= e($project['year']) ?>,
  <?= e(statusLabel($project['status'])) ?>) sera définitivement supprimé. Cette action est irréversible.
</p>

<form method="post" action="<?= e(url('admin/projects/' . $deleteId . '/delete')) ?>">
  <?= csrfField() ?>
  <button class="button button--danger" type="submit">Supprimer</button>
  <a href="<?= e(url('admin/projects/' . $deleteId . '/edit')) ?>">Annuler</a>
</form>
