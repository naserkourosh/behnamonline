<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Simple fixed-window rate limiter backed by the filesystem cache
 * (no DB/Redis dependency for the slice). Keyed by client + bucket name.
 */
final class RateLimiter
{
    private string $dir;

    public function __construct()
    {
        $this->dir = BASE_PATH . '/storage/cache/ratelimit';
        if (!is_dir($this->dir)) {
            @mkdir($this->dir, 0775, true);
        }
    }

    /**
     * Returns true if the action is allowed (and records a hit),
     * false if the limit for the current window has been exceeded.
     */
    public function attempt(string $bucket, string $clientId, int $maxAttempts, int $windowSeconds): bool
    {
        $key  = $bucket . ':' . $clientId;
        $file = $this->dir . '/' . sha1($key) . '.json';
        $now  = time();

        $data = ['count' => 0, 'reset' => $now + $windowSeconds];
        if (is_file($file)) {
            $decoded = json_decode((string) file_get_contents($file), true);
            if (is_array($decoded) && ($decoded['reset'] ?? 0) > $now) {
                $data = $decoded;
            }
        }

        if ($data['count'] >= $maxAttempts) {
            return false;
        }

        $data['count']++;
        @file_put_contents($file, json_encode($data), LOCK_EX);
        return true;
    }
}
