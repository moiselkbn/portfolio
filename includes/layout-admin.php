<?php

declare(strict_types=1);

/**
 * Bare shell for /admin/* pages — no public nav or footer.
 * index.php has captured the view output into $content and set $title.
 */

require __DIR__ . '/components/Head.php';
?>
<body class="admin">
  <main class="admin-main">
    <?= $content ?>
  </main>
</body>
</html>
