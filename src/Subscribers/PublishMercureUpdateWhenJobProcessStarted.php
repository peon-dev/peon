<?php

declare(strict_types=1);

namespace Peon\Subscribers;

use Peon\Domain\Job\Event\JobProcessStarted;
use Peon\Packages\MessageBus\Event\EventHandlerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Twig\Environment;

final class PublishMercureUpdateWhenJobProcessStarted implements EventHandlerInterface
{
    public function __construct(
    ) {}


    public function __invoke(JobProcessStarted $event): void
    {
        // TODO: Job detail - log
    }
}
