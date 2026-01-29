<?php

declare(strict_types=1);

namespace App\Model;

final class ConsoleService
{
    public function tailLog(string $logPath, int $lines = 200): string
    {
        if (!is_file($logPath)) {
            return 'Log zatím není dostupný.';
        }

        $data = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($data === false) {
            return 'Nelze načíst log.';
        }

        $slice = array_slice($data, -$lines);
        return implode("\n", $slice);
    }
}
