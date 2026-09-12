<?php

declare(strict_types=1);

/**
 * Site footer — identical on every page. Full viewport height (Figma).
 * The contact form is included on every page.
 */
?>
<footer class="footer" id="contact">
  <div class="footer__inner">
    <div class="footer__links">
      <ul class="footer__buttons">
        <li>
          <a class="button button--external" href="https://github.com/moiselkbn">
            <span class="button__content">
              <img class="button__icon" src="<?= e(url('assets/icons/github.png')) ?>"
                   alt="" width="24" height="24">
              <span>GitHub</span>
            </span>
            <span class="button__arrow" aria-hidden="true"></span>
          </a>
        </li>
        <li>
          <a class="button button--external" href="https://www.linkedin.com/in/moiselukebanu/">
            <span class="button__content">
              <img class="button__icon" src="<?= e(url('assets/icons/linkedin.svg')) ?>"
                   alt="" width="24" height="24">
              <span>LinkedIn</span>
            </span>
            <span class="button__arrow" aria-hidden="true"></span>
          </a>
        </li>
        <li>
          <a class="button" href="<?= e(url('cv/moise-lukebanu-cv.pdf')) ?>" download>
            <span class="button__content">
              <img class="button__icon" src="<?= e(url('assets/icons/arrow-external.svg')) ?>"
                   alt="" width="24" height="24" style="transform: rotate(90deg);">
              <span>Download the resume</span>
            </span>
          </a>
        </li>
      </ul>

      <p class="footer__copyright">Moïse Lukebanu, <?= date('Y') ?></p>
    </div>

    <p class="footer__location">
      Brussels, Belgium — <span data-brussels-time>—</span>
    </p>

    <?php require __DIR__ . '/ContactForm.php'; ?>
  </div>
</footer>
