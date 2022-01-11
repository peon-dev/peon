<?php

declare(strict_types=1);

namespace Peon\Subscribers;

use Peon\Domain\Job\Event\JobProcessOutputReceived;
use Peon\Packages\MessageBus\Event\EventHandlerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Twig\Environment;

final class PublishMercureUpdateWhenJobProcessOutputReceived implements EventHandlerInterface
{
    public function __construct(
    ) {}


    public function __invoke(JobProcessOutputReceived $event): void
    {
        // TODO: Job detail - log
    }
}
