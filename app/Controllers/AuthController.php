<?php
/**
 * CapitalNest Nepal - Authentication Controller
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;
use App\Helpers\Security;
use Exception;

class AuthController {
    private AuthService $authService;

    public function __construct() {
        $this->authService = new AuthService();
    }

    public function showLogin(): void {
        if (AuthService::getCurrentUser()) {
            header('Location: /dashboard');
            exit;
        }
        $csrf = Security::generateCsrfToken();
        $error = $_GET['error'] ?? null;
        $success = $_GET['success'] ?? null;
        require dirname(__DIR__) . '/Views/auth/login.php';
    }

    public function processLogin(): void {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $isAjax = isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json');

        try {
            $result = $this->authService->login($email, $password);
            if ($isAjax) {
                Security::jsonResponse(['success' => true, 'redirect' => '/dashboard', 'user' => $result['user']]);
            }
            header('Location: /dashboard');
            exit;
        } catch (Exception $e) {
            if ($isAjax) {
                Security::jsonResponse([
                    'success' => false,
                    'error' => $e->getMessage(),
                    'verification_required' => $e->getCode() === 1001,
                ], $e->getCode() === 1001 ? 403 : 400);
            }
            if ($e->getCode() === 1001) {
                header('Location: /register/pending?email=' . urlencode((string)$email) . '&error=' . urlencode($e->getMessage()));
                exit;
            }
            header('Location: /login?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    public function showRegister(): void {
        if (AuthService::getCurrentUser()) {
            header('Location: /dashboard');
            exit;
        }
        // Set the device cookie before the registration form is submitted.
        $this->authService->prepareRegistrationDevice();
        $csrf = Security::generateCsrfToken();
        $ref = $_GET['ref'] ?? '';
        $error = $_GET['error'] ?? null;
        require dirname(__DIR__) . '/Views/auth/register.php';
    }

    public function processRegister(): void {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $password = $_POST['password'] ?? '';
        $referralCode = $_POST['referral_code'] ?? null;
        $isAjax = isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json');

        try {
            $result = $this->authService->register($name, $email, $phone, $password, $referralCode);

            if ($isAjax) {
                Security::jsonResponse(['success' => true, 'redirect' => '/register/pending?email=' . urlencode((string)$result['email'])]);
            }
            header('Location: /register/pending?email=' . urlencode((string)$result['email']));
            exit;
        } catch (Exception $e) {
            if ($isAjax) {
                Security::jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
            }
            header('Location: /register?error=' . urlencode($e->getMessage()) . '&ref=' . urlencode((string)$referralCode));
            exit;
        }
    }

    public function showPendingVerification(): void {
        $email = $_GET['email'] ?? '';
        $success = $_GET['success'] ?? null;
        $error = $_GET['error'] ?? null;
        require dirname(__DIR__) . '/Views/auth/verification_pending.php';
    }

    public function resendVerification(): void {
        $email = $_GET['email'] ?? '';
        try {
            $this->authService->resendVerificationEmail($email);
            header('Location: /register/pending?email=' . urlencode($email) . '&success=' . urlencode('A new verification email has been sent.'));
        } catch (Exception $e) {
            header('Location: /register/pending?email=' . urlencode($email) . '&error=' . urlencode($e->getMessage()));
        }
        exit;
    }

    public function verifyEmail(): void {
        $token = $_GET['token'] ?? '';
        try {
            $this->authService->verifyEmail($token);
            require dirname(__DIR__) . '/Views/auth/email_verified.php';
            exit;
        } catch (Exception $e) {
            header('Location: /login?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    public function showForgotPassword(): void {
        $csrf = Security::generateCsrfToken();
        $error = $_GET['error'] ?? null;
        $success = $_GET['success'] ?? null;
        require dirname(__DIR__) . '/Views/auth/forgot_password.php';
    }

    public function processForgotPassword(): void {
        $email = $_POST['email'] ?? '';
        try {
            $result = $this->authService->requestPasswordReset($email);
            header('Location: /forgot-password?success=' . urlencode($result['message']));
            exit;
        } catch (Exception $e) {
            header('Location: /forgot-password?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    public function showResetPassword(): void {
        $token = $_GET['token'] ?? '';
        $csrf = Security::generateCsrfToken();
        $error = $_GET['error'] ?? null;
        require dirname(__DIR__) . '/Views/auth/reset_password.php';
    }

    public function processResetPassword(): void {
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        try {
            $this->authService->resetPassword($token, $password, $confirmPassword);
            header('Location: /login?success=' . urlencode('Password reset successful. Please sign in.'));
            exit;
        } catch (Exception $e) {
            header('Location: /reset-password?token=' . urlencode($token) . '&error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    public function showChangePassword(): void {
        $user = AuthService::getCurrentUser();
        if (!$user) {
            header('Location: /login?error=' . urlencode('Please sign in to change your password.'));
            exit;
        }

        $csrf = Security::generateCsrfToken();
        $error = $_GET['error'] ?? null;
        $success = $_GET['success'] ?? null;
        require dirname(__DIR__) . '/Views/auth/change_password.php';
    }

    public function processChangePassword(): void {
        $user = AuthService::getCurrentUser();
        if (!$user) {
            header('Location: /login?error=' . urlencode('Please sign in to change your password.'));
            exit;
        }

        $currentPassword = (string)($_POST['current_password'] ?? '');
        $newPassword = (string)($_POST['new_password'] ?? '');
        $confirmPassword = (string)($_POST['confirm_password'] ?? '');

        try {
            $this->authService->changeUserPassword((int)$user['id'], $currentPassword, $newPassword, $confirmPassword);
            header('Location: /account/password?success=' . urlencode('Your password was updated successfully.'));
            exit;
        } catch (Exception $e) {
            header('Location: /account/password?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    public function showAdminLogin(): void {
        if (AuthService::getCurrentAdmin()) {
            header('Location: /admin/dashboard');
            exit;
        }
        $csrf = Security::generateCsrfToken();
        $error = $_GET['error'] ?? null;
        require dirname(__DIR__) . '/Views/admin/login.php';
    }

    public function processAdminLogin(): void {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $isAjax = isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json');

        try {
            $result = $this->authService->adminLogin($email, $password);
            if ($isAjax) {
                Security::jsonResponse(['success' => true, 'redirect' => '/admin/dashboard', 'admin' => $result['admin']]);
            }
            header('Location: /admin/dashboard');
            exit;
        } catch (Exception $e) {
            if ($isAjax) {
                Security::jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
            }
            header('Location: /admin/login?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    public function logout(): void {
        AuthService::logout();
        header('Location: /login?success=' . urlencode('You have been logged out securely.'));
        exit;
    }

    public function adminLogout(): void {
        AuthService::logout();
        header('Location: /admin/login?success=' . urlencode('Administrative session closed.'));
        exit;
    }
}
