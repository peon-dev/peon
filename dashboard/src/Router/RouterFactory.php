<?php

declare(strict_types=1);

namespace PHPMate\Dashboard\Router;

use Nette;
use Nette\Application\Routers\RouteList;


final class RouterFactory
{
    use Nette\StaticClass;

    public static function createRouter(): RouteList
    {
        $router = new RouteList;
        $router->addRoute('<presenter>[/<id>]', 'Job:default');
        return $router;
    }
}
