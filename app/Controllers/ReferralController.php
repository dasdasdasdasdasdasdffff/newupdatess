<?php
/**
 * CapitalNest Nepal - Referral Controller
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\ReferralService;

class ReferralController {
    private ReferralService $referralService;

    public function __construct() {
        $this->referralService = new ReferralService();
    }

    public function index(): void {
        $user = AuthMiddleware::handle();
        $userId = (int)$user['id'];

        $summary = $this->referralService->getUserReferralSummary($userId);
        $appUrl = getenv('APP_URL') ?: 'http://localhost:3000';
        $referralLink = rtrim($appUrl, '/') . '/register?ref=' . urlencode($summary['referral_code']);

        require dirname(__DIR__) . '/Views/referrals/index.php';
    }
}
