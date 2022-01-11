<?php

declare(strict_types=1);

namespace Peon\Subscribers;

use Peon\Domain\Project\Event\ProjectDeleted;
use Peon\Packages\MessageBus\Event\EventHandlerInterface;
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
