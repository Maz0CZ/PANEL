<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\PackageManager;

final class HomepagePresenter extends BasePresenter
{
    public function __construct(private PackageManager $packageManager)
    {
        parent::__construct();
    }

    public function renderDefault(): void
    {
        if ($this->getUser()->isLoggedIn()) {
            $this->redirect('Dashboard:default');
        }
        $this->template->packages = $this->packageManager->fetchAll();
    }
}
