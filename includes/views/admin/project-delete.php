<?php

declare(strict_types=1);

/**
 * Delete confirmation. index.php provides $project (the row) and $deleteId.
 * The actual delete is the POST of the form below. Wrapped by layout-admin.php.
 */
$title = 'Delete project — Admin';
?>

<h1>Delete this project?</h1>

<p>
  <strong><?= e($project['title']) ?></strong> (<?= e($project['year']) ?>,
  <?= e($project['status']) ?>) will be removed for good. This cannot be undone.
</p>

<form method="post" action="<?= e(url('admin/projects/' . $deleteId . '/delete')) ?>">
  <?= csrfField() ?>
  <button class="button button--danger" type="submit">Delete</button>
  <a href="<?= e(url('admin/projects/' . $deleteId . '/edit')) ?>">Cancel</a>
</form>
