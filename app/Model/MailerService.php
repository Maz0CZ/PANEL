<?php

declare(strict_types=1);

namespace App\Model;

use PHPMailer\PHPMailer\PHPMailer;

final class MailerService
{
    private array $config;

    public function __construct(array $parameters)
    {
        $this->config = $parameters['mailer']['smtp'] ?? [];
    }

    public function sendWelcome(string $email): void
    {
        if (empty($this->config)) {
            return;
        }

        $mailer = new PHPMailer(true);
        $mailer->isSMTP();
        $mailer->Host = $this->config['host'] ?? '';
        $mailer->SMTPAuth = true;
        $mailer->Username = $this->config['username'] ?? '';
        $mailer->Password = $this->config['password'] ?? '';
        $mailer->SMTPSecure = $this->config['secure'] ?? 'tls';
        $mailer->Port = (int) ($this->config['port'] ?? 587);

        $mailer->setFrom($mailer->Username ?: 'noreply@example.com', 'UltimatePanel');
        $mailer->addAddress($email);
        $mailer->Subject = 'Vítejte v UltimatePanel';
        $mailer->Body = "Děkujeme za registraci v UltimatePanel.\n\nUltimatePanel tým";
        $mailer->send();
    }
}
