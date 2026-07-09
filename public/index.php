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
// The web root is wherever THIS file lives (public/ locally, public_html on
// a real host). Uploads and asset versioning must use this, not BASE_PATH.
define('PUBLIC_PATH', __DIR__);

require BASE_PATH . '/app/Core/autoload.php';

(new Bootstrap(BASE_PATH))->run();
