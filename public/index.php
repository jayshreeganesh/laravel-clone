<?php
/**
 * Laravel Clone - Front Controller
 */

define('LARAVEL_START', microtime(true));
if (!defined('LARAVEL_ROOT')) {
    define('LARAVEL_ROOT', dirname(__DIR__));
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
