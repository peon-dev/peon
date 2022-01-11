<?php declare(strict_types=1);

use Peon\Packages\MessageBus\Command\CommandBus;
use Peon\Packages\MessageBus\Command\CommandHandlerInterface;
use Peon\Packages\MessageBus\Event\EventBus;
use Peon\Packages\MessageBus\Event\EventHandlerInterface;
use Peon\UseCase\ExecuteJob;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Config\FrameworkConfig;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (FrameworkConfig $framework, ContainerConfigurator $configurator) {
    $configurator->services()->set(CommandBus::class)
        ->arg('$bus', service('command.bus'));

    $configurator->services()->set(EventBus::class)
        ->arg('$bus', service('event.bus'));

    $configurator->services()->instanceof(CommandHandlerInterface::class)
        ->tag('messenger.message_handler', ['bus' => 'command.bus']);

    $configurator->services()->instanceof(EventHandlerInterface::class)
        ->tag('messenger.message_handler', ['bus' => 'event.bus']);


    $framework->messenger()->defaultBus('command.bus');

    $framework->messenger()->bus('command.bus');

    $framework->messenger()->bus('event.bus')
        ->defaultMiddleware('allow_no_handlers');

    $framework->messenger()
        ->transport('jobs')
        ->options([
            'auto_setup' => false,
        ])
        ->dsn('%env(MESSENGER_TRANSPORT_DSN)%')
        ->retryStrategy()
            ->maxRetries(0);

    $framework->messenger()
        // async is whatever name you gave your transport above
        ->routing(ExecuteJob::class)->senders(['jobs']);

};
