<?php

declare(strict_types=1);

namespace App\Model;

use Nette\Security\Authenticator;
use Nette\Security\Identity;
use Nette\Security\AuthenticationException;
use PDO;

final class UserManager implements Authenticator
{
    private PDO $pdo;

    public function __construct(Database $database)
    {
        $this->pdo = $database->getConnection();
    }

    public function authenticate(string $user, string $password): Identity
    {
        $stmt = $this->pdo->prepare('SELECT id, email, password_hash, is_admin FROM users WHERE email = :email');
        $stmt->execute(['email' => $user]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || !password_verify($password, $row['password_hash'])) {
            throw new AuthenticationException('Nesprávné přihlašovací údaje.');
        }

        $roles = $row['is_admin'] ? ['admin'] : ['user'];
        return new Identity((int) $row['id'], $roles, ['email' => $row['email']]);
    }

    public function register(string $email, string $password): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        if ((int) $stmt->fetchColumn() > 0) {
            throw new \RuntimeException('Uživatel s tímto e-mailem už existuje.');
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare('INSERT INTO users (email, password_hash, is_admin) VALUES (:email, :hash, 0)');
        $stmt->execute(['email' => $email, 'hash' => $hash]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findById(int $userId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, email, is_admin FROM users WHERE id = :id');
        $stmt->execute(['id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
