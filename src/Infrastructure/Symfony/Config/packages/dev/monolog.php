<?php

declare(strict_types=1);

use Mangoweb\MonologTracyHandler\TracyHandler;
use Symfony\Config\MonologConfig;

return static function (MonologConfig $monologConfig): void {
    $monologConfig->handler('tracy')
        ->type('service')
        ->id(TracyHandler::class)
        ->level('error');

    $monologConfig->handler('main')
        ->type('stream')
        ->path('%kernel.logs_dir%/%kernel.environment%.log')
        ->level('debug')
        ->channels()
            ->elements(['!event']);

    $monologConfig->handler('console')
        ->type('console')
        ->processPsr3Messages(false)
        ->channels()
            ->elements(['!event', '!doctrine', '!console']);
};
