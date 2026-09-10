<?php

declare(strict_types=1);

/**
 * Contact form. Lives inside the footer on every page, so #contact scrolls
 * here from anywhere. Posts to /contact (handled in index.php, added in F3).
 */
?>
<form class="contact" method="post" action="<?= e(url('contact')) ?>">
  <?= csrfField() ?>

  <h2 class="contact__title">Let’s work together</h2>

  <div class="contact__fields">
    <div class="contact__row">
      <p class="field">
        <label class="field__label" for="c-first-name">First name</label>
        <input class="field__input" type="text" id="c-first-name" name="first_name"
               autocomplete="given-name">
      </p>
      <p class="field">
        <label class="field__label" for="c-last-name">Last name</label>
        <input class="field__input" type="text" id="c-last-name" name="last_name"
               autocomplete="family-name">
      </p>
    </div>

    <p class="field">
      <label class="field__label" for="c-email">
        Email address <span class="field__required">*</span>
      </label>
      <input class="field__input" type="email" id="c-email" name="email"
             required autocomplete="email">
    </p>

    <p class="field">
      <label class="field__label" for="c-subject">
        Subject <span class="field__required">*</span>
      </label>
      <input class="field__input" type="text" id="c-subject" name="subject" required>
    </p>

    <p class="field">
      <label class="field__label" for="c-message">
        Message <span class="field__required">*</span>
      </label>
      <textarea class="field__input field__input--message" id="c-message" name="message"
                rows="4" required></textarea>
    </p>
  </div>

  <button class="button" type="submit">Send message</button>
</form>
