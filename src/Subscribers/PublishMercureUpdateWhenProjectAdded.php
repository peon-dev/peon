<?php

declare(strict_types=1);

namespace Peon\Subscribers;

use Peon\Domain\Project\Event\ProjectAdded;
use Peon\Packages\MessageBus\Event\EventHandlerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Twig\Environment;

final class PublishMercureUpdateWhenProjectAdded implements EventHandlerInterface
{
    public function __construct(
    ) {}


    public function __invoke(ProjectAdded $event): void
    {
        // TODO: Dashboard - project stats
    }
}
