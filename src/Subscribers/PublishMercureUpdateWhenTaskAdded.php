<?php

declare(strict_types=1);

namespace PHPMate\Subscribers;

use PHPMate\Domain\Task\Event\TaskAdded;
use PHPMate\Packages\MessageBus\Event\EventHandlerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Twig\Environment;

final class PublishMercureUpdateWhenTaskAdded implements EventHandlerInterface
{
    public function __construct(
        private HubInterface $hub,
        private Environment $twig,
    ) {}


    public function __invoke(TaskAdded $event): void
    {
        // Dashboard - project stats
        // Project overview - tasks table append

    }
}
