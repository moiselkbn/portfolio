<?php

declare(strict_types=1);

/**
 * Escape a value for safe output inside HTML. Every dynamic value printed into
 * a page goes through this, without exception (CLAUDE.md § Sécurité — XSS).
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Build an absolute URL path from the app root.
 * Local: "/PORTFOLIO_2026_V3/public/about" — production: "/about".
 * Relies on the BASE_PATH constant defined by the front controller.
 */
function url(string $path = ''): string
{
    return BASE_PATH . '/' . ltrim($path, '/');
}
