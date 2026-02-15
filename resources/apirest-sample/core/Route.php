<?php

namespace Core;

use ReflectionFunction;
use ReflectionMethod;
use ReflectionException;
use ArgumentCountError;
use InvalidArgumentException;

/**
 * Simple router with parameterized routes, middleware, and dispatch.
 */
class Route
{
    protected static array $routes = []; // ['get' => [...], 'post' => [...]]
    protected static array $globalMiddleware = [];
    protected static $fallback = null;

    /**
     * Register GET route
     */
    public static function get(string $path, $handler, array $middleware = []): void
    {
        self::register('get', $path, $handler, $middleware);
    }

    public static function post(string $path, $handler, array $middleware = []): void
    {
        self::register('post', $path, $handler, $middleware);
    }

    public static function put(string $path, $handler, array $middleware = []): void
    {
        self::register('put', $path, $handler, $middleware);
    }

    public static function patch(string $path, $handler, array $middleware = []): void
    {
        self::register('patch', $path, $handler, $middleware);
    }

    public static function delete(string $path, $handler, array $middleware = []): void
    {
        self::register('delete', $path, $handler, $middleware);
    }

    public static function any(string $path, $handler, array $middleware = []): void
    {
        self::register('*', $path, $handler, $middleware);
    }

    protected static function register(string $method, string $path, $handler, array $middleware = []): void
    {
        [$regex, $paramNames] = self::compileRoute($path);

        self::$routes[$method][] = [
            'path' => $path,
            'regex' => $regex,
            'params' => $paramNames,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public static function setGlobalMiddleware(array $middleware): void
    {
        self::$globalMiddleware = $middleware;
    }

    public static function fallback($handler): void
    {
        self::$fallback = $handler;
    }

    public static function dispatch(): mixed
    {
        $request = new Request();
        $method = strtolower($request->method);
        $path = rtrim($request->route, '/') === '' ? '/' : rtrim($request->route, '/');

        $candidates = [];
        if (!empty(self::$routes[$method])) {
            $candidates = array_merge($candidates, self::$routes[$method]);
        }
        if (!empty(self::$routes['*'])) {
            $candidates = array_merge($candidates, self::$routes['*']);
        }

        foreach ($candidates as $route) {
            if (preg_match('#^' . $route['regex'] . '$#', $path, $matches)) {
                $params = [];
                foreach ($route['params'] as $i => $name) {
                    $params[$name] = $matches[$i + 1] ?? null;
                }

                $middlewareStack = array_merge(self::$globalMiddleware, $route['middleware']);

                $finalHandler = function (Request $req) use ($route, $params) {
                    return self::callHandler($route['handler'], $req, $params);
                };

                $pipeline = array_reduce(
                    array_reverse($middlewareStack),
                    function ($next, $middleware) {
                        return function (Request $req) use ($middleware, $next) {
                            return self::callMiddleware($middleware, $req, $next);
                        };
                    },
                    $finalHandler
                );

                $result = $pipeline($request);
                if ($result !== null) {
                    echo $result;
                }
                return $result;
            }
        }

        if (self::$fallback !== null) {
            $result = self::callHandler(self::$fallback, $request, []);
            if ($result !== null) echo $result;
            return $result;
        }

        http_response_code(404);
        echo '404 Not Found';
        return null;
    }

    protected static function compileRoute(string $path): array
    {
        $paramNames = [];

        $path = rtrim($path, '/');
        if ($path === '') $path = '/';

        $regex = preg_replace_callback('/\{([^}]+)\}/', function ($m) use (&$paramNames) {
            $paramNames[] = $m[1];
            return '([^\/]+)';
        }, $path);

        // escape slashes and other regex significant chars (but keep our capture groups)
        $regex = preg_quote($regex, '#');
        // restore parentheses from capture groups (preg_quote will escape them)
        $regex = str_replace('\([^\\\/]\+\)', '([^\/]+)', $regex);

        return [$regex, $paramNames];
    }

    protected static function callHandler($handler, Request $req, array $routeParams)
    {
        // support "Controller@method"
        if (is_string($handler) && strpos($handler, '@') !== false) {
            [$controller, $method] = explode('@', $handler, 2);
            $handler = [$controller, $method];
        }

        if (is_array($handler) && count($handler) === 2 && is_string($handler[0]) && class_exists($handler[0])) {
            $handler[0] = new $handler[0]();
        }

        if (!is_callable($handler)) {
            throw new InvalidArgumentException('Route handler is not callable. Handler: ' . print_r($handler, true));
        }

        $callArgs = [];
        try {
            if ($handler instanceof \Closure || (is_callable($handler) && !is_array($handler))) {
                $ref = new ReflectionFunction($handler);
            } else {
                $ref = new ReflectionMethod($handler[0], $handler[1]);
            }

            foreach ($ref->getParameters() as $param) {
                $paramType = $param->getType();
                $paramName = $param->getName();

                if ($paramType && !$paramType->isBuiltin() && $paramType->getName() === Request::class) {
                    $callArgs[] = $req;
                    continue;
                }
                if ($paramName === 'request') {
                    $callArgs[] = $req;
                    continue;
                }

                if (array_key_exists($paramName, $routeParams)) {
                    $callArgs[] = $routeParams[$paramName];
                    continue;
                }

                if (!empty($routeParams)) {
                    $callArgs[] = array_shift($routeParams);
                    continue;
                }

                if ($param->isDefaultValueAvailable()) {
                    $callArgs[] = $param->getDefaultValue();
                } else {
                    $callArgs[] = null;
                }
            }
        } catch (ReflectionException $e) {
            $callArgs = array_merge([$req], array_values($routeParams));
        }

        try {
            return call_user_func_array($handler, $callArgs);
        } catch (ArgumentCountError $e) {
            return call_user_func($handler);
        }
    }

    protected static function callMiddleware($middleware, Request $req, callable $next)
    {
        if (is_string($middleware) && strpos($middleware, '@') !== false) {
            [$class, $method] = explode('@', $middleware, 2);
            if (class_exists($class)) {
                $instance = new $class();
                if (is_callable([$instance, $method])) {
                    return call_user_func([$instance, $method], $req, $next);
                }
            }
        }

        if (is_string($middleware) && class_exists($middleware)) {
            $instance = new $middleware();
            if (is_callable([$instance, 'handle'])) {
                return call_user_func([$instance, 'handle'], $req, $next);
            }
        }

        if (is_callable($middleware)) {
            return call_user_func($middleware, $req, $next);
        }

        throw new InvalidArgumentException('Middleware is not callable: ' . print_r($middleware, true));
    }
}
