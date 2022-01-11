<?php

declare(strict_types=1);

namespace PHPMate\Subscribers;

use PHPMate\Domain\Job\Event\JobProcessStarted;
use PHPMate\Packages\MessageBus\Event\EventHandlerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Twig\Environment;

final class PublishMercureUpdateWhenJobProcessStarted implements EventHandlerInterface
{
    public function __construct(
        private HubInterface $hub,
        private Environment $twig,
    ) {}


    public function __invoke(JobProcessStarted $event): void
    {
        // TODO: Job detail - log
    }
}
