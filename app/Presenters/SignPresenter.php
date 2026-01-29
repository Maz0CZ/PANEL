<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\UserManager;
use App\Model\MailerService;
use Nette\Application\UI\Form;

final class SignPresenter extends BasePresenter
{
    public function __construct(private UserManager $userManager, private MailerService $mailerService)
    {
        parent::__construct();
    }

    public function renderIn(): void
    {
        if ($this->getUser()->isLoggedIn()) {
            $this->redirect('Dashboard:default');
        }
    }

    public function renderUp(): void
    {
        if ($this->getUser()->isLoggedIn()) {
            $this->redirect('Dashboard:default');
        }
    }

    protected function createComponentSignInForm(): Form
    {
        $form = new Form();
        $form->addEmail('email', 'E-mail')->setRequired('Zadejte e-mail.');
        $form->addPassword('password', 'Heslo')->setRequired('Zadejte heslo.');
        $form->addSubmit('send', 'Přihlásit');

        $form->onSuccess[] = function (Form $form, array $values): void {
            try {
                $this->getUser()->login($values['email'], $values['password']);
                $this->redirect('Dashboard:default');
            } catch (\Throwable $e) {
                $form->addError('Přihlášení se nezdařilo.');
            }
        };

        return $form;
    }

    protected function createComponentSignUpForm(): Form
    {
        $form = new Form();
        $form->addEmail('email', 'E-mail')->setRequired('Zadejte e-mail.');
        $form->addPassword('password', 'Heslo')->setRequired('Zadejte heslo.')->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků.', 8);
        $form->addPassword('passwordConfirm', 'Potvrzení hesla')
            ->setRequired('Potvrďte heslo.')
            ->addRule(Form::EQUAL, 'Hesla se neshodují.', $form['password']);
        $form->addSubmit('send', 'Registrovat');

        $form->onSuccess[] = function (Form $form, array $values): void {
            try {
                $this->userManager->register($values['email'], $values['password']);
                $this->mailerService->sendWelcome($values['email']);
                $this->flashMessage('Registrace proběhla úspěšně.', 'success');
                $this->redirect('Sign:in');
            } catch (\Throwable $e) {
                $form->addError($e->getMessage());
            }
        };

        return $form;
    }

    public function actionOut(): void
    {
        $this->getUser()->logout(true);
        $this->redirect('Homepage:default');
    }
}
