<?php

declare(strict_types=1);

namespace App;

use Nette\Bootstrap\Configurator;
use Nette\Utils\Finder;

final class Bootstrap
{
    public static function boot(): Configurator
    {
        $configurator = new Configurator();
        $appDir = __DIR__;

        $configurator->setTempDirectory(__DIR__ . '/../temp');
        $configurator->addStaticParameters([
            'appDir' => $appDir,
            'wwwDir' => __DIR__ . '/../www',
            'dataDir' => __DIR__ . '/../data',
        ]);

        $configurator->addConfig(__DIR__ . '/config/common.neon');
        if (is_file(__DIR__ . '/config/local.neon')) {
            $configurator->addConfig(__DIR__ . '/config/local.neon');
        }

        return $configurator;
    }
}
