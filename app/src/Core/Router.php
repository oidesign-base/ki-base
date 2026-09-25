<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Minimal router.
 *   $router->get('/batches/{id}', [BatchController::class, 'show']);
 * Options: ['auth' => false] for public pages, ['guest' => true] for pages
 * only for logged-out users (login). Every POST is CSRF-checked.
 */
final class Router
{
    private array $routes = [];

    public function get(string $pattern, callable|array $handler, array $options = []): void
    {
        $this->add('GET', $pattern, $handler, $options);
    }

    public function post(string $pattern, callable|array $handler, array $options = []): void
    {
        $this->add('POST', $pattern, $handler, $options);
    }

    private function add(string $method, string $pattern, callable|array $handler, array $options): void
    {
        $regex = preg_replace('#\{([a-z_]+)\}#', '(?P<$1>\d+)', $pattern);
        $this->routes[] = [
            'method'  => $method,
            'regex'   => '#^' . $regex . '$#',
            'handler' => $handler,
            'options' => $options + ['auth' => true, 'guest' => false],
        ];
    }

    public function dispatch(Request $request): Response
    {
        $methodMatched = false;

        foreach ($this->routes as $route) {
            if (!preg_match($route['regex'], $request->path, $m)) {
                continue;
            }
            $methodMatched = true;
            if ($route['method'] !== $request->method) {
                continue;
            }

            $params = array_map('intval', array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY));

            if ($request->method === 'POST') {
                Csrf::verify($request->input('_token'));
            }

            if ($route['options']['guest'] && Auth::check()) {
                return Response::redirect(url('/'));
            }

            if ($route['options']['auth'] && !Auth::check()) {
                if ($request->method === 'GET') {
                    Session::set('_intended', $request->uri());
                }
                return Response::redirect(url('/login'));
            }

            $handler = $route['handler'];
            if (is_array($handler)) {
                [$class, $method] = $handler;
                $handler = [new $class(), $method];
            }

            $result = $handler($request, ...array_values($params));
            return $result instanceof Response ? $result : Response::html((string) $result);
        }

        throw new HttpException($methodMatched ? 405 : 404);
    }
}
