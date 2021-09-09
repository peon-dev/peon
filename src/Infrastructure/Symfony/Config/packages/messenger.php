<?php declare(strict_types=1);

use PHPMate\UseCase\ExecuteJob;
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework) {
    $framework->messenger()->defaultBus('command.bus');

    $framework->messenger()->bus('command.bus');

    $framework->messenger()->bus('event.bus')
        ->defaultMiddleware('allow_no_handlers');

    $framework->messenger()
        ->transport('async')
        ->dsn('%env(MESSENGER_TRANSPORT_DSN)%');

    $framework->messenger()
        // async is whatever name you gave your transport above
        ->routing(ExecuteJob::class)->senders(['async']);
};
