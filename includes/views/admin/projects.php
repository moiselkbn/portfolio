<?php

declare(strict_types=1);

/** Admin project list. Wrapped by includes/layout-admin.php. */
$title = 'Projects — Admin';
$projects = allProjects();
?>

<h1>Projects</h1>

<p><a class="button" href="<?= e(url('admin/projects/new')) ?>">New project</a></p>

<table class="admin-table">
  <thead>
    <tr>
      <th>Title</th>
      <th>Year</th>
      <th>Status</th>
      <th>Updated</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($projects as $p): ?>
      <tr>
        <td><?= e($p['title']) ?></td>
        <td><?= e($p['year']) ?></td>
        <td><?= e($p['status']) ?></td>
        <td><?= e($p['updated_at']) ?></td>
        <td><a href="<?= e(url('admin/projects/' . $p['id'] . '/edit')) ?>">Edit</a></td>
      </tr>
    <?php endforeach; ?>

    <?php if (!$projects): ?>
      <tr><td colspan="5">No projects yet.</td></tr>
    <?php endif; ?>
  </tbody>
</table>
