<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Media library for the admin panel: browses, uploads, and deletes every
 * file under public/uploads/ (product images, blog covers, popups, and
 * anything uploaded directly here). Images are validated as decodable
 * rasters; short promo videos (mp4/webm) are accepted up to a larger cap.
 */
final class MediaLibraryService
{
    private const IMAGE_MAX = 3 * 1024 * 1024;   // 3 MB
    private const VIDEO_MAX = 30 * 1024 * 1024;  // 30 MB

    private const IMAGES = [
        'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
        'webp' => 'image/webp', 'gif' => 'image/gif',
    ];
    private const VIDEOS = [
        'mp4' => 'video/mp4', 'webm' => 'video/webm',
    ];

    private function root(): string
    {
        return BASE_PATH . '/public/uploads';
    }

    /** @return list<string> Top-level folders under uploads/. */
    public function folders(): array
    {
        $root = $this->root();
        if (!is_dir($root)) {
            return [];
        }
        $out = [];
        foreach ((array) scandir($root) as $entry) {
            if ($entry !== '.' && $entry !== '..' && is_dir($root . '/' . $entry)) {
                $out[] = $entry;
            }
        }
        sort($out);
        return $out;
    }

    /**
     * List files (newest first). Optionally restrict to a top-level folder.
     * @return list<array<string,mixed>>
     */
    public function list(string $folder = '', int $limit = 300): array
    {
        $root = $this->root();
        $base = $folder !== '' ? $root . '/' . preg_replace('/[^a-z0-9_-]/i', '', $folder) : $root;
        if (!is_dir($base)) {
            return [];
        }

        $files = [];
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($base, \FilesystemIterator::SKIP_DOTS)
        );
        foreach ($it as $file) {
            if (!$file->isFile()) {
                continue;
            }
            $ext = strtolower($file->getExtension());
            if (!isset(self::IMAGES[$ext]) && !isset(self::VIDEOS[$ext])) {
                continue;
            }
            $abs  = str_replace('\\', '/', $file->getPathname());
            $rel  = 'uploads' . explode('/public/uploads', $abs, 2)[1];
            $files[] = [
                'path'     => $rel,
                'name'     => $file->getFilename(),
                'ext'      => $ext,
                'size'     => $file->getSize(),
                'mtime'    => $file->getMTime(),
                'is_video' => isset(self::VIDEOS[$ext]),
            ];
        }
        usort($files, static fn ($a, $b) => $b['mtime'] <=> $a['mtime']);
        return array_slice($files, 0, max(1, $limit));
    }

    /** @return array{images:int,videos:int,bytes:int} */
    public function stats(): array
    {
        $images = 0;
        $videos = 0;
        $bytes  = 0;
        foreach ($this->list('', 100000) as $f) {
            $bytes += (int) $f['size'];
            if ($f['is_video']) {
                $videos++;
            } else {
                $images++;
            }
        }
        return ['images' => $images, 'videos' => $videos, 'bytes' => $bytes];
    }

    /**
     * Validate & store an uploaded file. Returns the web-relative path or null.
     * @param array{name?:string,type?:string,tmp_name?:string,error?:int,size?:int} $file
     */
    public function store(array $file, string $folder = 'library'): ?string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }
        $ext   = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        $isVid = isset(self::VIDEOS[$ext]);
        $isImg = isset(self::IMAGES[$ext]);
        if (!$isVid && !$isImg) {
            return null;
        }

        $size = (int) ($file['size'] ?? 0);
        $max  = $isVid ? self::VIDEO_MAX : self::IMAGE_MAX;
        if ($size <= 0 || $size > $max) {
            return null;
        }

        $tmp   = (string) ($file['tmp_name'] ?? '');
        $allow = $isVid ? self::VIDEOS : self::IMAGES;
        $mime  = function_exists('finfo_open') ? (new \finfo(FILEINFO_MIME_TYPE))->file($tmp) : $allow[$ext];
        if ($mime === false || !in_array($mime, $allow, true)) {
            return null;
        }
        if ($isImg && @getimagesize($tmp) === false) {
            return null;
        }

        $folder  = preg_replace('/[^a-z0-9_-]/i', '', $folder) ?: 'library';
        $sub     = $folder . '/' . date('Ym');
        $destDir = $this->root() . '/' . $sub;
        if (!is_dir($destDir) && !@mkdir($destDir, 0775, true) && !is_dir($destDir)) {
            return null;
        }

        $name = bin2hex(random_bytes(8)) . '.' . $ext;
        $dest = $destDir . '/' . $name;
        $moved = is_uploaded_file($tmp) ? move_uploaded_file($tmp, $dest) : @rename($tmp, $dest);

        return $moved ? 'uploads/' . $sub . '/' . $name : null;
    }

    /** Delete a file (web-relative path); refuses anything outside uploads/. */
    public function delete(string $path): bool
    {
        if (!str_starts_with($path, 'uploads/') || str_contains($path, '..')) {
            return false;
        }
        $full = BASE_PATH . '/public/' . $path;
        if (is_file($full)) {
            return @unlink($full);
        }
        return false;
    }
}
