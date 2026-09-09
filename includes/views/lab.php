<?php

declare(strict_types=1);

/** Lab. Wrapped by includes/layout.php. */
$title = 'Lab — Moïse Lukebanu';
$projects = getLabProjects();
?>

<h1>Lab</h1>

<ul class="project-list">
  <?php foreach ($projects as $project): ?>
    <li><?= e($project['title']) ?> — <?= e($project['year']) ?></li>
  <?php endforeach; ?>
</ul>
