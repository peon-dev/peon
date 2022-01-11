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
    ) {}


    public function __invoke(ProjectDeleted $event): void
    {
        // TODO: Dashboard - remove project
        // TODO: Project overview - delete
    }
}
