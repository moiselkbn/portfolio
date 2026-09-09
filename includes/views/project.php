<?php

declare(strict_types=1);

/**
 * Project page. $project is the row loaded by index.php
 * (findFeaturedProjectBySlug); index.php has already 404'd if it was null.
 * Wrapped by includes/layout.php.
 */
$title = $project['title'] . ' — Moïse Lukebanu';
?>

<h1><?= e($project['title']) ?></h1>
<p><?= e($project['year']) ?></p>
