<?php

declare(strict_types=1);

/**
 * Site footer, identical on every page. The contact form itself lives inside
 * this footer on the home page only (added in a later step).
 */
?>
<footer class="footer" id="contact">
  <ul class="footer__list">
    <li>© <?= date('Y') ?> Moïse Lukebanu</li>
    <li><a class="footer__link" href="<?= e(url('cv/moise-lukebanu-cv.pdf')) ?>">CV</a></li>
    <li><a class="footer__link" href="https://github.com/moiselkbn" rel="me">GitHub</a></li>
    <li><a class="footer__link" href="https://www.linkedin.com/in/moiselukebanu/" rel="me">LinkedIn</a></li>
  </ul>
</footer>
