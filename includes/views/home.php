<?php

declare(strict_types=1);

/** Home. Wrapped by includes/layout.php. */
$title = 'Moïse Lukebanu — Portfolio';
$projects = getFeaturedProjects();
?>

<h1>Home</h1>

<ul class="project-list">
  <?php foreach ($projects as $project): ?>
    <li>
      <a href="<?= e(url('projects/' . $project['slug'])) ?>">
        <?= e($project['title']) ?> — <?= e($project['year']) ?>
      </a>
    </li>
  <?php endforeach; ?>
</ul>
