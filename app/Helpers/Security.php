<?php
/**
 * CapitalNest Nepal - Enterprise Security Helper
 */

declare(strict_types=1);

namespace App\Helpers;

class Security {
    public static function startSecureSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.use_only_cookies', '1');
            ini_set('session.use_strict_mode', '1');
            $cookieParams = session_get_cookie_params();
            $isSecureRequest = (
                (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ||
                (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') ||
                (($_SERVER['HTTP_X_FORWARDED_SSL'] ?? '') === 'on') ||
                (($_SERVER['HTTP_CF_VISITOR'] ?? '') !== '' && str_contains((string)$_SERVER['HTTP_CF_VISITOR'], 'https')) ||
                (($_SERVER['SERVER_PORT'] ?? '') === '443')
            );

            session_set_cookie_params([
                'lifetime' => 86400 * 7,
                'path' => '/',
                'domain' => '',
                'secure' => $isSecureRequest,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();
        }
    }

    public static function generateCsrfToken(): string {
        self::startSecureSession();
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    public static function validateCsrfToken(?string $token): bool {
        self::startSecureSession();
        if (empty($_SESSION['_csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['_csrf_token'], $token);
    }

    public static function csrfInput(): string {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function sanitize(string $input): string {
        return trim(strip_tags($input));
    }

    public static function escape(?string $input): string {
        return htmlspecialchars((string)$input, ENT_QUOTES, 'UTF-8');
    }

    public static function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public static function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }

    public static function generateRef(string $prefix = 'TXN'): string {
        return sprintf('%s-%s-%s', $prefix, date('Ymd'), strtoupper(bin2hex(random_bytes(4))));
    }

    public static function jsonResponse(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }
}
