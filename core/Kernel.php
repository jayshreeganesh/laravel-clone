<?php
/**
 * Laravel Clone Core Engine
 * Ultra-low Inode Laravel Architecture
 */

namespace App\Core {

use PDO;
use Exception;

if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

class Database {
    private static ?PDO $pdo = null;

    public static function connect(): PDO {
        if (self::$pdo === null) {
            $config = require LARAVEL_ROOT . '/config/database.php';
            $default = $config['default'] ?? 'sqlite';
            $db = $config['connections'][$default];

            if ($default === 'sqlite') {
                $dbPath = $db['database'];
                if (!is_dir(dirname($dbPath))) mkdir(dirname($dbPath), 0777, true);
                self::$pdo = new PDO('sqlite:' . $dbPath);
            } else {
                $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['database']};charset=utf8mb4";
                self::$pdo = new PDO($dsn, $db['username'], $db['password']);
            }
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
            self::autoMigrate();
        }
        return self::$pdo;
    }

    private static function autoMigrate() {
        self::$pdo->exec("CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY " . (self::$pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite' ? 'AUTOINCREMENT' : 'AUTO_INCREMENT') . ",
            name VARCHAR(255) NOT NULL,
            sku VARCHAR(100) NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            stock INT NOT NULL DEFAULT 0,
            description TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
    }
}

class Request {
    public function all(): array {
        return array_merge($_GET, $_POST);
    }

    public function input(?string $key = null, $default = null) {
        $all = $this->all();
        if ($key === null) return $all;
        return isset($all[$key]) ? (is_string($all[$key]) ? trim($all[$key]) : $all[$key]) : $default;
    }

    public function post(?string $key = null, $default = null) {
        if ($key === null) return $_POST;
        return isset($_POST[$key]) ? (is_string($_POST[$key]) ? trim($_POST[$key]) : $_POST[$key]) : $default;
    }

    public function validate(array $rules): array {
        $errors = [];
        $data = [];
        foreach ($rules as $field => $ruleStr) {
            $value = $this->input($field);
            $ruleList = is_array($ruleStr) ? $ruleStr : explode('|', $ruleStr);
            foreach ($ruleList as $rule) {
                if ($rule === 'required' && ($value === null || $value === '')) {
                    $errors[$field] = ucfirst($field) . ' is required.';
                }
                if ($rule === 'numeric' && !is_numeric($value) && $value !== null && $value !== '') {
                    $errors[$field] = ucfirst($field) . ' must be a valid number.';
                }
            }
            $data[$field] = $value;
        }

        if (!empty($errors)) {
            $_SESSION['__laravel_errors'] = $errors;
            $_SESSION['__laravel_old'] = $this->all();
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
            exit;
        }
        return $data;
    }
}

class Model {
    protected string $table;
    protected array $fillable = [];
    public array $attributes = [];
    protected static ?string $resolvedTable = null;

    public function __construct(array $attributes = []) {
        $this->attributes = $attributes;
        if (empty($this->table)) {
            $class = (new \ReflectionClass($this))->getShortName();
            $this->table = strtolower($class) . 's';
        }
    }

    public function __get($key) {
        return $this->attributes[$key] ?? null;
    }

    public function __set($key, $value) {
        $this->attributes[$key] = $value;
    }

    public function __isset($key) {
        return isset($this->attributes[$key]);
    }

    public static function query(): static {
        return new static();
    }

    public static function all(): array {
        $instance = new static();
        $pdo = Database::connect();
        $stmt = $pdo->query("SELECT * FROM {$instance->table} ORDER BY id DESC");
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = new static($row);
        }
        return $results;
    }

    public static function find(int $id): ?static {
        $instance = new static();
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT * FROM {$instance->table} WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new static($row) : null;
    }

    public static function findOrFail(int $id): static {
        $item = static::find($id);
        if (!$item) {
            http_response_code(404);
            throw new Exception("Model not found [id: {$id}]");
        }
        return $item;
    }

    public static function create(array $data): static {
        $instance = new static();
        $filtered = array_intersect_key($data, array_flip($instance->fillable));
        $pdo = Database::connect();
        $cols = implode(', ', array_keys($filtered));
        $placeholders = implode(', ', array_fill(0, count($filtered), '?'));
        $stmt = $pdo->prepare("INSERT INTO {$instance->table} ({$cols}) VALUES ({$placeholders})");
        $stmt->execute(array_values($filtered));
        $filtered['id'] = (int)$pdo->lastInsertId();
        $instance->attributes = $filtered;
        return $instance;
    }

    public function update(array $data): bool {
        $filtered = array_intersect_key($data, array_flip($this->fillable));
        if (empty($filtered) || empty($this->attributes['id'])) return false;
        $pdo = Database::connect();
        $set = implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($filtered)));
        $stmt = $pdo->prepare("UPDATE {$this->table} SET {$set} WHERE id = ?");
        $params = array_merge(array_values($filtered), [$this->attributes['id']]);
        $success = $stmt->execute($params);
        if ($success) {
            $this->attributes = array_merge($this->attributes, $filtered);
        }
        return $success;
    }

    public function delete(): bool {
        if (empty($this->attributes['id'])) return false;
        $pdo = Database::connect();
        $stmt = $pdo->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$this->attributes['id']]);
    }
}

