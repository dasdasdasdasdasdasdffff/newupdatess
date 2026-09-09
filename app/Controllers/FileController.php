<?php
/**
 * CapitalNest Nepal - Secure Document & Voucher Delivery Gateway
 * Prevents direct unauthorized exposure of user KYC documents and financial slips.
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;
use Config\Database;

class FileController {
    public function serve(): void {
        // Ensure user or admin is logged in
        $user = AuthService::user();
        $admin = AuthService::admin();

        if (!$user && !$admin) {
            http_response_code(403);
            echo "Access Denied: Authentication required to access financial or identity artifacts.";
            exit;
        }

        $path = $_GET['path'] ?? '';
        if (empty($path)) {
            http_response_code(400);
            echo "Bad Request: Missing artifact path.";
            exit;
        }

        // Prevent directory traversal attacks
        $path = str_replace(['..', "\0"], '', $path);

        $rootStorage = dirname(__DIR__, 2) . '/storage';
        $fullPath = realpath($rootStorage . '/' . ltrim($path, '/'));

        if (!$fullPath || !str_starts_with($fullPath, realpath($rootStorage)) || !file_exists($fullPath)) {
            // Check if file is in public or uploaded elsewhere
            http_response_code(404);
            echo "Artifact not found.";
            exit;
        }

        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'webp' => 'image/webp'
        ];
        $contentType = $mimeTypes[$ext] ?? 'application/octet-stream';

        header("Content-Type: {$contentType}");
        header("Content-Length: " . filesize($fullPath));
        header("Cache-Control: private, max-age=3600");
        readfile($fullPath);
        exit;
    }
}
