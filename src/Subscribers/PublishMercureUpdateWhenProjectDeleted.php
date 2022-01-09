<?php

declare(strict_types=1);

namespace PHPMate\Subscribers;

use PHPMate\Domain\Project\Event\ProjectDeleted;
use PHPMate\Packages\MessageBus\Event\EventHandlerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Twig\Environment;

final class PublishMercureUpdateWhenProjectDeleted implements EventHandlerInterface
{
    public function __construct(
        private HubInterface $hub,
        private Environment $twig,
    ) {}


    public function __invoke(ProjectDeleted $event): void
    {
        // Dashboard - remove project
        // Project overview - delete

    }
}
