<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

/**
 * Application bootstrap: loads environment & config, hardens error
 * handling, starts the session, builds the router, dispatches the request.
 */
final class Bootstrap
{
    public function __construct(private string $basePath)
    {
    }

    public function run(): void
    {
        Env::load($this->basePath . '/.env');
        Config::load($this->basePath . '/config');

        date_default_timezone_set((string) Config::get('app.timezone', 'Asia/Tehran'));
        mb_internal_encoding('UTF-8');

        $debug = (bool) Config::get('app.debug', false);
        error_reporting($debug ? E_ALL : 0);
        ini_set('display_errors', $debug ? '1' : '0');

        require $this->basePath . '/app/Support/helpers.php';

        $this->registerErrorHandling($debug);

        View::setPath($this->basePath . '/views');
        Session::start($this->basePath . '/storage/sessions');

        $router  = new Router();
        (require $this->basePath . '/routes/web.php')($router);
        (require $this->basePath . '/routes/api.php')($router);

        $request  = new Request();
        $response = $router->dispatch($request);
        $response->send();
    }

    private function registerErrorHandling(bool $debug): void
    {
        set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            if ((error_reporting() & $severity) === 0) {
                return false;
            }
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        set_exception_handler(function (Throwable $e) use ($debug): void {
            $this->logException($e);

            if (!headers_sent()) {
                http_response_code(500);
            }

            if ($debug) {
                echo '<pre style="direction:ltr;text-align:left;padding:20px;background:#1e1e1e;color:#eee;'
                    . 'font-family:monospace;font-size:13px;white-space:pre-wrap;">';
                echo e($e::class . ': ' . $e->getMessage()) . "\n\n";
                echo e($e->getFile() . ':' . $e->getLine()) . "\n\n";
                echo e($e->getTraceAsString());
                echo '</pre>';
            } else {
                echo View::renderError(500);
            }
        });
    }

    private function logException(Throwable $e): void
    {
        $logDir = $this->basePath . '/storage/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0775, true);
        }
        $line = sprintf(
            "[%s] %s: %s in %s:%d\n",
            date('Y-m-d H:i:s'),
            $e::class,
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        );
        @file_put_contents($logDir . '/app.log', $line, FILE_APPEND | LOCK_EX);
    }
}
