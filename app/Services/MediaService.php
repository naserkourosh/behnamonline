<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Validated image uploads for the admin panel. Stores files under
 * public/uploads/{folder}/{YYYYMM}/ with a random name and returns the
 * web-relative path (e.g. "uploads/products/202607/ab12cd.jpg").
 */
final class MediaService
{
    private const MAX_BYTES = 3 * 1024 * 1024; // 3 MB
    private const ALLOWED   = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'webp' => 'image/webp',
        'gif'  => 'image/gif',
    ];

    /**
     * @param array{name?:string,type?:string,tmp_name?:string,error?:int,size?:int} $file
     */
    public function store(array $file, string $folder): ?string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }
        if (($file['size'] ?? 0) <= 0 || $file['size'] > self::MAX_BYTES) {
            return null;
        }

        $ext = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        if (!isset(self::ALLOWED[$ext])) {
            return null;
        }

        // Verify real MIME (not just the client-provided extension).
        $tmp  = (string) ($file['tmp_name'] ?? '');
        $mime = function_exists('finfo_open')
            ? (new \finfo(FILEINFO_MIME_TYPE))->file($tmp)
            : (self::ALLOWED[$ext]);
        if ($mime === false || !in_array($mime, self::ALLOWED, true)) {
            return null;
        }
        // Raster images must be decodable.
        if (@getimagesize($tmp) === false) {
            return null;
        }

        $folder   = preg_replace('/[^a-z0-9_-]/i', '', $folder) ?: 'misc';
        $sub      = $folder . '/' . date('Ym');
        $destDir  = PUBLIC_PATH . '/uploads/' . $sub;
        if (!is_dir($destDir) && !@mkdir($destDir, 0775, true) && !is_dir($destDir)) {
            return null;
        }

        $name = bin2hex(random_bytes(8)) . '.' . $ext;
        $dest = $destDir . '/' . $name;

        $moved = is_uploaded_file($tmp) ? move_uploaded_file($tmp, $dest) : @rename($tmp, $dest);
        if (!$moved) {
            return null;
        }

        return 'uploads/' . $sub . '/' . $name;
    }

    /**
     * Attach an existing media-library file by COPYING it into $folder with a
     * fresh name. A copy (not a reference) keeps per-product image deletion
     * safe: removing it can't break other records sharing the library file.
     */
    public function importFromLibrary(string $path, string $folder): ?string
    {
        if (!str_starts_with($path, 'uploads/') || str_contains($path, '..')) {
            return null;
        }
        $src = PUBLIC_PATH . '/' . $path;
        $ext = strtolower(pathinfo($src, PATHINFO_EXTENSION));
        if (!isset(self::ALLOWED[$ext]) || !is_file($src) || @getimagesize($src) === false) {
            return null;
        }

        $folder  = preg_replace('/[^a-z0-9_-]/i', '', $folder) ?: 'misc';
        $sub     = $folder . '/' . date('Ym');
        $destDir = PUBLIC_PATH . '/uploads/' . $sub;
        if (!is_dir($destDir) && !@mkdir($destDir, 0775, true) && !is_dir($destDir)) {
            return null;
        }

        $name = bin2hex(random_bytes(8)) . '.' . $ext;
        return @copy($src, $destDir . '/' . $name) ? 'uploads/' . $sub . '/' . $name : null;
    }

    /** Remove a previously stored upload (path is web-relative). */
    public function delete(?string $path): void
    {
        if ($path === null || !str_starts_with($path, 'uploads/')) {
            return; // never touch seed/placeholder assets
        }
        $full = PUBLIC_PATH . '/' . $path;
        if (is_file($full)) {
            @unlink($full);
        }
    }
}
