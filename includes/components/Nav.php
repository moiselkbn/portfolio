<?php

declare(strict_types=1);

/**
 * Site navigation, identical on every page.
 * Contact is an anchor to the form at the bottom of the home page, so the link
 * always points at "<home>#contact".
 */
?>
<nav class="navbar" aria-label="Main">
  <a class="navbar__home" href="<?= e(url()) ?>">Moïse Lukebanu</a>

  <ul class="navbar__list">
    <li><a class="navbar__link" href="<?= e(url('about')) ?>">About</a></li>
    <li><a class="navbar__link" href="<?= e(url('lab')) ?>">Lab</a></li>
    <li><a class="navbar__link" href="<?= e(url('#contact')) ?>">Contact</a></li>
  </ul>

  <p class="navbar__status">🔍 Looking for an internship — January 2027</p>
</nav>
