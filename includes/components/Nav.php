<?php

declare(strict_types=1);

/**
 * Site navigation (Figma component "Navbar", node 65:88).
 * Three groups spread with space-between: logo · availability status · links.
 * Contact is an anchor to the form in the footer, so it never gets an
 * "active" state — there's no dedicated page for it to match. Hidden
 * entirely on the 404 page ($view — decision of 11 September 2026): that
 * page has no footer, so #contact doesn't exist there and the link would
 * silently do nothing.
 * $path/$view come from the front controller (public/index.php) — same
 * top-level scope trick already used for $title in components/Head.php.
 */
?>
<nav class="navbar" aria-label="Main">
  <a class="navbar__home" href="<?= e(url()) ?>">
    <img class="navbar__home-image" src="<?= e(url('medias/title-name.svg')) ?>"
         alt="Moïse Lukebanu" width="1335" height="307">
  </a>

  <p class="navbar__status">
    Available for internship (Jan 2027 — Jun 2027)
    <span class="navbar__status-dot" aria-hidden="true"></span>
  </p>

  <ul class="navbar__list">
    <li>
      <a class="navbar__link" href="<?= e(url('about')) ?>"<?= $path === '/about' ? ' aria-current="page"' : '' ?>>About</a>
    </li>
    <li>
      <a class="navbar__link" href="<?= e(url('lab')) ?>"<?= $path === '/lab' ? ' aria-current="page"' : '' ?>>Lab</a>
    </li>
    <?php if ($view !== '404'): ?>
      <li><a class="navbar__link" href="#contact">Contact</a></li>
    <?php endif; ?>
  </ul>
</nav>
