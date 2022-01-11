<?php

declare(strict_types=1);

namespace PHPMate\Subscribers;

use PHPMate\Domain\Task\Event\TaskChanged;
use PHPMate\Packages\MessageBus\Event\EventHandlerInterface;

final class PublishMercureUpdateWhenTaskChanged implements EventHandlerInterface
{
    public function __construct(
    ) {}


    public function __invoke(TaskChanged $event): void
    {
        // TODO: Project overview - task row
    }
}
