<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/helpers.php';

/**
 * Front controller. Every request that is not a real file or directory is
 * rewritten here (public/.htaccess) and matched against the five public routes.
 */

// Path the app is served from: "/PORTFOLIO_2026_V3/public" locally, "" in production.
define('BASE_PATH', rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/'));

// Requested path, without the base prefix and without the query string.
$uri = strtok($_SERVER['REQUEST_URI'], '?');
$path = '/' . trim(substr($uri, strlen(BASE_PATH)), '/');

// Static routes: exact path => view file name (in includes/views/, without .php).
$routes = [
    '/'      => 'home',
    '/about' => 'about',
    '/lab'   => 'lab',
];

$view = $routes[$path] ?? null;
$slug = null;

// Dynamic route: /projects/{slug}
if ($view === null && preg_match('#^/projects/([a-z0-9-]+)$#', $path, $matches) === 1) {
    $view = 'project';
    $slug = $matches[1];
}

if ($view === null) {
    http_response_code(404);
    $view = '404';
}

// Render the view into a string (it sets $title and prints its own HTML),
// then wrap that string in the shared layout.
$title = 'Portfolio';
ob_start();
require dirname(__DIR__) . "/includes/views/{$view}.php";
$content = ob_get_clean();

require dirname(__DIR__) . '/includes/layout.php';
