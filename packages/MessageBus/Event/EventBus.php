<?php

declare(strict_types=1);

namespace Peon\Packages\MessageBus\Event;

use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;

class EventBus
{
    private MessageBusInterface $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function dispatch(object $event): void
    {
        try {
            $this->bus->dispatch($event);
        } catch (HandlerFailedException $e) {
            $ex = $e->getPrevious();
            if ($ex !== null) {
                throw $ex;
            }
            throw $e;
        }
    }
}
