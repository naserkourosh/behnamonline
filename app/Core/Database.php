<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Single shared PDO connection, configured for safe, predictable behavior:
 * real prepared statements, exceptions on error, associative fetches.
 */
final class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $host    = (string) Config::get('database.host', '127.0.0.1');
        $port    = (int) Config::get('database.port', 3306);
        $name    = (string) Config::get('database.database', 'behnam');
        $charset = (string) Config::get('database.charset', 'utf8mb4');

        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";

        try {
            self::$pdo = new PDO(
                $dsn,
                (string) Config::get('database.username', 'root'),
                (string) Config::get('database.password', ''),
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::ATTR_STRINGIFY_FETCHES  => false,
                ]
            );
        } catch (PDOException $e) {
            throw new RuntimeException('Database connection failed: ' . $e->getMessage(), (int) $e->getCode());
        }

        return self::$pdo;
    }

    /** Used by the migration runner to connect without selecting a database. */
    public static function serverConnection(): PDO
    {
        $host    = (string) Config::get('database.host', '127.0.0.1');
        $port    = (int) Config::get('database.port', 3306);
        $charset = (string) Config::get('database.charset', 'utf8mb4');

        return new PDO(
            "mysql:host={$host};port={$port};charset={$charset}",
            (string) Config::get('database.username', 'root'),
            (string) Config::get('database.password', ''),
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }
}
