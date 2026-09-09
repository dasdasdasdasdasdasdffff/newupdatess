<?php
/**
 * CapitalNest Nepal - User Authentication Guard Middleware
 */

declare(strict_types=1);

namespace App\Middleware;

use App\Services\AuthService;
use App\Helpers\Security;

class AuthMiddleware {
    public static function handle(): ?array {
        $user = AuthService::getCurrentUser();
        if (!$user) {
            if (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
                Security::jsonResponse(['error' => 'Authentication required.'], 401);
            }
            header('Location: /login');
            exit;
        }

        if ($user['status'] === 'suspended') {
            AuthService::logout();
            header('Location: /login?error=' . urlencode('Your account has been suspended by compliance.'));
            exit;
        }

        return $user;
    }
}
