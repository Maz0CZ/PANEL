<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\PackageManager;
use App\Model\ServerManager;
use App\Model\ProvisioningService;
use App\Model\FtpService;
use Nette\Application\UI\Form;

final class DashboardPresenter extends BasePresenter
{
    private array $games = [
        'minecraft' => 'Minecraft (Spigot)',
        'valheim' => 'Valheim',
        'cs2' => 'Counter-Strike 2',
        'terraria' => 'Terraria',
    ];

    public function __construct(
        private PackageManager $packageManager,
        private ServerManager $serverManager,
        private ProvisioningService $provisioning,
        private FtpService $ftpService,
        private array $parameters,
    ) {
        parent::__construct();
    }

    public function renderDefault(): void
    {
        $this->ensureLoggedIn();
        $userId = (int) $this->getUser()->getId();
        $this->template->servers = $this->serverManager->fetchForUser($userId);
        $this->template->packages = $this->packageManager->fetchAll();
        $this->template->games = $this->games;
        $this->template->canCreate = $this->serverManager->countForUser($userId) < $this->parameters['provisioning']['maxServersPerUser'];
    }

    protected function createComponentCreateServerForm(): Form
    {
        $form = new Form();
        $form->addSelect('game', 'Hra', $this->games)->setRequired('Vyberte hru.');

        $packages = [];
        foreach ($this->packageManager->fetchAll() as $package) {
            $packages[$package['id']] = sprintf('%s (%d MB RAM)', $package['name'], $package['ram_mb']);
        }

        $form->addSelect('package', 'Balíček', $packages)->setPrompt('Vyberte balíček')->setRequired('Vyberte balíček.');
        $form->addSubmit('send', 'Vytvořit server');

        $form->onSuccess[] = function (Form $form, array $values): void {
            $this->ensureLoggedIn();
            $userId = (int) $this->getUser()->getId();
            if ($this->serverManager->countForUser($userId) >= $this->parameters['provisioning']['maxServersPerUser']) {
                $form->addError('Každý uživatel může mít pouze jeden server.');
                return;
            }

            $package = $this->packageManager->findById((int) $values['package']);
            if (!$package) {
                $form->addError('Zvolený balíček neexistuje.');
                return;
            }

            $port = $this->provisioning->getNextPort();
            $directory = $this->parameters['provisioning']['baseDir'] . '/server_' . $userId . '_' . time();
            $ftp = $this->ftpService->generateCredentials();

            $serverId = $this->serverManager->create(
                $userId,
                (int) $values['package'],
                (string) $values['game'],
                $port,
                $directory,
                'pending',
                $ftp['user'],
                $ftp['password']
            );

            $screenName = $this->provisioning->screenName($serverId);
            $this->serverManager->updateScreenName($serverId, $screenName);
            $game = (string) $values['game'];
            $this->provisioning->provisionServer($serverId, $game, (int) $package['ram_mb'], $port, $directory);
            $status = $game === 'minecraft' ? 'running' : 'created';
            $this->serverManager->updateStatus($serverId, $status);

            $this->flashMessage('Server byl vytvořen.', 'success');
            $this->redirect('Server:detail', ['id' => $serverId]);
        };

        return $form;
    }

    private function ensureLoggedIn(): void
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
    }
}
