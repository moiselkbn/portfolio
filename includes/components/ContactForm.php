<?php

declare(strict_types=1);

/**
 * Contact form. Lives inside the footer on every page, so #contact scrolls
 * here from anywhere. Posts to /contact; index.php validates and redirects
 * back here (PRG). This file reads the one-shot result and redraws itself:
 * a success line, or per-field errors with the typed values kept.
 */

$c = takeContactState();

/** The error line for a field, or '' — printed just under the field. */
$err = static fn (string $field): string => isset($c['errors'][$field])
    ? '<span class="field__error" id="c-' . $field . '-error">' . e($c['errors'][$field]) . '</span>'
    : '';

/** The value the visitor last typed (already escaped), or ''. */
$old = static fn (string $field): string => e($c['old'][$field] ?? '');

/** aria hooks tying an invalid field to its message, or ''. */
$invalid = static fn (string $field): string => isset($c['errors'][$field])
    ? ' aria-invalid="true" aria-describedby="c-' . $field . '-error"'
    : '';
?>
<form class="contact" method="post" action="<?= e(url('contact')) ?>">
  <?= csrfField() ?>

  <h2 class="contact__title">Let’s work together</h2>

  <?php if ($c['sent']): ?>
    <p class="contact__status" role="status">Thanks — your message has been sent.</p>
  <?php endif; ?>

  <div class="contact__fields">
    <div class="contact__row">
      <p class="field">
        <label class="field__label" for="c-first-name">First name</label>
        <input class="field__input" type="text" id="c-first-name" name="first_name"
               value="<?= $old('first_name') ?>" autocomplete="given-name"<?= $invalid('first_name') ?>>
        <?= $err('first_name') ?>
      </p>
      <p class="field">
        <label class="field__label" for="c-last-name">Last name</label>
        <input class="field__input" type="text" id="c-last-name" name="last_name"
               value="<?= $old('last_name') ?>" autocomplete="family-name"<?= $invalid('last_name') ?>>
        <?= $err('last_name') ?>
      </p>
    </div>

    <p class="field">
      <label class="field__label" for="c-email">
        Email address <span class="field__required">*</span>
      </label>
      <input class="field__input" type="email" id="c-email" name="email"
             value="<?= $old('email') ?>" required autocomplete="email"<?= $invalid('email') ?>>
      <?= $err('email') ?>
    </p>

    <p class="field">
      <label class="field__label" for="c-subject">
        Subject <span class="field__required">*</span>
      </label>
      <input class="field__input" type="text" id="c-subject" name="subject"
             value="<?= $old('subject') ?>" required<?= $invalid('subject') ?>>
      <?= $err('subject') ?>
    </p>

    <p class="field">
      <label class="field__label" for="c-message">
        Message <span class="field__required">*</span>
      </label>
      <textarea class="field__input field__input--message" id="c-message" name="message"
                rows="4" required<?= $invalid('message') ?>><?= $old('message') ?></textarea>
      <?= $err('message') ?>
    </p>
  </div>

  <?php /* Honeypot: off-screen, skipped by humans, often filled by bots. */ ?>
  <p class="contact__hp" aria-hidden="true">
    <label for="c-website">Leave this field empty</label>
    <input type="text" id="c-website" name="website" tabindex="-1" autocomplete="off">
  </p>
  <?php /* Submit timestamp: an instant submit is a bot (see contactLooksLikeSpam). */ ?>
  <input type="hidden" name="loaded_at" value="<?= time() ?>">

  <button class="button" type="submit">Send message</button>
</form>
