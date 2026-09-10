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
    '/'               => 'home',
    '/about'          => 'about',
    '/lab'            => 'lab',
    '/admin'              => 'admin/dashboard',
    '/admin/login'        => 'admin/login',
    '/admin/projects'     => 'admin/projects',
    '/admin/projects/new' => 'admin/project-form',
];

$view = $routes[$path] ?? null;
$slug = null;

// Dynamic route: /projects/{slug}
if ($view === null && preg_match('#^/projects/([a-z0-9-]+)$#', $path, $matches) === 1) {
    $view = 'project';
    $slug = $matches[1];
}

// Dynamic route: /admin/projects/{id}/edit
$editId = null;
if ($view === null && preg_match('#^/admin/projects/(\d+)/edit$#', $path, $matches) === 1) {
    $view = 'admin/project-form';
    $editId = (int) $matches[1];
}

// Dynamic route: /admin/projects/{id}/delete
$deleteId = null;
if ($view === null && preg_match('#^/admin/projects/(\d+)/delete$#', $path, $matches) === 1) {
    $view = 'admin/project-delete';
    $deleteId = (int) $matches[1];
}

if ($view === null) {
    http_response_code(404);
    $view = '404';
}

// Admin section: session, login handling, access guard.
$loginError = null;
if (str_starts_with($view, 'admin/')) {
    require_once dirname(__DIR__) . '/includes/admin/projects.php';
    require_once dirname(__DIR__) . '/includes/admin/uploads.php';
    require_once dirname(__DIR__) . '/includes/flash.php';
    startSession();

    if ($view === 'admin/login') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            checkCsrf();
            if (attemptLogin(
                (string) ($_POST['username'] ?? ''),
                (string) ($_POST['password'] ?? '')
            )) {
                header('Location: ' . url('admin'));
                exit;
            }
            http_response_code(401);
            $loginError = 'Identifiants incorrects.';
        } elseif (currentAdmin() !== null) {
            header('Location: ' . url('admin'));
            exit;
        }
    } elseif (currentAdmin() === null) {
        // Every admin page except the login form needs a signed-in admin.
        header('Location: ' . url('admin/login'));
        exit;
    }

    // Create / edit a project (same form).
    if ($view === 'admin/project-form') {
        $isEdit = $editId !== null;
        $errors = [];

        if ($isEdit) {
            $project = findProjectById($editId);
            if ($project === null) {
                http_response_code(404);
                $view = '404';
            } else {
                $form = projectFormFromRow($project);
            }
        } else {
            $form = emptyProjectForm();
        }

        if ($view === 'admin/project-form' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            checkCsrf();
            $form = projectDataFromPost();
            // cover_image is not a text field: keep the current one unless a new
            // upload replaces it below.
            $form['cover_image'] = $isEdit ? ($project['cover_image'] ?? null) : null;

            $errors = validateProject($form, $editId);

            $hasUpload = ($_FILES['cover_image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
            if ($hasUpload) {
                $uploadError = validateUploadedImage($_FILES['cover_image']);
                if ($uploadError !== null) {
                    $errors['cover_image'] = $uploadError;
                }
            }

            // Write the file only once the whole form is valid.
            if (!$errors && $hasUpload) {
                try {
                    $form['cover_image'] = storeUploadedImage(
                        $_FILES['cover_image'],
                        slugify($form['title'])
                    );
                } catch (RuntimeException $e) {
                    $errors['cover_image'] = $e->getMessage();
                }
            }

            if (!$errors) {
                if ($isEdit) {
                    updateProject($editId, $form);
                    flash('Project saved.');
                    header('Location: ' . url("admin/projects/{$editId}/edit"));
                } else {
                    $editId = createProject($form);
                    flash('Project created.');
                    header('Location: ' . url("admin/projects/{$editId}/edit"));
                }
                exit;
            }
        }
    }

    // Delete a project — GET shows a confirmation page, POST does it.
    if ($view === 'admin/project-delete') {
        $project = findProjectById($deleteId);
        if ($project === null) {
            http_response_code(404);
            $view = '404';
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            checkCsrf();
            deleteProject($deleteId);
            flash('Project deleted.');
            header('Location: ' . url('admin/projects'));
            exit;
        }
    }
}

// The public project page only exists for a real project; anything else 404s.
// (No "$project = null" here — the admin delete branch sets $project too and
// this ran after it, blanking it before the view rendered.)
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
