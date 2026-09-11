<?php

declare(strict_types=1);

/**
 * Admin sign-in. $loginError (string|null) is set by index.php on a failed POST.
 * Wrapped by includes/layout-admin.php.
 */
$title = 'Connexion — Admin';
?>

<h1>Connexion</h1>

<?php if (!empty($loginError)): ?>
  <p class="form__error"><?= e($loginError) ?></p>
<?php endif; ?>

<form class="form" method="post" action="<?= e(url('admin/login')) ?>">
  <?= csrfField() ?>

  <p class="form__row">
    <label class="form__label" for="username">Identifiant</label>
    <input class="form__input" type="text" id="username" name="username"
           autocomplete="username" required autofocus>
  </p>

  <p class="form__row">
    <label class="form__label" for="password">Mot de passe</label>
    <input class="form__input" type="password" id="password" name="password"
           autocomplete="current-password" required>
  </p>

  <button class="button" type="submit">Se connecter</button>
</form>
