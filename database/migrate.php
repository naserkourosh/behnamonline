<?php

declare(strict_types=1);

/**
 * Migration runner (CLI).
 *
 *   php database/migrate.php          # apply pending migrations
 *   php database/migrate.php --fresh  # drop & recreate the database first
 *
 * Applies the ordered .sql files in database/migrations/ and records each
 * in a `migrations` table so they run exactly once.
 */

use App\Core\Config;
use App\Core\Database;
use App\Core\Env;

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/app/Core/autoload.php';

Env::load(BASE_PATH . '/.env');
Config::load(BASE_PATH . '/config');

$dbName = (string) Config::get('database.database', 'behnam');
$fresh  = in_array('--fresh', $argv, true);

echo "→ Connecting to MySQL…\n";
$server = Database::serverConnection();

if ($fresh) {
    echo "→ Dropping database `{$dbName}`…\n";
    $server->exec("DROP DATABASE IF EXISTS `{$dbName}`");
}

$server->exec(
    "CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci"
);
echo "✓ Database `{$dbName}` ready.\n";

$pdo = Database::connection();
$pdo->exec(
    'CREATE TABLE IF NOT EXISTS migrations (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(191) NOT NULL UNIQUE,
        applied_at DATETIME NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci'
);

$applied = $pdo->query('SELECT filename FROM migrations')->fetchAll(PDO::FETCH_COLUMN);
$files   = glob(BASE_PATH . '/database/migrations/*.sql') ?: [];
sort($files);

$ran = 0;
foreach ($files as $file) {
    $name = basename($file);
    if (in_array($name, $applied, true)) {
        continue;
    }

    echo "→ Applying {$name}…\n";
    $sql = (string) file_get_contents($file);

    foreach (splitStatements($sql) as $statement) {
        $pdo->exec($statement);
    }

    $stmt = $pdo->prepare('INSERT INTO migrations (filename, applied_at) VALUES (?, ?)');
    $stmt->execute([$name, date('Y-m-d H:i:s')]);
    $ran++;
    echo "✓ {$name}\n";
}

echo $ran === 0 ? "Nothing to migrate. ✓\n" : "Done: {$ran} migration(s) applied. ✓\n";

/** Split a SQL file into individual statements (no procedures/strings with ';'). @return list<string> */
function splitStatements(string $sql): array
{
    $sql = preg_replace('/^\s*--.*$/m', '', $sql) ?? $sql;
    $parts = array_map('trim', explode(';', $sql));
    return array_values(array_filter($parts, static fn ($s) => $s !== ''));
}
