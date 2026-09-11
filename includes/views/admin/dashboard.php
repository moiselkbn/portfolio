<?php

declare(strict_types=1);

/**
 * Admin home. index.php has already redirected here only if signed in.
 * Wrapped by includes/layout-admin.php.
 */
$admin = currentAdmin();
$title = 'Admin';
?>

<h1>Admin</h1>
<p>Connecté en tant que <?= e($admin['username']) ?>.</p>

<p><a href="<?= e(url('admin/projects')) ?>">Gérer les projets</a></p>

<form method="post" action="<?= e(url('admin/logout')) ?>">
  <?= csrfField() ?>
  <button class="button" type="submit">Se déconnecter</button>
</form>
