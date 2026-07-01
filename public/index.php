<?php
/**
 * بهنام (Behnam) — front controller.
 *
 * Apache rewrites every non-file request here (see public/.htaccess).
 * Responsibilities: bootstrap the environment, then dispatch the router.
 */

declare(strict_types=1);

use App\Core\Bootstrap;

define('BEHNAM_START', microtime(true));
define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/app/Core/autoload.php';

(new Bootstrap(BASE_PATH))->run();
