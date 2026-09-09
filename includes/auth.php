<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';

/**
 * Admin authentication. Session-based, two accounts, no visitor accounts,
 * no signup page (accounts are created with scripts/create-admin.php).
 */

/**
 * Start the session once, with a hardened cookie.
 * Only called on /admin/* — public visitors never get a session cookie.
 */
function startSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_set_cookie_params([
        'path'     => '/',
        'httponly' => true,               // JS can't read the cookie
        'samesite' => 'Lax',              // not sent on cross-site POSTs
        'secure'   => !empty($_SERVER['HTTPS']), // HTTPS only in production
    ]);

    session_start();
}

/**
 * The signed-in admin (id + username), or null.
 *
 * @return array{id:int, username:string}|null
 */
function currentAdmin(): ?array
{
    return $_SESSION['admin'] ?? null;
}

/**
 * Redirect to the login page when nobody is signed in. Used to guard pages.
 */
function requireAdmin(): void
{
    if (currentAdmin() === null) {
        header('Location: ' . url('admin/login'));
        exit;
    }
}

/**
 * Destroy the session completely (data, cookie, server-side store).
 */
function logout(): void
{
    $_SESSION = [];

    $params = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires'  => time() - 42000,
        'path'     => $params['path'],
        'domain'   => $params['domain'],
        'secure'   => $params['secure'],
        'httponly' => $params['httponly'],
        'samesite' => $params['samesite'],
    ]);

    session_destroy();
}

/**
 * Check a username/password pair. On success, sign the admin in and return true.
 * On any failure (unknown user OR wrong password) return false — the caller
 * must not be able to tell which.
 */
function attemptLogin(string $username, string $password): bool
{
    $stmt = db()->prepare('SELECT * FROM admin_user WHERE username = ?');
    $stmt->execute([$username]);
    $row = $stmt->fetch();

    if($row !== false && password_verify($password, $row['password_hash'])){
        session_regenerate_id(true);
        $_SESSION['admin'] = [
            'id' => (int) $row['id'],
            'username' => $row['username']
        ];
        return true;
    }
    return false;
}

