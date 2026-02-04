<?php

declare(strict_types=1);

namespace App\Model;

use Symfony\Component\Process\Process;
use PDO;

final class ProvisioningService
{
    private PDO $pdo;
    private array $config;

    public function __construct(Database $database, array $parameters)
    {
        $this->pdo = $database->getConnection();
        $this->config = $parameters['provisioning'];
    }

    public function getNextPort(): int
    {
        $stmt = $this->pdo->query('SELECT MAX(port) FROM servers');
        $maxPort = (int) $stmt->fetchColumn();
        if ($maxPort <= 0) {
            return (int) $this->config['portStart'];
        }
        return $maxPort + 1;
    }

    public function provisionServer(int $serverId, string $game, int $ramMb, int $port, string $directory): void
    {
        if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
            throw new \RuntimeException('Nelze vytvořit adresář serveru.');
        }

        if ($game === 'minecraft') {
            $jarPath = $this->config['spigotJar'];
            if (!is_file($jarPath)) {
                throw new \RuntimeException('Chybí spigot.jar. Umístěte jej do ' . $jarPath . '.');
            }

            $targetJar = $directory . '/spigot.jar';
            if (!is_file($targetJar)) {
                if (!copy($jarPath, $targetJar)) {
                    throw new \RuntimeException('Nelze zkopírovat spigot.jar.');
                }
            }

            $this->writeEula($directory);
            $this->writeServerProperties($directory, $port);
            $this->startServer($serverId, $ramMb, $directory);
            return;
        }

        file_put_contents($directory . '/README.txt', "Sem umístěte herní soubory pro $game.\n");
    }

    public function startServer(int $serverId, int $ramMb, string $directory): void
    {
        $screenName = $this->screenName($serverId);
        $logPath = $directory . '/' . $this->config['logFile'];
        $directoryArg = escapeshellarg($directory);
        $logArg = escapeshellarg($logPath);
        $javaCmd = sprintf('cd %s && java -Xms%dM -Xmx%dM -jar spigot.jar nogui >> %s 2>&1', $directoryArg, $ramMb, $ramMb, $logArg);

        $createScreen = new Process(['screen', '-d', '-m', '-S', $screenName]);
        $createScreen->run();

        $command = sprintf('screen -S %s -p 0 -X stuff %s', escapeshellarg($screenName), escapeshellarg($javaCmd . "\n"));
        $process = Process::fromShellCommandline($command);
        $process->run();
    }

    public function stopServer(int $serverId): void
    {
        $screenName = $this->screenName($serverId);
        $command = sprintf('screen -S %s -p 0 -X stuff %s', escapeshellarg($screenName), escapeshellarg("stop\n"));
        Process::fromShellCommandline($command)->run();
    }

    public function reloadServer(int $serverId): void
    {
        $screenName = $this->screenName($serverId);
        $command = sprintf('screen -S %s -p 0 -X stuff %s', escapeshellarg($screenName), escapeshellarg("reload\n"));
        Process::fromShellCommandline($command)->run();
    }

    public function sendCommand(int $serverId, string $commandText): void
    {
        $screenName = $this->screenName($serverId);
        $command = sprintf('screen -S %s -p 0 -X stuff %s', escapeshellarg($screenName), escapeshellarg($commandText . "\n"));
        Process::fromShellCommandline($command)->run();
    }

    public function screenName(int $serverId): string
    {
        return $this->config['screenPrefix'] . $serverId;
    }

    private function writeEula(string $directory): void
    {
        $eulaContent = $this->config['eula'] ? 'eula=true' : 'eula=false';
        file_put_contents($directory . '/eula.txt', $eulaContent . "\n");
    }

    private function writeServerProperties(string $directory, int $port): void
    {
        $content = [
            'enable-rcon=false',
            'rcon.port=0',
            'query.port=' . $port,
            'server-port=' . $port,
            'max-players=20',
            'online-mode=true',
        ];
        file_put_contents($directory . '/server.properties', implode("\n", $content) . "\n");
    }
}
