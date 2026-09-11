<?php

declare(strict_types=1);

/**
 * Page shell. index.php has already captured the view's output into $content
 * (a string of HTML we produced ourselves — not user input, so not re-escaped).
 * $title comes from the view too. A view can set $hideFooter = true before
 * this file loads (404.php does) to render without the footer — that page is
 * a fixed, non-scrolling full screen, so the footer would be unreachable
 * scroll-past content anyway.
 */

require __DIR__ . '/components/Head.php';
?>
<body<?= empty($hideFooter) ? '' : ' class="layout--no-scroll"' ?>>
  <?php require __DIR__ . '/components/Nav.php'; ?>

  <main class="layout__main">
    <?= $content ?>
  </main>

  <?php if (empty($hideFooter)): ?>
    <?php require __DIR__ . '/components/Footer.php'; ?>
  <?php endif; ?>
</body>
</html>
