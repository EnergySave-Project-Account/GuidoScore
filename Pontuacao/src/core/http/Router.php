<?php
class Router
{
    private array $routes = [];
    private string $currentRouteName = '';

    public function get(mixed $path, mixed $action, array $options = []){
        $this->add('GET', $path, $action, $options);
    }

    public function post(mixed $path, mixed $action, array $options = []){
        $this->add('POST', $path, $action, $options);
    }

    public function delete(mixed $path, mixed $action, array $options = []){
        $this->add('DELETE', $path, $action, $options);
    }

    private function add(mixed $method, mixed $path, mixed $action, array $options = []){
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'action' => $action,
            'options' => $options
        ];
    }

    public function dispatch(mixed $method, mixed $uri){
        $method = strtoupper($method);
        $uri = $this->cleanUri($uri);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;

            $pattern = $this->convertRouteToRegex($route['path']);

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                $this->setCurrentRouteName($route['path']);
                $this->applyRouteOptions($route['options'] ?? []);
                return $this->runAction($route['action'], $matches);
            }
        }

        http_response_code(404);
        if (function_exists('view')) {
            return view('404');
        }

        echo "404 Not Found";
        return null;
    }

    private function applyRouteOptions(array $options): void {
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $isStateChanging = !in_array($requestMethod, ['GET', 'HEAD', 'OPTIONS'], true);

        if ($isStateChanging) {
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

            if (strpos($contentType, 'application/json') === false) {
                http_response_code(415);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => 'Tipo inválido.']);
                exit;
            }
        }

        foreach ($options as $key => $value) {
            switch ($key) {
                case 'rateLimit':
                    if ($value === true) {
                        RateLimitService::verify($this->currentRouteName);
                    } elseif (is_array($value)) {
                        RateLimitService::verify(
                            $this->currentRouteName,
                            $value['maxAttempts'] ?? 10,
                            $value['windowSeconds'] ?? 60,
                            $value['banSeconds'] ?? 300
                        );
                    }
                    break;

                case 'needsCSRF':
                    if ($value === true) {
                        CSRFService::validateToken();
                    }
                    break;
            }
        }

        if ($isStateChanging) {
            $hasExplicitRateLimit = array_key_exists('rateLimit', $options);
            if (!$hasExplicitRateLimit || $options['rateLimit'] !== false) {
                RateLimitService::verify($this->currentRouteName, 10, 60, 300);
            }

            $hasExplicitNeedsCsrf = array_key_exists('needsCSRF', $options);
            if (!$hasExplicitNeedsCsrf || $options['needsCSRF'] !== false) {
                CSRFService::validateToken();
            }
        }
    }

    private function runAction(mixed $action, array $params){
        if (is_callable($action)) {
            return call_user_func_array($action, $params);
        }

        if (is_string($action)) {
            [$controller, $method] = explode('@', $action);
            return (new $controller)->$method(...$params);
        }
    }

    private function setCurrentRouteName(string $routeName): void {
        $this->currentRouteName = $routeName;
    }

    private function getCurrentRouteName(): string {
        return $this->currentRouteName;
    }

    private function convertRouteToRegex(mixed $path){
        return "#^" . preg_replace('#\\{[^}]+}#', '([^/]+)', $path) . "$#";
    }

    private function cleanUri(mixed $uri){
        $path = parse_url($uri, PHP_URL_PATH);
        // Normalize trailing slash
        $path = rtrim($path, '/');
        return $path === '' ? '/' : $path;
    }
}
