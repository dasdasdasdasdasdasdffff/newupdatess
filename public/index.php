<?php
/**
 * CapitalNest Nepal - Master Front Controller
 * PHP 8.2+ Enterprise FinTech Core
 */

declare(strict_types=1);

// Report all errors in dev/debug mode, suppress fatal crashes
error_reporting(E_ALL);
ini_set('display_errors', '0');

// Base directories
define('ROOT_DIR', dirname(__DIR__));
define('APP_DIR', ROOT_DIR . '/app');
define('CONFIG_DIR', ROOT_DIR . '/config');
define('STORAGE_DIR', ROOT_DIR . '/storage');

// Load environment variables from the project .env file if present.
$envFile = ROOT_DIR . '/.env';
if (is_file($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines !== false) {
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }

            $separatorPos = strpos($trimmed, '=');
            if ($separatorPos === false) {
                continue;
            }

            $key = trim(substr($trimmed, 0, $separatorPos));
            $value = trim(substr($trimmed, $separatorPos + 1));

            if ((strlen($value) >= 2) && (($value[0] === '"' && $value[strlen($value) - 1] === '"') || ($value[0] === "'" && $value[strlen($value) - 1] === "'"))) {
                $value = substr($value, 1, -1);
            }

            $value = str_replace(['\\n', '\\r', '\\t'], ["\n", "\r", "\t"], $value);

            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

// Robust Autoloader
spl_autoload_register(function ($class) {
    if (str_starts_with($class, 'App\\')) {
        $rel = str_replace('\\', '/', substr($class, 4));
        $candidates = [
            APP_DIR . '/' . $rel . '.php',
            APP_DIR . '/' . lcfirst($rel) . '.php',
            APP_DIR . '/' . strtolower($rel) . '.php',
        ];
        foreach ($candidates as $file) {
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    } elseif (str_starts_with($class, 'Config\\')) {
        $rel = str_replace('\\', '/', substr($class, 7));
        $candidates = [
            CONFIG_DIR . '/' . $rel . '.php',
            CONFIG_DIR . '/' . strtolower($rel) . '.php',
            CONFIG_DIR . '/' . lcfirst($rel) . '.php',
        ];
        foreach ($candidates as $file) {
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
});

use App\Helpers\Security;
use App\Middleware\CsrfMiddleware;
use App\Services\AuthService;

// Initialize secure session
Security::startSecureSession();

$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$requestMethod = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

// Route API requests if URI starts with /api/
if (str_starts_with($requestUri, '/api/')) {
    require_once ROOT_DIR . '/routes/api.php';
    exit;
}

// Protected file access route for KYC and proofs (Authenticated access only!)
if ($requestUri === '/secure-file') {
    $path = $_GET['path'] ?? '';
    // Must be logged in as admin or owner
    $admin = AuthService::getCurrentAdmin();
    $user = AuthService::getCurrentUser();

    if (!$admin && !$user) {
        http_response_code(403);
        die('Forbidden. Authentication required.');
    }

    $sanitized = basename(dirname($path)) . '/' . basename($path);
    $fullPath = STORAGE_DIR . '/uploads/' . $sanitized;

    if (!file_exists($fullPath) || !is_file($fullPath)) {
        http_response_code(404);
        die('Document not found.');
    }

    $mime = mime_content_type($fullPath) ?: 'application/octet-stream';
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($fullPath));
    header('Cache-Control: private, max-age=3600');
    readfile($fullPath);
    exit;
}

// CSRF Verification for state-mutating requests
CsrfMiddleware::handle();

// Load Web Routes
$routes = require_once ROOT_DIR . '/routes/web.php';
$routeKey = "{$requestMethod} {$requestUri}";

try {
    if (isset($routes[$routeKey])) {
        $handler = $routes[$routeKey];
        if (is_callable($handler)) {
            $handler();
        } elseif (is_array($handler)) {
            [$controllerClass, $method] = $handler;
            $controller = new $controllerClass();
            $controller->$method();
        }
        exit;
    }

    // 404 Not Found
    http_response_code(404);
    echo '<!DOCTYPE html><html><head><title>404 Not Found</title><script src="https://cdn.tailwindcss.com"></script></head><body class="bg-[#F8F9FA] flex items-center justify-center min-h-screen text-center"><div class="p-8 bg-white border border-[#E5E7EB] rounded-2xl shadow-sm max-w-md"><h1 class="text-4xl font-bold text-[#111111] mb-2">404</h1><p class="text-[#6B7280] mb-6">The requested financial endpoint was not found.</p><a href="/dashboard" class="px-5 py-2.5 bg-[#111111] text-white rounded-xl font-medium text-sm">Return to Dashboard</a></div></body></html>';
} catch (Throwable $e) {
    error_log("Critical Application Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    http_response_code(500);
    echo '<!DOCTYPE html><html><head><title>System Incident</title><script src="https://cdn.tailwindcss.com"></script></head><body class="bg-[#F8F9FA] flex items-center justify-center min-h-screen text-center"><div class="p-8 bg-white border border-[#E5E7EB] rounded-2xl shadow-sm max-w-md"><h2 class="text-xl font-semibold text-[#111111] mb-2">Service Incident</h2><p class="text-sm text-[#6B7280] mb-6">' . htmlspecialchars($e->getMessage()) . '</p><a href="/" class="px-5 py-2.5 bg-[#111111] text-white rounded-xl font-medium text-sm">Return Home</a></div></body></html>';
}
