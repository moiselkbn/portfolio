<?php

declare(strict_types=1);

/**
 * Not found. Wrapped by includes/layout.php.
 * The giant "ERROR404" is prebuilt art (public/medias/ERROR404.svg), not live
 * type — same reasoning as the navbar wordmark (see Nav.php): its contour is
 * baked into the file, so no CSS text-stroke/text-shadow trick is needed
 * here, and none of the letterform artifacts that came with them.
 * assets/js/error-404.js scatters medias/404.svg randomly behind the content
 * below — loaded only here, on no other page.
 * No footer here — decision of 11 September 2026: this page is a fixed,
 * non-scrolling full screen (see .error-404 in style.css), so a footer below
 * it could never be reached by scrolling anyway.
 */
$title = 'Page not found — Moïse Lukebanu';
$hideFooter = true;

// Cache-bust the script: this file has been iterated on a lot, and a browser
// that had cached an older copy would silently keep testing stale logic.
// filemtime() ties the query string to the file's own last-modified time, so
// it only ever changes when the file actually does.
$scatterScriptPath = dirname(__DIR__, 2) . '/public/assets/js/error-404.js';
$scatterScriptVersion = file_exists($scatterScriptPath) ? filemtime($scatterScriptPath) : time();
?>

<div class="error-404">
  <div class="error-404__scatter" data-image-src="<?= e(url('medias/404.svg')) ?>"></div>

  <div class="error-404__heading">
    <h1><img class="error-404__number" src="<?= e(url('medias/ERROR404.svg')) ?>"
             alt="404" width="1170" height="313"></h1>
    <h2 class="error-404__subtitle">This page could not be found</h2>
  </div>
  <a class="button" href="<?= e(url()) ?>">Go to main menu</a>
</div>

<script type="module" src="<?= e(url('assets/js/error-404.js') . '?v=' . $scatterScriptVersion) ?>"></script>
