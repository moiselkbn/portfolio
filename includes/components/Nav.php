<?php

declare(strict_types=1);

/**
 * Site navigation (Figma component "Navbar", node 101:712).
 * Three groups spread with space-between: logo · availability status · links.
 * Contact is an anchor to the form at the bottom of the home page.
 */
?>
<nav class="navbar" aria-label="Main">
  <a class="navbar__home" href="<?= e(url()) ?>">Moïse Lukebanu</a>

  <p class="navbar__status">
    Available for internship (Jan 2027 — Jun 2027)
    <span class="navbar__status-dot" aria-hidden="true"></span>
  </p>

  <ul class="navbar__list">
    <li><a class="navbar__link" href="<?= e(url('about')) ?>">About</a></li>
    <li><a class="navbar__link" href="<?= e(url('lab')) ?>">Lab</a></li>
    <li><a class="navbar__link" href="<?= e(url('#contact')) ?>">Contact</a></li>
  </ul>
</nav>
