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
<p>Signed in as <?= e($admin['username']) ?>.</p>

<form method="post" action="<?= e(url('admin/logout')) ?>">
  <button class="button" type="submit">Sign out</button>
</form>
