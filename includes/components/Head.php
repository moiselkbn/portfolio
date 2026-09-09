<?php

declare(strict_types=1);

/**
 * Document head. Expects $title (string) set by the current view.
 * Everything from <!doctype> down to </head>; <body> is opened by layout.php.
 */
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title ?? 'Portfolio') ?></title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Inter+Tight:ital,wght@0,100..900;1,100..900&display=swap">

  <link rel="stylesheet" href="<?= e(url('assets/style/style.css')) ?>">
</head>
