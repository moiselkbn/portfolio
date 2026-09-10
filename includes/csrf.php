<?php

declare(strict_types=1);

/**
 * CSRF protection. One secret token per session, embedded as a hidden field in
 * every admin form and checked on every admin POST. A third-party site can make
 * the browser send a POST with the session cookie, but it cannot read or guess
 * this token.
 *
 * Requires an active session (startSession() in auth.php).
 */

/**
 * The session's CSRF token, created on first use.
 */
function csrfToken(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf'];
}

/**
 * The hidden input to drop inside every admin <form>.
 */
function csrfField(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrfToken()) . '">';
}

/**
 * Abort with 403 unless the POST carried this session's token.
 * Call at the top of every admin POST handler, before doing anything.
 */
function checkCsrf(): void
{
    $submitted = $_POST['csrf'] ?? '';
    $expected  = $_SESSION['csrf'] ?? '';

    // Reject on mismatch, and also when the session has no token at all
    // (hash_equals('', '') would otherwise be true). hash_equals is constant-time.
    if ($expected === '' || !hash_equals($expected, $submitted)) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }
}
