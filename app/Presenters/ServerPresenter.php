<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\ServerManager;
use App\Model\ProvisioningService;
use App\Model\ConsoleService;
use App\Model\FtpService;
use Nette\Application\UI\Form;

final class ServerPresenter extends BasePresenter
{
    public function __construct(
        private ServerManager $serverManager,
        private ProvisioningService $provisioning,
        private ConsoleService $consoleService,
        private FtpService $ftpService,
    ) {
        parent::__construct();
    }

    public function renderDetail(int $id): void
    {
        $server = $this->findServerOrRedirect($id);
        $this->template->server = $server;
        $this->template->ftpHost = $this->ftpService->getHost();
        $this->template->ftpPort = $this->ftpService->getPort();
    }

    public function renderConsole(int $id): void
    {
        $server = $this->findServerOrRedirect($id);
        $this->template->server = $server;
        $this->template->log = $this->consoleService->tailLog($server['directory'] . '/console.log');
    }

    public function renderFiles(int $id): void
    {
        $server = $this->findServerOrRedirect($id);
        $this->template->server = $server;
        $this->template->files = $this->ftpService->listDirectory($server['directory']);
    }

    protected function createComponentCommandForm(): Form
    {
        $form = new Form();
        $form->addText('command', 'Příkaz')
            ->setRequired('Zadejte příkaz.')
            ->setHtmlAttribute('placeholder', 'say Hello from UltimatePanel');
        $form->addSubmit('send', 'Odeslat');

        $form->onSuccess[] = function (Form $form, array $values): void {
            $serverId = (int) $this->getParameter('id');
            $server = $this->findServerOrRedirect($serverId);
            $this->provisioning->sendCommand($serverId, $values['command']);
            $this->flashMessage('Příkaz byl odeslán.', 'success');
            $this->redirect('this');
        };

        return $form;
    }

    public function handleLog(int $id): void
    {
        $server = $this->findServerOrRedirect($id);
        $log = $this->consoleService->tailLog($server['directory'] . '/console.log');
        $this->sendJson(['log' => $log]);
    }

    public function handleAction(int $id, string $type): void
    {
        $server = $this->findServerOrRedirect($id);
        if ($server['game'] !== 'minecraft') {
            $this->flashMessage('Automatické spouštění je připraveno pouze pro Minecraft. Doplňte skripty pro další hry.', 'warning');
            $this->redirect('this');
        }
        switch ($type) {
            case 'start':
                $this->provisioning->startServer($id, (int) $server['ram_mb'], $server['directory']);
                $this->serverManager->updateStatus($id, 'running');
                break;
            case 'stop':
                $this->provisioning->stopServer($id);
                $this->serverManager->updateStatus($id, 'stopped');
                break;
            case 'reload':
                $this->provisioning->reloadServer($id);
                break;
            default:
                $this->flashMessage('Neznámá akce.', 'danger');
                $this->redirect('this');
                return;
        }

        $this->flashMessage('Akce byla provedena.', 'success');
        $this->redirect('this');
    }

    private function findServerOrRedirect(int $id): array
    {
        $this->ensureLoggedIn();
        $server = $this->serverManager->findById($id);
        if (!$server) {
            $this->error('Server nebyl nalezen.');
        }

        if (!$this->getUser()->isInRole('admin') && (int) $server['user_id'] !== (int) $this->getUser()->getId()) {
            $this->error('K tomuto serveru nemáte přístup.');
        }

        return $server;
    }

    private function ensureLoggedIn(): void
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
    }
}
