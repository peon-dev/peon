<?php

declare(strict_types=1);

namespace PHPMate\Packages\MessageBus\Command;

use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;

class CommandBus
{
    private MessageBusInterface $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function dispatch(object $command): void
    {
        try {
            $this->bus->dispatch($command);
        } catch (HandlerFailedException $e) {
            $ex = $e->getPrevious();
            if ($ex !== null) {
                throw $ex;
            }
            throw $e;
        }
    }
}
