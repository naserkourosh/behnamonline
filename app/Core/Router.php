<?php

declare(strict_types=1);

namespace App\Core;

use Closure;

/**
 * Lightweight router with named params ({slug}, {id}) and per-route
 * middleware. Handlers are [Controller::class, 'method'] pairs or closures.
 */
final class Router
{
    /** @var list<array{method:string,pattern:string,regex:string,vars:list<string>,handler:mixed,middleware:list<class-string>}> */
    private array $routes = [];

    /** @var list<class-string> */
    private array $groupMiddleware = [];

    /**
     * Register a group of routes that share middleware.
     * @param list<class-string> $middleware
     */
    public function group(array $middleware, Closure $callback): void
    {
        $previous = $this->groupMiddleware;
        $this->groupMiddleware = array_merge($previous, $middleware);
        $callback($this);
        $this->groupMiddleware = $previous;
    }

    /** @param mixed $handler @param list<class-string> $middleware */
    public function get(string $path, mixed $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    /** @param mixed $handler @param list<class-string> $middleware */
    public function post(string $path, mixed $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    /** @param mixed $handler @param list<class-string> $middleware */
    public function add(string $method, string $path, mixed $handler, array $middleware = []): void
    {
        $path = '/' . trim($path, '/');
        $vars = [];

        $regex = preg_replace_callback('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', static function ($m) use (&$vars) {
            $vars[] = $m[1];
            return '([^/]+)';
        }, $path);

        $this->routes[] = [
            'method'     => $method,
            'pattern'    => $path,
            'regex'      => '#^' . $regex . '$#u',
            'vars'       => $vars,
            'handler'    => $handler,
            'middleware' => array_merge($this->groupMiddleware, $middleware),
        ];
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->method();
        $path   = $request->path();
        $methodMatched = false;

        foreach ($this->routes as $route) {
            if (!preg_match($route['regex'], $path, $matches)) {
                continue;
            }
            if ($route['method'] !== $method) {
                $methodMatched = true;
                continue;
            }

            array_shift($matches);
            $params = array_combine($route['vars'], $matches) ?: [];
            $request->setParams($params);

            return $this->runPipeline($route, $request);
        }

        $status = $methodMatched ? 405 : 404;
        return $this->errorResponse($request, $status);
    }

    /** @param array{handler:mixed,middleware:list<class-string>} $route */
    private function runPipeline(array $route, Request $request): Response
    {
        $core = function (Request $req) use ($route): Response {
            return $this->invoke($route['handler'], $req);
        };

        $pipeline = array_reduce(
            array_reverse($route['middleware']),
            function (Closure $next, string $middlewareClass): Closure {
                return function (Request $req) use ($middlewareClass, $next): Response {
                    /** @var Middleware $mw */
                    $mw = new $middlewareClass();
                    return $mw->handle($req, $next);
                };
            },
            $core
        );

        return $pipeline($request);
    }

    private function invoke(mixed $handler, Request $request): Response
    {
        if ($handler instanceof Closure) {
            return $handler($request);
        }

        [$class, $methodName] = $handler;
        $controller = new $class();
        return $controller->$methodName($request);
    }

    private function errorResponse(Request $request, int $status): Response
    {
        if ($request->wantsJson()) {
            return Response::json(['error' => $status === 404 ? 'Not Found' : 'Method Not Allowed'], $status);
        }
        $html = View::renderError($status);
        return Response::html($html, $status);
    }
}
