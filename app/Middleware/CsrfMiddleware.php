<?php
/**
 * CapitalNest Nepal - CSRF Protection Middleware
 */

declare(strict_types=1);

namespace App\Middleware;

use App\Helpers\Security;

class CsrfMiddleware {
    public static function handle(): void {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $token = $_POST['_csrf_token']
                ?? $_POST['csrf_token']
                ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
            if (!Security::validateCsrfToken($token)) {
                if (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
                    Security::jsonResponse(['error' => 'Security validation token mismatch. Please refresh and try again.'], 403);
                }
                http_response_code(403);
                die('CSRF Security Validation Failed. Please return to the previous page and refresh.');
            }
        }
    }
}
