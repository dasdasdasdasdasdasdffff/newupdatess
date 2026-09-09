<?php

declare(strict_types=1);

namespace App\Helpers;

use PDO;

final class DatabaseSessionHandler implements \SessionHandlerInterface
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string
    {
        $statement = $this->db->prepare(
            'SELECT payload FROM sessions WHERE id = :id AND last_activity >= :activity LIMIT 1'
        );
        $statement->execute([
            ':id' => $id,
            ':activity' => time() - (int)ini_get('session.gc_maxlifetime'),
        ]);

        $payload = $statement->fetchColumn();
        return $payload === false ? '' : (string)$payload;
    }

    public function write(string $id, string $data): bool
    {
        $driver = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);
        $sql = $driver === 'mysql'
            ? 'INSERT INTO sessions (id, user_id, ip_address, user_agent, payload, last_activity)
               VALUES (:id, NULL, :ip, :user_agent, :payload, :last_activity)
               ON DUPLICATE KEY UPDATE
                   ip_address = VALUES(ip_address),
                   user_agent = VALUES(user_agent),
                   payload = VALUES(payload),
                   last_activity = VALUES(last_activity)'
            : 'INSERT INTO sessions (id, user_id, ip_address, user_agent, payload, last_activity)
               VALUES (:id, NULL, :ip, :user_agent, :payload, :last_activity)
               ON CONFLICT(id) DO UPDATE SET
                   ip_address = excluded.ip_address,
                   user_agent = excluded.user_agent,
                   payload = excluded.payload,
                   last_activity = excluded.last_activity';
        $statement = $this->db->prepare($sql);

        return $statement->execute([
            ':id' => $id,
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ':payload' => $data,
            ':last_activity' => time(),
        ]);
    }

    public function destroy(string $id): bool
    {
        $statement = $this->db->prepare('DELETE FROM sessions WHERE id = :id');
        return $statement->execute([':id' => $id]);
    }

    public function gc(int $max_lifetime): int|false
    {
        $statement = $this->db->prepare('DELETE FROM sessions WHERE last_activity < :activity');
        $statement->execute([':activity' => time() - $max_lifetime]);
        return $statement->rowCount();
    }
}
