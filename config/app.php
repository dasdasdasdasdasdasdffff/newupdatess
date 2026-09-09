<?php
/**
 * CapitalNest Nepal - Enterprise Application Configuration
 */

declare(strict_types=1);

return [
    'name' => getenv('APP_NAME') ?: 'CapitalNest Nepal',
    'tagline' => 'Premier Wealth & Investment Management Platform',
    'env' => getenv('APP_ENV') ?: 'production',
    'debug' => (bool)(getenv('APP_DEBUG') ?: false),
    'url' => getenv('APP_URL') ?: 'http://localhost:3000',
    'currency' => [
        'code' => 'NPR',
        'symbol' => 'रू',
        'rate_usd' => 135.50
    ],
    'limits' => [
        'min_deposit' => 1000.00,
        'max_deposit' => 2000000.00,
        'min_withdrawal' => 500.00,
        'max_withdrawal' => 500000.00,
        'withdrawal_fee_percent' => 1.00, // 1%
        'referral_commission_rate' => 5.00, // 5% of referee's first investment
    ],
    'session' => [
        'lifetime' => 86400 * 7, // 7 days
        'secure' => false, // Set to true if running over HTTPS strictly
        'httponly' => true,
        'samesite' => 'Lax'
    ],
    'paths' => [
        'storage' => dirname(__DIR__) . '/storage',
        'uploads' => dirname(__DIR__) . '/storage/uploads',
        'logs' => dirname(__DIR__) . '/storage/logs',
    ],
    'security' => [
        'csrf_token_name' => '_csrf_token',
        'max_login_attempts' => 5,
        'lockout_time_seconds' => 900 // 15 mins
    ]
];
