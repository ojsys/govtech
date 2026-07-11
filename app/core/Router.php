<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Tiny regex router. Routes registered as METHOD + path pattern -> [Controller, method].
 * Patterns support {param} placeholders captured and passed to the action.
 */
final class Router
{
    /** @var array<int,array{method:string,regex:string,params:string[],handler:mixed}> */
    private array $routes = [];

    public function get(string $path, array|callable $handler): void { $this->map('GET', $path, $handler); }
    public function post(string $path, array|callable $handler): void { $this->map('POST', $path, $handler); }
    public function put(string $path, array|callable $handler): void { $this->map('PUT', $path, $handler); }
    public function delete(string $path, array|callable $handler): void { $this->map('DELETE', $path, $handler); }

    public function map(string $method, string $path, array|callable $handler): void
    {
        $params = [];
        $regex = preg_replace_callback('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', function ($m) use (&$params) {
            $params[] = $m[1];
            return '([^/]+)';
        }, $path);
        $this->routes[] = [
            'method'  => $method,
            'regex'   => '#^' . $regex . '$#',
            'params'  => $params,
            'handler' => $handler,
        ];
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $path = $request->path();
        $allowed = [];

        foreach ($this->routes as $route) {
            if (!preg_match($route['regex'], $path, $matches)) {
                continue;
            }
            if ($route['method'] !== $method) {
                $allowed[] = $route['method'];
                continue;
            }
            array_shift($matches);
            $args = array_combine($route['params'], $matches) ?: [];
            $this->invoke($route['handler'], $request, $args);
            return;
        }

        if ($allowed !== []) {
            header('Allow: ' . implode(', ', array_unique($allowed)));
            $this->abort($request, 405, 'Method Not Allowed');
        }
        $this->abort($request, 404, 'Page Not Found');
    }

    private function invoke(array|callable $handler, Request $request, array $args): void
    {
        if (is_callable($handler)) {
            $handler($request, $args);
            return;
        }
        [$class, $action] = $handler;
        $controller = new $class();
        $controller->{$action}($request, $args);
    }

    private function abort(Request $request, int $code, string $message): never
    {
        http_response_code($code);
        if ($request->wantsJson()) {
            json_response(['error' => $message], $code);
        }
        $view = new View();
        // errors/404 and errors/500 views; falls back to plain text if missing.
        $tpl = $code === 404 ? 'errors/404' : 'errors/500';
        if (is_file(VIEW_PATH . '/pages/' . $tpl . '.php')) {
            echo $view->render('pages/' . $tpl, ['code' => $code, 'message' => $message]);
        } else {
            echo "<h1>{$code}</h1><p>" . e($message) . '</p>';
        }
        exit;
    }
}
