<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\ServerManager;
use App\Model\UserManager;

final class AdminPresenter extends BasePresenter
{
    public function __construct(private ServerManager $serverManager, private UserManager $userManager)
    {
        parent::__construct();
    }

    public function renderDefault(): void
    {
        $this->ensureAdmin();
        $this->template->servers = $this->serverManager->fetchAll();
    }

    private function ensureAdmin(): void
    {
        if (!$this->getUser()->isLoggedIn() || !$this->getUser()->isInRole('admin')) {
            $this->redirect('Sign:in');
        }
    }
}
