<?php

declare(strict_types=1);

/**
 * Image upload handling for the admin. Videos are NOT uploaded here — they are
 * placed by hand in public/medias/{slug}/ and referenced by filename.
 *
 * The flow: validateUploadedImage() says yes/no, then storeUploadedImage()
 * re-encodes the pixels to a fresh WebP (dropping anything hidden in the file)
 * and saves it under a random name.
 */

const UPLOAD_MAX_BYTES   = 3 * 1024 * 1024;   // 3 MB accepted at the door
const STORED_MAX_BYTES   = 300 * 1024;        // 300 KB once converted (CLAUDE.md)
const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

/**
 * Check one entry of $_FILES. Returns an error message, or null if the upload
 * is acceptable.
 *
 * @param array<string, mixed> $file one $_FILES['field'] entry
 */
function validateUploadedImage(array $file): ?string
{
    // 1. Did the transfer itself succeed? PHP fills $file['error'] with a code.
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return match ($file['error']) {
            UPLOAD_ERR_NO_FILE                        => 'Aucun fichier sélectionné.',
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Fichier trop volumineux.',
            default                                   => "L'upload a échoué.",
        };
    }

    // 2. Is tmp_name really a file PHP received via HTTP upload? (Not a path an
    //    attacker managed to inject — e.g. /etc/passwd.)
    if (!is_uploaded_file($file['tmp_name'])) {
        return 'Upload invalide.';
    }

    // 3. Our own size ceiling, before we spend effort decoding it.
    if ($file['size'] <= 0 || $file['size'] > UPLOAD_MAX_BYTES) {
        return 'Image trop lourde (3 Mo maximum).';
    }

    // 4. The REAL type, read from the file's first bytes — never $file['type'],
    //    which the browser sends and anyone can fake.
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!in_array($mime, ALLOWED_MIME_TYPES, true)) {
        return 'Format non accepté (jpeg, png ou webp).';
    }

    // 5. And the extension on the name the user's browser sent.
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS, true)) {
        return 'Extension non acceptée (.jpg, .png ou .webp).';
    }

    return null;
}

/**
 * Re-encode the uploaded image to a fresh WebP under public/medias/{slug}/ and
 * return the stored filename. Assumes validateUploadedImage() already passed.
 *
 * @param array<string, mixed> $file
 * @throws RuntimeException if GD cannot read/write, or the result is too heavy
 */
function storeUploadedImage(array $file, string $slug): string
{
    $dir = dirname(__DIR__, 2) . "/public/medias/{$slug}";
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        throw new RuntimeException("Impossible de créer le dossier {$dir}.");
    }

    $image = @imagecreatefromstring((string) file_get_contents($file['tmp_name']));
    if ($image === false) {
        throw new RuntimeException('Image illisible.');
    }

    $name = bin2hex(random_bytes(16)) . '.webp';
    $path = "{$dir}/{$name}";

    $ok = imagewebp($image, $path, 82);
    imagedestroy($image);
    if (!$ok) {
        throw new RuntimeException("Échec de l'écriture de l'image.");
    }

    if (filesize($path) > STORED_MAX_BYTES) {
        unlink($path);
        throw new RuntimeException(
            'Image trop lourde après conversion (> 300 Ko). Compresse-la avant de la recharger.'
        );
    }

    return $name;
}
