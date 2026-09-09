<?php

declare(strict_types=1);

/**
 * Create an admin account. Command line only.
 *
 *   php scripts/create-admin.php <username>
 *
 * Prompts for a password (hidden), hashes it with bcrypt, inserts the row.
 * There are exactly two admins and no signup page — this is how they are made.
 */

if (PHP_SAPI !== 'cli') {
    exit("This script runs from the command line only.\n");
}

require __DIR__ . '/../includes/database.php';

$username = $argv[1] ?? null;
if ($username === null || trim($username) === '') {
    fwrite(STDERR, "Usage: php scripts/create-admin.php <username>\n");
    exit(1);
}
$username = trim($username);

// Read the password without echoing it to the terminal.
fwrite(STDOUT, "Password for '{$username}': ");
shell_exec('stty -echo 2>/dev/null');
$password = trim((string) fgets(STDIN));
shell_exec('stty echo 2>/dev/null');
fwrite(STDOUT, "\n");

if (strlen($password) < 12) {
    fwrite(STDERR, "Password must be at least 12 characters.\n");
    exit(1);
}

$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

$stmt = db()->prepare('INSERT INTO admin_user (username, password_hash) VALUES (?, ?)');

try {
    $stmt->execute([$username, $hash]);
    fwrite(STDOUT, "Created admin '{$username}'.\n");
} catch (PDOException $e) {
    // SQLSTATE 23000 = integrity constraint violation (here: duplicate username).
    $message = $e->getCode() === '23000'
        ? "An admin named '{$username}' already exists.\n"
        : "Could not create the admin: {$e->getMessage()}\n";
    fwrite(STDERR, $message);
    exit(1);
}
