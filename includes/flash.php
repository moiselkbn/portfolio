<?php

declare(strict_types=1);

/**
 * One-shot messages that survive a redirect: set before header('Location'),
 * read once on the next page, then gone. Admin only (needs an active session).
 */

function flash(string $message): void
{
    $_SESSION['flash'] = $message;
}

/**
 * Return the pending message (or null) and clear it.
 */
function takeFlash(): ?string
{
    $message = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return $message;
}