class RedirectResponse {
    protected string $url;
    public function __construct(string $url) {
        $this->url = $url;
    }
    public function with(string $key, $value): self {
        $_SESSION['__laravel_flash'][$key] = $value;
        return $this;
    }
    public function send() {
        header('Location: ' . $this->url);
        exit;
    }
    public function __destruct() {
        $this->send();
    }
}

class Route {
    private static array $routes = [];
    private static ?array $lastRoute = null;

    public static function get(string $uri, $action): self {
        return self::add('GET', $uri, $action);
    }
    public static function post(string $uri, $action): self {
        return self::add('POST', $uri, $action);
    }
    public static function delete(string $uri, $action): self {
        return self::add('POST', $uri, $action);
    }

    private static function add(string $method, string $uri, $action): self {
        $route = [
            'method' => $method,
            'uri'    => '/' . trim($uri, '/'),
            'action' => $action,
            'name'   => null,
        ];
        self::$routes[] = $route;
        self::$lastRoute = &self::$routes[count(self::$routes) - 1];
        return new self();
    }

    public function name(string $name): self {
        if (self::$lastRoute) {
            self::$lastRoute['name'] = $name;
        }
        return $this;
    }

    public static function getRoutes(): array {
        return self::$routes;
    }

    public static function dispatch() {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        if ($scriptDir !== '/' && $scriptDir !== '\\' && str_starts_with($uri, $scriptDir)) {
            $uri = substr($uri, strlen($scriptDir));
        }
        $path = '/' . trim($uri, '/');
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Check for _method override (PUT / DELETE in forms)
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        foreach (self::$routes as $route) {
            if ($route['method'] !== $method && $route['method'] !== 'ANY') continue;
            
            // Match pattern with params like /products/{id}/edit
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route['uri']);
            if (preg_match('#^' . $pattern . '$#', $path, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $action = $route['action'];

                if (is_array($action)) {
                    [$controllerClass, $methodName] = $action;
                    $controller = new $controllerClass();
                    $request = new Request();
                    $reflection = new \ReflectionMethod($controllerClass, $methodName);
                    $args = [];
                    foreach ($reflection->getParameters() as $param) {
                        $paramType = $param->getType();
                        if ($paramType && $paramType->getName() === Request::class) {
                            $args[] = $request;
                        } else {
                            $name = $param->getName();
                            $args[] = $params[$name] ?? array_shift($params);
                        }
                    }
                    $response = $reflection->invokeArgs($controller, $args);
                    if (is_string($response)) {
                        echo $response;
                    }
                    return;
                } elseif (is_callable($action)) {
                    $response = call_user_func_array($action, $params);
                    if (is_string($response)) echo $response;
                    return;
                }
            }
        }

        http_response_code(404);
        echo "<h1>404 | Not Found</h1><p>The route {$path} could not be found.</p>";
    }
}
}

namespace {
    // Global Laravel Helper Functions
    if (!function_exists('view')) {
        function view(string $view, array $data = []): string {
            $viewPath = LARAVEL_ROOT . '/resources/views/' . str_replace('.', '/', $view) . '.php';
            if (!file_exists($viewPath)) {
                throw new Exception("View [{$view}] not found at {$viewPath}");
            }
            extract($data);
            
            // Clear flash data after reading
            $errors = $_SESSION['__laravel_errors'] ?? [];
            $flash_success = $_SESSION['__laravel_flash']['success'] ?? null;
            $flash_error = $_SESSION['__laravel_flash']['error'] ?? null;
            unset($_SESSION['__laravel_errors'], $_SESSION['__laravel_flash']);

            ob_start();
            include $viewPath;
            $slot = ob_get_clean();

            $layoutPath = LARAVEL_ROOT . '/resources/views/layouts/app.php';
            if (file_exists($layoutPath)) {
                ob_start();
                include $layoutPath;
                return ob_get_clean();
            }
            return $slot;
        }
    }

    if (!function_exists('redirect')) {
        function redirect(string $url): App\Core\RedirectResponse {
            $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
            $base = ($scriptDir === '/' || $scriptDir === '\\') ? '' : rtrim($scriptDir, '/');
            $fullUrl = (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) ? $url : ($base . '/' . ltrim($url, '/'));
            return new App\Core\RedirectResponse($fullUrl);
        }
    }

    if (!function_exists('url')) {
        function url(string $path = ''): string {
            $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
            $base = ($scriptDir === '/' || $scriptDir === '\\') ? '' : rtrim($scriptDir, '/');
            return $base . '/' . ltrim($path, '/');
        }
    }

    if (!function_exists('old')) {
        function old(string $key, $default = '') {
            $val = $_SESSION['__laravel_old'][$key] ?? $default;
            return htmlspecialchars((string)$val);
        }
    }

    if (!function_exists('csrf_field')) {
        function csrf_field(): string {
            if (empty($_SESSION['__token'])) {
                $_SESSION['__token'] = bin2hex(random_bytes(16));
            }
            return '<input type="hidden" name="_token" value="' . $_SESSION['__token'] . '">';
        }
    }
}
