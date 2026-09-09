<?php
/**
 * CapitalNest Nepal - Admin Authentication & Authorization Guard Middleware
 */

declare(strict_types=1);

namespace App\Middleware;

use App\Services\AuthService;
use App\Helpers\Security;

class AdminMiddleware {
    public static function handle(?string $requiredRole = null): ?array {
        $admin = AuthService::getCurrentAdmin();
        if (!$admin) {
            if (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
                Security::jsonResponse(['error' => 'Administrative authorization required.'], 403);
            }
            header('Location: /admin/login');
            exit;
        }

        if ($requiredRole && $admin['role'] !== 'super_admin' && $admin['role'] !== $requiredRole) {
            if (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
                Security::jsonResponse(['error' => 'Insufficient role permissions for this operational sector.'], 403);
            }
            header('Location: /admin/dashboard?error=' . urlencode('Access restricted by policy.'));
            exit;
        }

        return $admin;
    }
}
