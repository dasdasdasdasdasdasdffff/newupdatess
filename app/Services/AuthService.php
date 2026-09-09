<?php
/**
 * CapitalNest Nepal - Enterprise Authentication Service
 * Secure Session Regeneration, Strict Password Hashing & Dual-Role Guards
 */

declare(strict_types=1);

namespace App\Services;

use Config\Database;
use App\Helpers\Security;
use PDO;
use Exception;

class AuthService {
    private PDO $db;
    private WalletService $walletService;

    public function __construct(?PDO $db = null) {
        $this->db = $db ?? Database::getConnection();
        $this->walletService = new WalletService($this->db);
    }

    /**
     * User Registration with automatic Wallet initialization and Referral binding
     */
    public function register(string $name, string $email, string $phone, string $password, ?string $referralCode = null): array {
        $cleanEmail = strtolower(trim($email));
        $cleanPhone = trim($phone);
        $cleanName = trim($name);
        $clientIp = $this->resolveClientIp();
        $deviceFingerprint = $this->generateDeviceFingerprint();

        if (!filter_var($cleanEmail, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Please provide a valid corporate or personal email address.");
        }
        if (strlen($password) < 8) {
            throw new Exception("Password must contain at least 8 characters.");
        }

        // Check uniqueness
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $cleanEmail]);
        if ($stmt->fetch()) {
            throw new Exception("An account is already registered with this email address.");
        }

        // Generate user referral code
        $userRefCode = ReferralService::generateUniqueCode();
        $verificationToken = $this->generateSecureToken();
        $passwordHash = Security::hashPassword($password);

