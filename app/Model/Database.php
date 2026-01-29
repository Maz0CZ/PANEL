<?php

declare(strict_types=1);

namespace App\Model;

use PDO;

final class Database
{
    private PDO $pdo;

    public function __construct(array $parameters)
    {
        $path = $parameters['database']['path'] ?? __DIR__ . '/../../data/panel.sqlite';
        $this->pdo = new PDO('sqlite:' . $path);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }
}
