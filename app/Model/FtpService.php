<?php

declare(strict_types=1);

namespace App\Model;

final class FtpService
{
    private array $config;

    public function __construct(array $parameters)
    {
        $this->config = $parameters['ftp'];
    }

    public function generateCredentials(): array
    {
        $username = 'up_' . bin2hex(random_bytes(4));
        $password = bin2hex(random_bytes(8));

        return [
            'user' => $username,
            'password' => $password,
            'host' => $this->config['host'],
            'port' => $this->config['port'],
        ];
    }

    public function listDirectory(string $path): array
    {
        if (!is_dir($path)) {
            return [];
        }

        $items = array_values(array_diff(scandir($path) ?: [], ['.', '..']));
        $output = [];
        foreach ($items as $item) {
            $itemPath = $path . DIRECTORY_SEPARATOR . $item;
            $output[] = [
                'name' => $item,
                'is_dir' => is_dir($itemPath),
                'size' => is_file($itemPath) ? filesize($itemPath) : null,
                'modified' => date('Y-m-d H:i:s', filemtime($itemPath) ?: time()),
            ];
        }

        return $output;
    }

    public function getHost(): string
    {
        return (string) ($this->config['host'] ?? '127.0.0.1');
    }

    public function getPort(): int
    {
        return (int) ($this->config['port'] ?? 21);
    }
}
