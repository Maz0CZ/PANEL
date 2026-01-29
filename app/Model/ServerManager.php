<?php

declare(strict_types=1);

namespace App\Model;

use PDO;

final class ServerManager
{
    private PDO $pdo;

    public function __construct(Database $database)
    {
        $this->pdo = $database->getConnection();
    }

    public function countForUser(int $userId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM servers WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    public function fetchForUser(int $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT s.*, p.name AS package_name, p.ram_mb FROM servers s JOIN packages p ON p.id = s.package_id WHERE s.user_id = :user_id ORDER BY s.id DESC');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchAll(): array
    {
        $stmt = $this->pdo->query('SELECT s.*, u.email AS user_email, p.name AS package_name, p.ram_mb FROM servers s JOIN users u ON u.id = s.user_id JOIN packages p ON p.id = s.package_id ORDER BY s.id DESC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT s.*, p.name AS package_name, p.ram_mb FROM servers s JOIN packages p ON p.id = s.package_id WHERE s.id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(int $userId, int $packageId, string $game, int $port, string $directory, string $screenName, string $ftpUser, string $ftpPassword): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO servers (user_id, package_id, game, port, directory, status, screen_name, ftp_user, ftp_password) VALUES (:user_id, :package_id, :game, :port, :directory, :status, :screen_name, :ftp_user, :ftp_password)');
        $stmt->execute([
            'user_id' => $userId,
            'package_id' => $packageId,
            'game' => $game,
            'port' => $port,
            'directory' => $directory,
            'status' => 'provisioning',
            'screen_name' => $screenName,
            'ftp_user' => $ftpUser,
            'ftp_password' => $ftpPassword,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function updateScreenName(int $serverId, string $screenName): void
    {
        $stmt = $this->pdo->prepare('UPDATE servers SET screen_name = :screen_name WHERE id = :id');
        $stmt->execute(['screen_name' => $screenName, 'id' => $serverId]);
    }

    public function updateStatus(int $serverId, string $status): void
    {
        $stmt = $this->pdo->prepare('UPDATE servers SET status = :status WHERE id = :id');
        $stmt->execute(['status' => $status, 'id' => $serverId]);
    }

    public function updateStatusAndPid(int $serverId, string $status, ?int $pid): void
    {
        $stmt = $this->pdo->prepare('UPDATE servers SET status = :status, pid = :pid WHERE id = :id');
        $stmt->execute(['status' => $status, 'pid' => $pid, 'id' => $serverId]);
    }
}
