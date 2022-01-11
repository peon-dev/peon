<?php

declare(strict_types=1);

namespace Peon\Subscribers;

use Peon\Domain\Task\Event\TaskChanged;
use Peon\Packages\MessageBus\Event\EventHandlerInterface;

final class PublishMercureUpdateWhenTaskChanged implements EventHandlerInterface
{
    public function __construct(
    ) {}


    public function __invoke(TaskChanged $event): void
    {
        // TODO: Project overview - task row
    }
}
