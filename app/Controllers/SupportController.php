<?php
/**
 * CapitalNest Nepal - Support Tickets Controller
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use Config\Database;
use App\Helpers\Security;
use Exception;

class SupportController {
    private \PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function index(): void {
        $user = AuthMiddleware::handle();
        $userId = (int)$user['id'];

        $stmt = $this->db->prepare("SELECT * FROM support_tickets WHERE user_id = :uid ORDER BY id DESC");
        $stmt->execute([':uid' => $userId]);
        $tickets = $stmt->fetchAll();

        $csrf = Security::generateCsrfToken();
        $error = $_GET['error'] ?? null;
        $success = $_GET['success'] ?? null;

        require dirname(__DIR__) . '/Views/support/index.php';
    }

    public function create(): void {
        $user = AuthMiddleware::handle();
        $userId = (int)$user['id'];

        $subject = trim($_POST['subject'] ?? '');
        $category = $_POST['category'] ?? 'account';
        $priority = $_POST['priority'] ?? 'medium';
        $message = trim($_POST['message'] ?? '');

        if (empty($subject) || empty($message)) {
            header('Location: /support?error=' . urlencode('Subject and message cannot be empty.'));
            exit;
        }

        $ticketRef = Security::generateRef('TCK');

        $this->db->beginTransaction();
        try {
            $tStmt = $this->db->prepare("
                INSERT INTO support_tickets (ticket_ref, user_id, subject, category, priority, status, created_at)
                VALUES (:ref, :uid, :sub, :cat, :pri, 'open', datetime('now'))
            ");
            $tStmt->execute([
                ':ref' => $ticketRef,
                ':uid' => $userId,
                ':sub' => Security::sanitize($subject),
                ':cat' => $category,
                ':pri' => $priority
            ]);
            $ticketId = (int)$this->db->lastInsertId();

            $mStmt = $this->db->prepare("
                INSERT INTO support_messages (ticket_id, sender_type, sender_id, message, created_at)
                VALUES (:tid, 'user', :uid, :msg, datetime('now'))
            ");
            $mStmt->execute([
                ':tid' => $ticketId,
                ':uid' => $userId,
                ':msg' => Security::sanitize($message)
            ]);

            $this->db->commit();
            header('Location: /support?success=' . urlencode('Support ticket opened successfully. Ref: ' . $ticketRef));
            exit;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            header('Location: /support?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    public function view(int $ticketId): void {
        $user = AuthMiddleware::handle();
        $userId = (int)$user['id'];

        $tStmt = $this->db->prepare("SELECT * FROM support_tickets WHERE id = :id AND user_id = :uid LIMIT 1");
        $tStmt->execute([':id' => $ticketId, ':uid' => $userId]);
        $ticket = $tStmt->fetch();

        if (!$ticket) {
            header('Location: /support?error=' . urlencode('Ticket not found.'));
            exit;
        }

        $mStmt = $this->db->prepare("SELECT * FROM support_messages WHERE ticket_id = :tid ORDER BY id ASC");
        $mStmt->execute([':tid' => $ticketId]);
        $messages = $mStmt->fetchAll();

        $csrf = Security::generateCsrfToken();
        require dirname(__DIR__) . '/Views/support/view.php';
    }

    public function reply(int $ticketId): void {
        $user = AuthMiddleware::handle();
        $userId = (int)$user['id'];

        $message = trim($_POST['message'] ?? '');
        if (!empty($message)) {
            $stmt = $this->db->prepare("
                INSERT INTO support_messages (ticket_id, sender_type, sender_id, message, created_at)
                VALUES (:tid, 'user', :uid, :msg, datetime('now'))
            ");
            $stmt->execute([':tid' => $ticketId, ':uid' => $userId, ':msg' => Security::sanitize($message)]);
            $this->db->prepare("UPDATE support_tickets SET updated_at = datetime('now') WHERE id = :id")->execute([':id' => $ticketId]);
        }
        header("Location: /support/view?id={$ticketId}");
        exit;
    }
}
