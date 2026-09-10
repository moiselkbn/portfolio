<?php

declare(strict_types=1);

/**
 * Global error handling. Call registerErrorHandlers() once, early in index.php.
 * After that, any exception nobody catches lands in handleUncaughtException()
 * instead of producing a blank page (display_errors is off in production).
 */
function registerErrorHandlers(): void
{
    set_exception_handler('handleUncaughtException');
}

/**
 * Last-resort handler for an exception that bubbled all the way up.
 */
function handleUncaughtException(\Throwable $e): void
{
    // Full trace to the server log — never to the page.
    error_log((string) $e);

    // Drop any half-rendered page so the 500 is the only thing sent.
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    http_response_code(500);

    if (APP_ENV === 'local') {
        echo '<pre>' . e((string) $e) . '</pre>';
    } else {
        require dirname(__DIR__) . '/includes/views/500.php';
    }

    exit;
}
