<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/helpers.php';
require_once dirname(__DIR__) . '/includes/projects.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/csrf.php';

/**
 * Front controller. Every request that is not a real file or directory is
 * rewritten here (public/.htaccess) and matched against the routes below.
 */

// Path the app is served from: "/PORTFOLIO_2026_V3/public" locally, "" in production.
define('BASE_PATH', rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/'));

// Requested path, without the base prefix and without the query string.
$uri = strtok($_SERVER['REQUEST_URI'], '?');
$path = '/' . trim(substr($uri, strlen(BASE_PATH)), '/');

// Sign out is an action, not a page — POST only, then back to the login page.
if ($path === '/admin/logout' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    startSession();
    checkCsrf();
    logout();
    header('Location: ' . url('admin/login'));
    exit;
}

// Static routes: exact path => view file name (in includes/views/, without .php).
$routes = [
    '/'            => 'home',
    '/about'       => 'about',
    '/lab'         => 'lab',
    '/admin'       => 'admin/dashboard',
    '/admin/login' => 'admin/login',
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

// Admin section: session, login submission, access guard.
$loginError = null;
if (str_starts_with($view, 'admin/')) {
    startSession();

    if ($view === 'admin/login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        checkCsrf();
        $ok = attemptLogin(
            (string) ($_POST['username'] ?? ''),
            (string) ($_POST['password'] ?? '')
        );
        if ($ok) {
            header('Location: ' . url('admin'));
            exit;
        }
        http_response_code(401);
        $loginError = 'Identifiants incorrects.';
    }

    // Already signed in? Skip the login page.
    if ($view === 'admin/login' && currentAdmin() !== null) {
        header('Location: ' . url('admin'));
        exit;
    }
    // Not signed in? The dashboard is off limits.
    if ($view === 'admin/dashboard' && currentAdmin() === null) {
        header('Location: ' . url('admin/login'));
        exit;
    }
}

// The project page only exists for a real project; anything else is a 404.
$project = null;
if ($view === 'project') {
    $project = findFeaturedProjectBySlug($slug);
    if ($project === null) {
        http_response_code(404);
        $view = '404';
    }
}

// Render the view into a string (it sets $title and prints its own HTML),
// then wrap it in the matching layout.
$layout = str_starts_with($view, 'admin/') ? 'layout-admin' : 'layout';
$title = 'Portfolio';
ob_start();
require dirname(__DIR__) . "/includes/views/{$view}.php";
$content = ob_get_clean();

require dirname(__DIR__) . "/includes/{$layout}.php";
