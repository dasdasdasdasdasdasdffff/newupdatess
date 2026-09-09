<?php
/**
 * CapitalNest Nepal - Notifications Controller
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use Config\Database;
use App\Helpers\Security;

class NotificationController {
    private \PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function index(): void {
        $user = AuthMiddleware::handle();
        $userId = (int)$user['id'];

        $stmt = $this->db->prepare("SELECT * FROM notifications WHERE user_id = :uid ORDER BY id DESC LIMIT 50");
        $stmt->execute([':uid' => $userId]);
        $notifications = $stmt->fetchAll();

        // Mark all as read
        $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :uid")->execute([':uid' => $userId]);

        require dirname(__DIR__) . '/Views/notifications/index.php';
    }
}
