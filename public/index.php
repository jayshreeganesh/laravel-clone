<?php
/**
 * Laravel Clone - Front Controller
 */

if (session_status() === PHP_SESSION_NONE) { session_start(); }
define('LARAVEL_START', microtime(true));
if (!defined('LARAVEL_ROOT')) {
    define('LARAVEL_ROOT', dirname(__DIR__));
}

// Auto-detect if app needs installation
$configFile = LARAVEL_ROOT . '/config/database.php';
$needsInstall = false;

if (!file_exists($configFile)) {
    $needsInstall = true;
} else {
    $cfg = require $configFile;
    if (empty($cfg['connections'])) {
        $needsInstall = true;
    } else {
        // Quick check: can we connect and do tables exist?
        try {
            $default = $cfg['default'] ?? 'sqlite';
            $db = $cfg['connections'][$default];
            if ($default === 'sqlite') {
                $dbPath = $db['database'] ?? '';
                if (!file_exists($dbPath)) { $needsInstall = true; }
                else {
                    $pdo = new PDO('sqlite:' . $dbPath);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $check = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
                    if ($check->fetchColumn() === false) { $needsInstall = true; }
                }
            } else {
                $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['database']};charset=utf8mb4";
                $pdo = new PDO($dsn, $db['username'], $db['password']);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $check = $pdo->query("SHOW TABLES LIKE 'users'");
                if ($check->rowCount() === 0) { $needsInstall = true; }
            }
        } catch (Exception $e) {
            $needsInstall = true;
        }
    }
}

if ($needsInstall) {
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if (strpos($uri, 'install.php') === false) {
        header('Location: /install.php');
        exit;
    }
}

// Autoload App classes
spl_autoload_register(function ($class) {
    if (str_starts_with($class, 'App\\')) {
        $path = str_replace('\\', '/', substr($class, 4));
        // Check core directory or app directory
        if (str_starts_with($path, 'Core/')) {
            $file = LARAVEL_ROOT . '/core/' . substr($path, 5) . '.php';
        } else {
            $file = LARAVEL_ROOT . '/app/' . $path . '.php';
        }
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

// Load Core Engine
require_once LARAVEL_ROOT . '/core/Kernel.php';

// Load Web Routes
require_once LARAVEL_ROOT . '/routes/web.php';

// Dispatch Request
App\Core\Route::dispatch();

