<?php

declare(strict_types=1);

/**
 * Page shell. index.php has already captured the view's output into $content
 * (a string of HTML we produced ourselves — not user input, so not re-escaped).
 * $title comes from the view too.
 */

require __DIR__ . '/components/Head.php';
?>
<body>
  <?php require __DIR__ . '/components/Nav.php'; ?>

  <main class="layout__main">
    <?= $content ?>
  </main>

  <?php require __DIR__ . '/components/Footer.php'; ?>
</body>
</html>