        $this->db->beginTransaction();
        try {
            // 1. Insert User
            $expiresAt = date('Y-m-d H:i:s', time() + 86400);
            $uStmt = $this->db->prepare("
                INSERT INTO users (referral_code, referred_by, name, email, phone, password_hash, email_verified, email_verification_token, email_verification_expires_at, status, registration_ip, last_seen_ip, device_fingerprint, created_at)
                VALUES (:ref_code, :referred_by, :name, :email, :phone, :hash, 0, :verify_token, :expires_at, 'active', :registration_ip, :last_seen_ip, :device_fingerprint, CURRENT_TIMESTAMP)
            ");
            $uStmt->execute([
                ':ref_code' => $userRefCode,
                ':referred_by' => $referralCode ? strtoupper(trim($referralCode)) : null,
                ':name' => Security::sanitize($cleanName),
                ':email' => $cleanEmail,
                ':phone' => Security::sanitize($cleanPhone),
                ':hash' => $passwordHash,
                ':verify_token' => $verificationToken,
                ':expires_at' => $expiresAt,
                ':registration_ip' => $clientIp,
                ':last_seen_ip' => $clientIp,
                ':device_fingerprint' => $deviceFingerprint,
            ]);
            $userId = (int)$this->db->lastInsertId();

            // 2. Initialize Profile
            $pStmt = $this->db->prepare("INSERT INTO user_profiles (user_id, created_at) VALUES (:uid, CURRENT_TIMESTAMP)");
            $pStmt->execute([':uid' => $userId]);

            // 3. Initialize Wallet
            $wStmt = $this->db->prepare("
                INSERT INTO wallets (user_id, available_balance, invested_balance, total_earnings, total_deposits, total_withdrawals, currency, created_at)
                VALUES (:uid, '0.00', '0.00', '0.00', '0.00', '0.00', 'NPR', CURRENT_TIMESTAMP)
            ");
            $wStmt->execute([':uid' => $userId]);

            // 4. Bind Referral if code provided
            if (!empty($referralCode)) {
                $refService = new ReferralService($this->db);
                $refService->bindReferral($userId, $referralCode);
            }

            // 5. Welcome Notification
            $notif = $this->db->prepare("
                INSERT INTO notifications (user_id, title, message, type, is_read, created_at)
                VALUES (:uid, 'Welcome to CapitalNest Nepal', 'Your investment account and secure wallet have been created successfully.', 'system', 0, CURRENT_TIMESTAMP)
            ");
            $notif->execute([':uid' => $userId]);

            $this->db->commit();

            // Do not make registration wait for a slow email provider response.
            $this->queueVerificationEmail($cleanEmail, $cleanName, $verificationToken);

            return [
                'success' => true,
                'user_id' => $userId,
                'email' => $cleanEmail,
                'name' => $cleanName,
                'verification_token' => $verificationToken,
            ];
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * User Login with Session Fixation Protection (session_regenerate_id)
     */
    public function login(string $email, string $password): array {
        $cleanEmail = strtolower(trim($email));

        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $cleanEmail]);
        $user = $stmt->fetch();

        if (!$user || !Security::verifyPassword($password, $user['password_hash'])) {
            throw new Exception("Invalid email or password.");
        }

        if ((int)($user['email_verified'] ?? 0) !== 1) {
            throw new Exception("Please verify your email before signing in. Check your inbox for the verification link.");
        }

        if ($user['status'] === 'suspended') {
            throw new Exception("Your account has been suspended by compliance. Please contact support.");
        }

        if (!empty($user['blocked_reason'])) {
            throw new Exception("Access restricted due to suspicious account activity.");
        }

        // Session Regeneration to eliminate fixation attacks
        Security::startSecureSession();
        session_regenerate_id(true);

        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = 'user';
        $_SESSION['logged_in_at'] = time();

        // Update last login
        $ip = $this->resolveClientIp();
        $upd = $this->db->prepare("UPDATE users SET last_login_at = CURRENT_TIMESTAMP, last_login_ip = :ip, last_seen_ip = :ip, device_fingerprint = COALESCE(device_fingerprint, :device_fingerprint) WHERE id = :id");
        $upd->execute([
            ':ip' => $ip,
            ':device_fingerprint' => $this->generateDeviceFingerprint(),
            ':id' => $user['id']
        ]);

        return [
            'success' => true,
            'user' => [
                'id' => (int)$user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'referral_code' => $user['referral_code']
            ]
        ];
    }

    public function verifyEmail(string $token): array {
        $cleanToken = trim($token);
        if ($cleanToken === '') {
            throw new Exception("Invalid verification link.");
        }

        $stmt = $this->db->prepare("SELECT * FROM users WHERE email_verification_token = :token LIMIT 1");
        $stmt->execute([':token' => $cleanToken]);
        $user = $stmt->fetch();

        if (!$user) {
            throw new Exception("This verification link is invalid or has already been used.");
        }

        if (!empty($user['email_verification_expires_at']) && strtotime((string)$user['email_verification_expires_at']) < time()) {
            throw new Exception("This verification link has expired. Please register again to receive a new link.");
        }

        $this->db->beginTransaction();
        try {
            $upd = $this->db->prepare("UPDATE users SET email_verified = 1, email_verified_at = CURRENT_TIMESTAMP, email_verification_token = NULL, email_verification_expires_at = NULL WHERE id = :id AND email_verified = 0");
            $upd->execute([':id' => $user['id']]);

            (new ReferralService($this->db))->activateVerifiedReferral((int)$user['id']);
            $this->db->commit();
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }

        return [
            'success' => true,
            'user' => [
                'id' => (int)$user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
            ]
        ];
    }

    public function resendVerificationEmail(string $email): void {
        $cleanEmail = strtolower(trim($email));
        if (!filter_var($cleanEmail, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Please provide a valid email address.');
        }

        $user = $this->getUserByEmail($cleanEmail);
        if (!$user) {
            throw new Exception('No account was found for this email address.');
        }
        if ((int)($user['email_verified'] ?? 0) === 1) {
            throw new Exception('This email address is already verified. You can sign in.');
        }

        $token = $this->generateSecureToken();
        $expiresAt = date('Y-m-d H:i:s', time() + 86400);
        $stmt = $this->db->prepare("
            UPDATE users
            SET email_verification_token = :token, email_verification_expires_at = :expires
            WHERE id = :id AND email_verified = 0
        ");
        $stmt->execute([
            ':token' => $token,
            ':expires' => $expiresAt,
            ':id' => $user['id']
        ]);

        $this->sendVerificationEmail($cleanEmail, (string)$user['name'], $token);
    }

    public function requestPasswordReset(string $email): array {
        $cleanEmail = strtolower(trim($email));
        if (!filter_var($cleanEmail, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Please provide a valid email address.");
        }

        $user = $this->getUserByEmail($cleanEmail);
        if (!$user) {
            return ['success' => true, 'message' => 'If an account exists for this email, a reset link has been sent.'];
        }

        $token = $this->generateSecureToken();
        $expiresAt = date('Y-m-d H:i:s', time() + 3600);
        $upd = $this->db->prepare("UPDATE users SET password_reset_token = :token, password_reset_expires_at = :expires WHERE id = :id");
        $upd->execute([
            ':token' => $token,
            ':expires' => $expiresAt,
            ':id' => $user['id'],
        ]);

        $baseUrl = rtrim((string)(getenv('APP_URL') ?: ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? '127.0.0.1:8000')), '/');
        $resetLink = $baseUrl . '/reset-password?token=' . urlencode($token);
        $this->sendEmail(
            $cleanEmail,
            $user['name'],
            'Reset your CapitalNest Nepal password',
            "Hello {$user['name']},\n\n" .
            "We received a request to reset your CapitalNest Nepal password.\n\n" .
            "Click the secure link below to create a new password:\n\n" .
            $resetLink . "\n\n" .
            "This link expires in 1 hour. If you did not request this, you can safely ignore this email.\n\n" .
            "Regards,\nCapitalNest Nepal",
            $resetLink,
            [
                'heading' => 'Reset your password',
                'subtitle' => 'We received a request to create a new password for your account.',
                'ctaText' => 'Reset Password',
                'primaryText' => 'This secure link is unique to your account and expires in 1 hour.',
            ]
        );

        return ['success' => true, 'message' => 'If an account exists for this email, a reset link has been sent.', 'reset_link' => $resetLink];
    }

    public function resetPassword(string $token, string $password, string $confirmPassword): array {
        $cleanToken = trim($token);
        if ($cleanToken === '') {
            throw new Exception("Invalid password reset link.");
        }
        if (strlen($password) < 8) {
            throw new Exception("Password must contain at least 8 characters.");
        }
        if ($password !== $confirmPassword) {
            throw new Exception("Password confirmation does not match.");
        }

        $stmt = $this->db->prepare("SELECT * FROM users WHERE password_reset_token = :token LIMIT 1");
        $stmt->execute([':token' => $cleanToken]);
        $user = $stmt->fetch();
        if (!$user) {
            throw new Exception("This password reset link is invalid or has already been used.");
        }

        if (!empty($user['password_reset_expires_at']) && strtotime((string)$user['password_reset_expires_at']) < time()) {
            throw new Exception("This password reset link has expired. Please request a new one.");
        }

        $hash = Security::hashPassword($password);
        $upd = $this->db->prepare("UPDATE users SET password_hash = :hash, password_reset_token = NULL, password_reset_expires_at = NULL WHERE id = :id");
        $upd->execute([':hash' => $hash, ':id' => $user['id']]);

        return ['success' => true, 'message' => 'Your password has been reset successfully.'];
    }

    public function changeUserPassword(int $userId, string $currentPassword, string $newPassword, string $confirmPassword): array {
        if (strlen($newPassword) < 8) {
            throw new Exception("New password must contain at least 8 characters.");
        }
        if ($newPassword !== $confirmPassword) {
            throw new Exception("New password confirmation does not match.");
        }

        $userStmt = $this->db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $userStmt->execute([':id' => $userId]);
        $user = $userStmt->fetch();
        if (!$user) {
            throw new Exception("User not found.");
        }

        if (!empty($currentPassword) && !Security::verifyPassword($currentPassword, $user['password_hash'])) {
            throw new Exception("Current password is incorrect.");
        }

        $this->db->prepare("UPDATE users SET password_hash = :hash WHERE id = :id")
            ->execute([':hash' => Security::hashPassword($newPassword), ':id' => $userId]);

        return ['success' => true, 'message' => 'User password updated successfully.'];
    }

    public function changeAdminPassword(int $adminId, string $currentPassword, string $newPassword, string $confirmPassword): array {
        if (strlen($newPassword) < 8) {
            throw new Exception("New password must contain at least 8 characters.");
        }
        if ($newPassword !== $confirmPassword) {
            throw new Exception("New password confirmation does not match.");
        }

        $adminStmt = $this->db->prepare("SELECT * FROM admin_users WHERE id = :id LIMIT 1");
        $adminStmt->execute([':id' => $adminId]);
        $admin = $adminStmt->fetch();
        if (!$admin) {
            throw new Exception("Admin account not found.");
        }

        if (!Security::verifyPassword($currentPassword, $admin['password_hash'])) {
            throw new Exception("Current admin password is incorrect.");
        }

        $this->db->prepare("UPDATE admin_users SET password_hash = :hash, updated_at = CURRENT_TIMESTAMP WHERE id = :id")
            ->execute([':hash' => Security::hashPassword($newPassword), ':id' => $adminId]);

        return ['success' => true, 'message' => 'Admin password updated successfully.'];
    }

    private function sendVerificationEmail(string $email, string $name, string $token, int $maxAttempts = 3): void {
        $baseUrl = rtrim((string)(getenv('APP_URL') ?: ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? '127.0.0.1:8000')), '/');
        $verificationLink = $baseUrl . '/verify-email?token=' . urlencode($token);
        $this->sendEmail(
            $email,
            $name,
            'Verify your CapitalNest Nepal account',
            "Hello {$name},\n\n" .
            "Thank you for creating your CapitalNest Nepal account. Please verify your email address by clicking the secure verification link below:\n\n" .
            $verificationLink . "\n\n" .
            "If you did not create this account, you can safely ignore this email.\n\n" .
            "Regards,\nCapitalNest Nepal",
            $verificationLink,
            [
                'heading' => 'Verify your email',
                'subtitle' => 'Welcome to CapitalNest Nepal. Please confirm your email address to activate your account.',
                'ctaText' => 'Verify Email',
                'primaryText' => 'This verification link is unique to your account and expires in 24 hours.',
            ],
            $maxAttempts
        );
    }

    private function queueVerificationEmail(string $email, string $name, string $token): void {
        $send = function () use ($email, $name, $token): void {
            try {
                $this->sendVerificationEmail($email, $name, $token, 1);
            } catch (\Throwable $mailError) {
                error_log('Verification email dispatch failed after registration: ' . $mailError->getMessage());
            }
        };

        if (function_exists('fastcgi_finish_request')) {
            register_shutdown_function($send);
            return;
        }

        // The PHP built-in server has no response-finalization hook.
        register_shutdown_function($send);
    }

    private function generateSecureToken(): string {
        return bin2hex(random_bytes(32));
    }

    private function resolveClientIp(): string {
        $sources = [
            $_SERVER['HTTP_CF_CONNECTING_IP'] ?? null,
            $_SERVER['HTTP_TRUE_CLIENT_IP'] ?? null,
            $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null,
            $_SERVER['HTTP_X_REAL_IP'] ?? null,
            $_SERVER['REMOTE_ADDR'] ?? null,
        ];

        foreach ($sources as $source) {
            if (empty($source)) {
                continue;
            }

            foreach (explode(',', (string)$source) as $candidate) {
                $ip = trim($candidate);
                if ($this->isValidIp($ip)) {
                    return $ip;
                }
            }
        }

        return '127.0.0.1';
    }

    private function isValidIp(string $ip): bool {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    private function generateDeviceFingerprint(): string {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'unknown';
        $accept = $_SERVER['HTTP_ACCEPT'] ?? 'unknown';
        $ip = $this->resolveClientIp();
        $seed = $ip . '|' . $ua . '|' . $lang . '|' . $accept;
        return hash('sha256', $seed);
    }

    private function sendEmail(string $toEmail, string $toName, string $subject, string $body, ?string $fallbackLink = null, array $emailTemplate = [], int $maxAttempts = 3): void {
        $apiKey = trim((string)(getenv('RESEND_API_KEY') ?: ''), " \t\n\r\0\x0B\"'");
        $from = trim((string)(getenv('RESEND_FROM') ?: 'CapitalNest Nepal <onboarding@resend.dev>'), " \t\n\r\0\x0B\"'");
        if ($apiKey === '') {
            throw new Exception('Resend is not configured. Set RESEND_API_KEY.');
        }

        $html = $this->buildHtmlEmailTemplate($toName, $subject, $emailTemplate['heading'] ?? 'Action required', $emailTemplate['subtitle'] ?? '', $emailTemplate['primaryText'] ?? $body, $fallbackLink ?? '', $emailTemplate['ctaText'] ?? 'Continue');
        $lastError = null;
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $curl = curl_init('https://api.resend.com/emails');
            if ($curl === false) {
                throw new Exception('Unable to initialize Resend HTTP client.');
            }
            curl_setopt_array($curl, [
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $apiKey,
                    'Content-Type: application/json',
                ],
                CURLOPT_POSTFIELDS => json_encode([
                    'from' => $from,
                    'to' => [$toEmail],
                    'subject' => $subject,
                    'text' => $body,
                    'html' => $html,
                ], JSON_THROW_ON_ERROR),
            ]);
            $response = curl_exec($curl);
            $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $error = curl_error($curl);
            curl_close($curl);

            if ($response !== false && $status >= 200 && $status < 300) {
                error_log('Verification email accepted by Resend for recipient domain: ' . (str_contains($toEmail, '@') ? substr(strrchr($toEmail, '@'), 1) : 'unknown'));
                return;
            }

            $lastError = new Exception('Resend request failed (HTTP ' . $status . '): ' . ($error !== '' ? $error : (string)$response));
            if ($attempt < $maxAttempts) {
                usleep(500000);
            }
        }

        throw new Exception('Resend delivery failed after ' . $maxAttempts . ' attempt(s): ' . ($lastError?->getMessage() ?? 'unknown error'));
    }

    private function buildHtmlEmailTemplate(string $toName, string $subject, string $heading, string $subtitle, string $primaryText, string $ctaLink, string $ctaText): string {
        $safeSubject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
        $safeHeading = htmlspecialchars($heading, ENT_QUOTES, 'UTF-8');
        $safeSubtitle = htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8');
        $safePrimary = nl2br(htmlspecialchars($primaryText, ENT_QUOTES, 'UTF-8'));
        $safeName = htmlspecialchars($toName, ENT_QUOTES, 'UTF-8');
        $safeLink = htmlspecialchars($ctaLink, ENT_QUOTES, 'UTF-8');
        $safeCta = htmlspecialchars($ctaText, ENT_QUOTES, 'UTF-8');

        return "<div style=\"background:#f5f5f3;padding:32px 16px;font-family:Arial,Helvetica,sans-serif;color:#111827;\">
            <div style=\"max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #e5e7eb;border-radius:18px;overflow:hidden;\">
                <div style=\"background:linear-gradient(135deg,#111827,#1f2937);padding:24px 32px;\">
                    <div style=\"text-align:center; margin-bottom:12px;\">
                        <div style=\"width:56px;height:56px;border-radius:50%;background:#111827;color:#ffffff;display:inline-flex;align-items:center;justify-content:center;font-size:18px;font-weight:700;letter-spacing:1px;\">CN</div>
                    </div>
                    <div style=\"font-size:12px;letter-spacing:2px;color:#d4af37;text-transform:uppercase;font-weight:700; text-align:center;\">CapitalNest Nepal</div>
                    <div style=\"height:12px;\"></div>
                    <div style=\"font-size:28px;font-weight:700;color:#ffffff; text-align:center;\">{$safeHeading}</div>
                </div>
                <div style=\"padding:32px;\">
                    <div style=\"font-size:18px;font-weight:600;color:#111827;\">Hello {$safeName},</div>
                    <div style=\"height:12px;\"></div>
                    <p style=\"margin:0 0 16px;font-size:15px;line-height:1.7;color:#4b5563;\">{$safeSubtitle}</p>
                    <p style=\"margin:0 0 24px;font-size:15px;line-height:1.7;color:#374151;\">{$safePrimary}</p>
                    <div style=\"text-align:center;margin:24px 0;\">
                        <a href=\"{$safeLink}\" style=\"display:inline-block;background:#d4af37;color:#111827;text-decoration:none;padding:14px 26px;border-radius:10px;font-weight:700;font-size:15px;\">{$safeCta}</a>
                    </div>
                </div>
                <div style=\"border-top:1px solid #e5e7eb;padding:18px 32px 26px;font-size:12px;color:#6b7280;\">
                    &copy; 2026 CapitalNest Nepal. Secure account access and investment services.
                </div>
            </div>
        </div>";
    }

    /**
     * Admin Login (Completely segregated from user sessions)
     */
    public function adminLogin(string $email, string $password): array {
        $cleanEmail = strtolower(trim($email));

        $stmt = $this->db->prepare("SELECT * FROM admin_users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $cleanEmail]);
        $admin = $stmt->fetch();

        if (!$admin || !Security::verifyPassword($password, $admin['password_hash'])) {
            throw new Exception("Invalid administrative credentials.");
        }

        if ($admin['status'] !== 'active') {
            throw new Exception("This administrative account is disabled.");
        }

        Security::startSecureSession();
        session_regenerate_id(true);

        $_SESSION['admin_id'] = (int)$admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['admin_logged_in_at'] = time();

        // Update login
        $upd = $this->db->prepare("UPDATE admin_users SET last_login_at = CURRENT_TIMESTAMP, last_login_ip = :ip WHERE id = :id");
        $upd->execute([
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            ':id' => $admin['id']
        ]);

        return [
            'success' => true,
            'admin' => [
                'id' => (int)$admin['id'],
                'name' => $admin['name'],
                'email' => $admin['email'],
                'role' => $admin['role']
            ]
        ];
    }

    public function getUserByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => strtolower(trim($email))]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function getCurrentUser(): ?array {
        Security::startSecureSession();
        if (empty($_SESSION['user_id'])) return null;

        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT u.*, w.available_balance, w.invested_balance, w.total_earnings, w.total_deposits, w.total_withdrawals
            FROM users u
            LEFT JOIN wallets w ON u.id = w.user_id
            WHERE u.id = :id LIMIT 1
        ");
        $stmt->execute([':id' => $_SESSION['user_id']]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function getCurrentAdmin(): ?array {
        Security::startSecureSession();
        if (empty($_SESSION['admin_id'])) return null;

        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM admin_users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $_SESSION['admin_id']]);
        $admin = $stmt->fetch();
        return $admin ?: null;
    }

    public static function logout(): void {
        Security::startSecureSession();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }
}
