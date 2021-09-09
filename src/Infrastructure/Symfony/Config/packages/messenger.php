<?php declare(strict_types=1);

use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework) {
    $framework->messenger()->defaultBus('command.bus');

    $framework->messenger()->bus('command.bus');

    $framework->messenger()->bus('event.bus')
        ->defaultMiddleware('allow_no_handlers');
};
