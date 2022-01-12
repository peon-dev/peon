<?php

declare(strict_types=1);

namespace Peon\UseCase;

use Peon\Domain\Project\Event\ProjectDeleted;
use Peon\Domain\Project\Exception\ProjectNotFound;
use Peon\Domain\Project\ProjectsCollection;
use Peon\Packages\MessageBus\Command\CommandHandlerInterface;
use Peon\Packages\MessageBus\Event\EventBus;

final class DeleteProjectHandler implements CommandHandlerInterface
{
    public function __construct(
        private ProjectsCollection $projectsCollection,
        private EventBus $eventBus,
    ) {}


    /**
     * @throws ProjectNotFound
     */
    public function __invoke(DeleteProject $command): void
    {
        $this->projectsCollection->remove($command->projectId);

        // TODO: this event could be dispatched in entity
        $this->eventBus->dispatch(
            new ProjectDeleted($command->projectId)
        );
    }
}
