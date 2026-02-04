<?php

declare(strict_types=1);

namespace App\Router;

use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;

final class RouterFactory
{
    public static function createRouter(): RouteList
    {
        $router = new RouteList();
        $router->addRoute('', 'Homepage:default');
        $router->addRoute('login', 'Sign:in');
        $router->addRoute('register', 'Sign:up');
        $router->addRoute('logout', 'Sign:out');
        $router->addRoute('dashboard', 'Dashboard:default');
        $router->addRoute('server/<id>', 'Server:detail');
        $router->addRoute('server/<id>/console', 'Server:console');
        $router->addRoute('server/<id>/files', 'Server:files');
        $router->addRoute('admin', 'Admin:default');
        return $router;
    }
}
