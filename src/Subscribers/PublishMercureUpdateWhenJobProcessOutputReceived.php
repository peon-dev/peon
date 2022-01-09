<?php

declare(strict_types=1);

namespace PHPMate\Subscribers;

use PHPMate\Domain\Job\Event\JobProcessOutputReceived;
use PHPMate\Packages\MessageBus\Event\EventHandlerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Twig\Environment;

final class PublishMercureUpdateWhenJobProcessOutputReceived implements EventHandlerInterface
{
    public function __construct(
        private HubInterface $hub,
        private Environment $twig,
    ) {}


    public function __invoke(JobProcessOutputReceived $event): void
    {
        // Job detail - log
    }
}
