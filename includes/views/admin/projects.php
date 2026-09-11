<?php

declare(strict_types=1);

/** Admin project list. Wrapped by includes/layout-admin.php. */
$title = 'Projets — Admin';
$projects = allProjects();
?>

<h1>Projets</h1>

<p><a class="button" href="<?= e(url('admin/projects/new')) ?>">Nouveau projet</a></p>

<table class="admin-table">
  <thead>
    <tr>
      <th>Titre</th>
      <th>Année</th>
      <th>Statut</th>
      <th>Mis à jour</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($projects as $p): ?>
      <tr>
        <td><?= e($p['title']) ?></td>
        <td><?= e($p['year']) ?></td>
        <td><?= e(statusLabel($p['status'])) ?></td>
        <td><?= e($p['updated_at']) ?></td>
        <td>
          <a href="<?= e(url('admin/projects/' . $p['id'] . '/edit')) ?>">Modifier</a>
          &middot;
          <a href="<?= e(url('admin/projects/' . $p['id'] . '/delete')) ?>">Supprimer</a>
        </td>
      </tr>
    <?php endforeach; ?>

    <?php if (!$projects): ?>
      <tr><td colspan="5">Aucun projet pour l'instant.</td></tr>
    <?php endif; ?>
  </tbody>
</table>
