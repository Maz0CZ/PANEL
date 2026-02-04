<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Presenter;

abstract class BasePresenter extends Presenter
{
    protected function startup(): void
    {
        parent::startup();
        $this->template->panelVersion = '1.0.1';
        $this->template->panelDeveloper = 'Filip Piller';
        $this->template->isLoggedIn = $this->getUser()->isLoggedIn();
        $this->template->isAdmin = $this->getUser()->isInRole('admin');
    }
}
