<?php

declare(strict_types=1);

/**
 * Admin sign-in. $loginError (string|null) is set by index.php on a failed POST.
 * Wrapped by includes/layout-admin.php.
 */
$title = 'Sign in — Admin';
?>

<h1>Sign in</h1>

<?php if (!empty($loginError)): ?>
  <p class="form__error"><?= e($loginError) ?></p>
<?php endif; ?>

<form class="form" method="post" action="<?= e(url('admin/login')) ?>">
  <?= csrfField() ?>

  <p class="form__row">
    <label class="form__label" for="username">Username</label>
    <input class="form__input" type="text" id="username" name="username"
           autocomplete="username" required autofocus>
  </p>

  <p class="form__row">
    <label class="form__label" for="password">Password</label>
    <input class="form__input" type="password" id="password" name="password"
           autocomplete="current-password" required>
  </p>

  <button class="button" type="submit">Sign in</button>
</form>
